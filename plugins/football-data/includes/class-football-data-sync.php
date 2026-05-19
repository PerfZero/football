<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_Sync
{
    public function __construct(
        private Football_Data_Settings $settings,
        private Football_Data_Api_Client $api
    ) {
    }

    public function register_admin_page(): void
    {
        add_submenu_page(
            'football-data',
            'Синхронизация',
            'Синхронизация',
            'manage_options',
            'football-data-sync',
            [$this, 'render_page']
        );
    }

    public function handle_admin_action(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Недостаточно прав.');
        }

        check_admin_referer('football_data_sync');

        $task = sanitize_key($_POST['football_data_sync_task'] ?? '');
        $result = match ($task) {
            'leagues' => $this->sync_leagues(),
            'standings' => $this->sync_standings(),
            'teams' => $this->sync_teams(),
            'fixtures' => $this->sync_fixtures(),
            default => new WP_Error('football_data_unknown_sync_task', 'Неизвестная задача синхронизации.'),
        };

        $payload = is_wp_error($result)
            ? ['type' => 'error', 'message' => $result->get_error_message()]
            : ['type' => 'success', 'message' => $this->format_result_message($task, $result)];

        set_transient($this->notice_key(), $payload, 60);

        wp_safe_redirect(admin_url('admin.php?page=football-data-sync'));
        exit;
    }

    public function render_page(): void
    {
        $settings = $this->settings->get();
        $league_ids = $this->settings->selected_league_ids();
        $notice = get_transient($this->notice_key());
        delete_transient($this->notice_key());
        ?>
        <div class="wrap">
            <h1>Синхронизация Football Data</h1>
            <p>Загрузка работает только по лигам, выбранным в настройках API. Это защищает лимиты и не тянет весь API целиком.</p>

            <?php if (is_array($notice)) : ?>
                <div class="notice notice-<?php echo esc_attr($notice['type']); ?> is-dismissible">
                    <p><?php echo esc_html($notice['message']); ?></p>
                </div>
            <?php endif; ?>

            <h2>Текущие настройки</h2>
            <table class="widefat striped" style="max-width: 900px;">
                <tbody>
                <tr>
                    <th>Источник</th>
                    <td><?php echo esc_html($this->settings->is_mock_mode() ? 'Мок-данные' : 'Реальный API'); ?></td>
                </tr>
                <tr>
                    <th>Ключ API</th>
                    <td><?php echo esc_html($this->settings->api_key_configured() ? 'Задан' : 'Не задан'); ?></td>
                </tr>
                <tr>
                    <th>Сезон</th>
                    <td><code><?php echo esc_html($settings['default_season']); ?></code></td>
                </tr>
                <tr>
                    <th>Лиги</th>
                    <td><?php echo $league_ids ? '<code>' . esc_html(implode(', ', $league_ids)) . '</code>' : 'Не выбраны'; ?></td>
                </tr>
                <tr>
                    <th>Кэш API</th>
                    <td><?php echo esc_html($this->settings->api_cache_ttl()); ?> сек.</td>
                </tr>
                </tbody>
            </table>

            <h2>Загрузить данные</h2>
            <p>Рекомендуемый порядок: сначала турниры, потом команды, потом матчи. Игроков добавим отдельным порционным синком, потому что этот endpoint самый тяжёлый по лимитам.</p>
            <p>Если синхронизация возвращает 0 созданных и 0 обновлённых записей, сначала проверьте сезон: для сезона 2024/25 в API нужно указывать <code>2024</code>, для 2025/26 — <code>2025</code>.</p>

            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin: 18px 0;">
                <?php $this->render_button('leagues', 'Загрузить турниры'); ?>
                <?php $this->render_button('standings', 'Загрузить таблицы'); ?>
                <?php $this->render_button('teams', 'Загрузить команды'); ?>
                <?php $this->render_button('fixtures', 'Загрузить матчи сезона'); ?>
            </div>

            <h2>Что уже есть в WordPress</h2>
            <table class="widefat striped" style="max-width: 900px;">
                <tbody>
                <tr><th>Турниры</th><td><?php echo esc_html(wp_count_posts('football_league')->publish ?? 0); ?></td></tr>
                <tr><th>Команды</th><td><?php echo esc_html(wp_count_posts('football_team')->publish ?? 0); ?></td></tr>
                <tr><th>Матчи</th><td><?php echo esc_html(wp_count_posts('football_fixture')->publish ?? 0); ?></td></tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function sync_leagues(): array|WP_Error
    {
        $season = $this->default_season();
        $items = $this->api->selected_leagues($season);
        if (is_wp_error($items)) {
            return $items;
        }

        $stats = $this->empty_stats(1);
        foreach ($items as $item) {
            $league = $item['league'] ?? [];
            $country = $item['country'] ?? [];
            $api_id = absint($league['id'] ?? 0);
            $name = sanitize_text_field($league['name'] ?? '');

            if (!$api_id || $name === '') {
                $stats['skipped']++;
                continue;
            }

            $post_id = $this->upsert_post('football_league', $api_id, $name, '', [
                'football_logo' => $this->sideload_image($league['logo'] ?? '', $name . ' logo'),
                'football_country' => sanitize_text_field($country['name'] ?? ''),
                'football_country_code' => sanitize_text_field($country['code'] ?? ''),
                'football_country_flag' => $this->sideload_image($country['flag'] ?? '', ($country['name'] ?? $name) . ' flag'),
                'football_season' => $season,
                'football_league_type' => sanitize_key($league['type'] ?? 'league'),
                'football_season_start' => sanitize_text_field($this->season_value($item, 'start')),
                'football_season_end' => sanitize_text_field($this->season_value($item, 'end')),
                'football_season_current' => $this->season_value($item, 'current') ? '1' : '0',
                'football_coverage' => wp_json_encode($this->season_value($item, 'coverage') ?: [], JSON_UNESCAPED_UNICODE),
                'football_api_payload' => wp_json_encode($item, JSON_UNESCAPED_UNICODE),
            ], $stats);

            $this->assign_terms($post_id, 'football_country', $country['name'] ?? '');
            $this->assign_terms($post_id, 'football_season', $season);
        }

        return $stats;
    }

    public function sync_standings(): array|WP_Error
    {
        $season = $this->default_season();
        $league_ids = $this->settings->selected_league_ids();
        if (!$league_ids) {
            return new WP_Error('football_data_no_selected_leagues', 'В настройках не выбраны лиги для загрузки.');
        }

        $stats = $this->empty_stats(count($league_ids));
        foreach ($league_ids as $league_id) {
            $response = $this->api->request_for_league('standings', $league_id, ['season' => $season]);
            if (is_wp_error($response)) {
                return $response;
            }

            $league = $response['response'][0]['league'] ?? [];
            if (!$league) {
                $stats['skipped']++;
                continue;
            }

            $post_id = $this->find_post_by_api_id('football_league', $league_id);
            if (!$post_id) {
                $post_id = $this->upsert_post('football_league', $league_id, sanitize_text_field($league['name'] ?? 'League ' . $league_id), '', [
                    'football_logo' => $this->sideload_image($league['logo'] ?? '', ($league['name'] ?? 'League ' . $league_id) . ' logo'),
                    'football_country' => sanitize_text_field($league['country'] ?? ''),
                    'football_country_flag' => $this->sideload_image($league['flag'] ?? '', ($league['country'] ?? 'Country') . ' flag'),
                    'football_season' => $season,
                ], $stats);
            } else {
                $stats['updated']++;
            }

            update_post_meta($post_id, 'football_standings', $this->localize_standings_images($league['standings'] ?? []));
            update_post_meta($post_id, 'football_standings_payload', wp_json_encode($league, JSON_UNESCAPED_UNICODE));
            $this->assign_terms($post_id, 'football_season', $season);
        }

        return $stats;
    }

    public function sync_teams(): array|WP_Error
    {
        $season = $this->default_season();
        $league_ids = $this->settings->selected_league_ids();
        if (!$league_ids) {
            return new WP_Error('football_data_no_selected_leagues', 'В настройках не выбраны лиги для загрузки.');
        }

        $stats = $this->empty_stats(count($league_ids));
        foreach ($league_ids as $league_id) {
            $response = $this->api->request_for_league('teams', $league_id, ['season' => $season]);
            if (is_wp_error($response)) {
                return $response;
            }

            foreach ($response['response'] ?? [] as $item) {
                $team = $item['team'] ?? [];
                $venue = $item['venue'] ?? [];
                $api_id = absint($team['id'] ?? 0);
                $name = sanitize_text_field($team['name'] ?? '');

                if (!$api_id || $name === '') {
                    $stats['skipped']++;
                    continue;
                }

                $post_id = $this->upsert_post('football_team', $api_id, $name, '', [
                    'football_logo' => $this->sideload_image($team['logo'] ?? '', $name . ' logo'),
                    'football_team_code' => sanitize_text_field($team['code'] ?? ''),
                    'football_country' => sanitize_text_field($team['country'] ?? ''),
                    'football_national_team' => !empty($team['national']) ? '1' : '0',
                    'football_city' => sanitize_text_field($venue['city'] ?? ''),
                    'football_stadium' => sanitize_text_field($venue['name'] ?? ''),
                    'football_founded' => sanitize_text_field((string) ($team['founded'] ?? '')),
                    'football_league_api_id' => (string) $league_id,
                    'football_venue_api_id' => sanitize_text_field((string) ($venue['id'] ?? '')),
                    'football_venue_address' => sanitize_text_field($venue['address'] ?? ''),
                    'football_venue_capacity' => sanitize_text_field((string) ($venue['capacity'] ?? '')),
                    'football_venue_surface' => sanitize_text_field($venue['surface'] ?? ''),
                    'football_venue_image' => $this->sideload_image($venue['image'] ?? '', ($venue['name'] ?? $name . ' stadium') . ' image'),
                ], $stats);

                $this->assign_terms($post_id, 'football_country', $team['country'] ?? '');
            }
        }

        return $stats;
    }

    public function sync_fixtures(): array|WP_Error
    {
        $season = $this->default_season();
        $settings = $this->settings->get();
        $league_ids = $this->settings->selected_league_ids();
        if (!$league_ids) {
            return new WP_Error('football_data_no_selected_leagues', 'В настройках не выбраны лиги для загрузки.');
        }

        $stats = $this->empty_stats(count($league_ids));
        foreach ($league_ids as $league_id) {
            $response = $this->api->request_for_league('fixtures', $league_id, [
                'season' => $season,
                'timezone' => $settings['timezone'],
            ]);
            if (is_wp_error($response)) {
                return $response;
            }

            foreach ($response['response'] ?? [] as $item) {
                $fixture = $item['fixture'] ?? [];
                $league = $item['league'] ?? [];
                $teams = $item['teams'] ?? [];
                $goals = $item['goals'] ?? [];
                $api_id = absint($fixture['id'] ?? 0);
                $home = sanitize_text_field($teams['home']['name'] ?? '');
                $away = sanitize_text_field($teams['away']['name'] ?? '');

                if (!$api_id || $home === '' || $away === '') {
                    $stats['skipped']++;
                    continue;
                }

                $post_id = $this->upsert_post('football_fixture', $api_id, $home . ' - ' . $away, '', [
                    'football_league_api_id' => (string) $league_id,
                    'football_league_name' => sanitize_text_field($league['name'] ?? ''),
                    'football_round' => sanitize_text_field($league['round'] ?? ''),
                    'football_match_datetime' => sanitize_text_field($fixture['date'] ?? ''),
                    'football_venue' => sanitize_text_field($fixture['venue']['name'] ?? ''),
                    'football_status' => sanitize_text_field($fixture['status']['long'] ?? ''),
                    'football_home_team' => $home,
                    'football_away_team' => $away,
                    'football_home_score' => sanitize_text_field((string) ($goals['home'] ?? '')),
                    'football_away_score' => sanitize_text_field((string) ($goals['away'] ?? '')),
                ], $stats);

                $this->assign_terms($post_id, 'football_season', $season);
            }
        }

        return $stats;
    }

    private function render_button(string $task, string $label): void
    {
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('football_data_sync'); ?>
            <input type="hidden" name="action" value="football_data_sync">
            <input type="hidden" name="football_data_sync_task" value="<?php echo esc_attr($task); ?>">
            <?php submit_button($label, 'primary', 'submit', false); ?>
        </form>
        <?php
    }

    private function upsert_post(string $post_type, int $api_id, string $title, string $content, array $meta, array &$stats): int
    {
        $post_id = $this->find_post_by_api_id($post_type, $api_id);

        $post_data = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_title' => $title,
            'post_content' => $content,
        ];

        if ($post_id) {
            $post_data['ID'] = $post_id;
            $post_id = wp_update_post($post_data, true);
            $stats['updated']++;
        } else {
            $post_id = wp_insert_post($post_data, true);
            $stats['created']++;
        }

        if (is_wp_error($post_id)) {
            $stats['skipped']++;
            return 0;
        }

        update_post_meta($post_id, 'football_api_id', (string) $api_id);
        $this->update_carbon_meta($post_id, 'football_api_id', (string) $api_id);
        foreach ($meta as $key => $value) {
            update_post_meta($post_id, $key, $value);
            $this->update_carbon_meta($post_id, $key, $value);
        }

        return (int) $post_id;
    }

    private function update_carbon_meta(int $post_id, string $key, mixed $value): void
    {
        if (!function_exists('carbon_set_post_meta')) {
            return;
        }

        carbon_set_post_meta($post_id, $key, $value);
    }

    private function find_post_by_api_id(string $post_type, int $api_id): int
    {
        $existing = get_posts([
            'post_type' => $post_type,
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_key' => 'football_api_id',
            'meta_value' => (string) $api_id,
        ]);

        return $existing ? (int) $existing[0] : 0;
    }

    private function assign_terms(int $post_id, string $taxonomy, string $term_name): void
    {
        $term_name = trim($term_name);
        if (!$post_id || $term_name === '') {
            return;
        }

        wp_set_object_terms($post_id, $term_name, $taxonomy, false);
    }

    private function sideload_image(string $url, string $title): int|string
    {
        $url = esc_url_raw($url);
        if ($url === '') {
            return '';
        }

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_key' => '_football_data_source_url',
            'meta_value' => $url,
        ]);

        if ($existing) {
            return (int) $existing[0];
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $allow_svg = static function (array $extensions): array {
            $extensions[] = 'svg';

            return array_values(array_unique($extensions));
        };

        add_filter('image_sideload_extensions', $allow_svg);
        $attachment_id = media_sideload_image($url, 0, sanitize_text_field($title), 'id');
        remove_filter('image_sideload_extensions', $allow_svg);

        if (is_wp_error($attachment_id)) {
            if (str_ends_with(strtolower((string) parse_url($url, PHP_URL_PATH)), '.svg')) {
                return $this->sideload_svg($url, $title);
            }

            return '';
        }

        update_post_meta((int) $attachment_id, '_football_data_source_url', $url);

        return (int) $attachment_id;
    }

    private function sideload_svg(string $url, string $title): int|string
    {
        $temporary_file = download_url($url, 20);
        if (is_wp_error($temporary_file)) {
            return '';
        }

        $filename = sanitize_file_name(wp_basename((string) parse_url($url, PHP_URL_PATH)));
        if ($filename === '') {
            $filename = sanitize_title($title) . '.svg';
        }

        $contents = file_get_contents($temporary_file);
        @unlink($temporary_file);

        if ($contents === false || trim($contents) === '') {
            return '';
        }

        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error']) || empty($upload_dir['path'])) {
            return '';
        }

        wp_mkdir_p($upload_dir['path']);
        $filename = wp_unique_filename($upload_dir['path'], $filename);
        $file_path = trailingslashit($upload_dir['path']) . $filename;

        if (file_put_contents($file_path, $contents) === false) {
            return '';
        }

        $attachment_id = wp_insert_attachment([
            'post_mime_type' => 'image/svg+xml',
            'post_title' => sanitize_text_field($title),
            'post_status' => 'inherit',
        ], $file_path);

        if (is_wp_error($attachment_id) || !$attachment_id) {
            return '';
        }

        update_post_meta((int) $attachment_id, '_football_data_source_url', $url);

        return (int) $attachment_id;
    }

    private function localize_standings_images(array $standings): array
    {
        foreach ($standings as $group_index => $group) {
            if (!is_array($group)) {
                continue;
            }

            foreach ($group as $row_index => $row) {
                $logo = $row['team']['logo'] ?? '';
                $team_name = $row['team']['name'] ?? 'Team';

                if ($logo) {
                    $standings[$group_index][$row_index]['team']['logo'] = $this->sideload_image($logo, $team_name . ' logo');
                }
            }
        }

        return $standings;
    }

    private function default_season(): string
    {
        $settings = $this->settings->get();

        return sanitize_text_field($settings['default_season']);
    }

    private function empty_stats(int $requests): array
    {
        return [
            'requests' => $requests,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];
    }

    private function format_result_message(string $task, array $result): string
    {
        $labels = [
            'leagues' => 'Турниры',
            'standings' => 'Таблицы',
            'teams' => 'Команды',
            'fixtures' => 'Матчи',
        ];

        return sprintf(
            '%s: запросов %d, создано %d, обновлено %d, пропущено %d.',
            $labels[$task] ?? 'Синхронизация',
            $result['requests'],
            $result['created'],
            $result['updated'],
            $result['skipped']
        );
    }

    private function notice_key(): string
    {
        return 'football_data_sync_notice_' . get_current_user_id();
    }

    private function season_value(array $item, string $key): mixed
    {
        foreach (($item['seasons'] ?? []) as $season) {
            if ((string) ($season['year'] ?? '') === $this->default_season()) {
                return $season[$key] ?? null;
            }
        }

        return $item['seasons'][0][$key] ?? null;
    }
}
