<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_Settings
{
    public const OPTION_NAME = 'football_data_settings';

    public static function defaults(): array
    {
        return [
            'api_host' => 'https://v3.football.api-sports.io',
            'api_key' => '',
            'mock_mode' => '1',
            'timezone' => 'Europe/Moscow',
            'default_season' => '2024',
            'selected_leagues' => '',
            'api_cache_ttl' => '3600',
            'languages' => 'ru,en,es,pt,fr,de',
        ];
    }

    public static function ensure_defaults(): void
    {
        if (!get_option(self::OPTION_NAME)) {
            add_option(self::OPTION_NAME, self::defaults());
        }
    }

    public function get(): array
    {
        return wp_parse_args((array) get_option(self::OPTION_NAME, []), self::defaults());
    }

    public function is_mock_mode(): bool
    {
        $settings = $this->get();

        return $settings['mock_mode'] === '1' || !$this->api_key_configured();
    }

    public function api_key(): string
    {
        if (defined('FOOTBALL_DATA_API_KEY') && FOOTBALL_DATA_API_KEY) {
            return (string) FOOTBALL_DATA_API_KEY;
        }

        $environment_key = getenv('FOOTBALL_DATA_API_KEY');
        if ($environment_key) {
            return (string) $environment_key;
        }

        $settings = $this->get();

        return (string) $settings['api_key'];
    }

    public function api_key_configured(): bool
    {
        return $this->api_key() !== '';
    }

    public function selected_league_ids(): array
    {
        $settings = $this->get();
        $raw_ids = preg_split('/[\s,;]+/', (string) $settings['selected_leagues']);
        $ids = [];

        foreach ($raw_ids as $raw_id) {
            $id = absint($raw_id);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    public function api_cache_ttl(): int
    {
        $settings = $this->get();

        return max(300, absint($settings['api_cache_ttl'] ?? 3600));
    }

    public function register_settings(): void
    {
        register_setting('football_data_settings_group', self::OPTION_NAME, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => self::defaults(),
        ]);
    }

    public function sanitize(array $input): array
    {
        $defaults = self::defaults();

        return [
            'api_host' => esc_url_raw($input['api_host'] ?? $defaults['api_host']),
            'api_key' => sanitize_text_field($input['api_key'] ?? ''),
            'mock_mode' => !empty($input['mock_mode']) ? '1' : '0',
            'timezone' => sanitize_text_field($input['timezone'] ?? $defaults['timezone']),
            'default_season' => sanitize_text_field($input['default_season'] ?? $defaults['default_season']),
            'selected_leagues' => $this->sanitize_league_ids($input['selected_leagues'] ?? ''),
            'api_cache_ttl' => (string) max(300, absint($input['api_cache_ttl'] ?? $defaults['api_cache_ttl'])),
            'languages' => sanitize_text_field($input['languages'] ?? $defaults['languages']),
        ];
    }

    private function sanitize_league_ids(string $value): string
    {
        $raw_ids = preg_split('/[\s,;]+/', $value);
        $ids = [];

        foreach ($raw_ids as $raw_id) {
            $id = absint($raw_id);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return implode("\n", array_values($ids));
    }

    public function register_admin_pages(): void
    {
        add_menu_page(
            'Football Data',
            'Football Data',
            'manage_options',
            'football-data',
            [$this, 'render_overview_page'],
            'dashicons-chart-line',
            58
        );

        add_submenu_page(
            'football-data',
            'Настройки API',
            'Настройки API',
            'manage_options',
            'football-data-settings',
            [$this, 'render_settings_page']
        );
    }

    public function render_overview_page(): void
    {
        $settings = $this->get();
        ?>
        <div class="wrap">
            <h1>Football Data</h1>
            <p>Плагин отвечает за футбольные данные: API-Football, мок-данные, сущности WordPress и будущую синхронизацию.</p>

            <h2>Текущий статус</h2>
            <table class="widefat striped" style="max-width: 900px;">
                <tbody>
                <tr>
                    <th>Режим данных</th>
                    <td><?php echo esc_html($this->is_mock_mode() ? 'Мок-данные' : 'Реальный API'); ?></td>
                </tr>
                <tr>
                    <th>API host</th>
                    <td><code><?php echo esc_html($settings['api_host']); ?></code></td>
                </tr>
                <tr>
                    <th>Сезон по умолчанию</th>
                    <td><?php echo esc_html($settings['default_season']); ?></td>
                </tr>
                <tr>
                    <th>Выбранные лиги API</th>
                    <td>
                        <?php
                        $league_ids = $this->selected_league_ids();
                        echo $league_ids ? '<code>' . esc_html(implode(', ', $league_ids)) . '</code>' : 'Не выбраны';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Языки</th>
                    <td><code><?php echo esc_html($settings['languages']); ?></code></td>
                </tr>
                </tbody>
            </table>

            <h2>Сущности</h2>
            <ul>
                <li>Турниры: <code>football_league</code></li>
                <li>Команды: <code>football_team</code></li>
                <li>Игроки: <code>football_player</code></li>
                <li>Матчи: <code>football_fixture</code></li>
                <li>Букмекеры: <code>football_bookmaker</code></li>
            </ul>

            <h2>Доступные демо-shortcodes</h2>
            <ul>
                <li><code>[football_data_status]</code> — статус источника данных.</li>
                <li><code>[football_data_players]</code> — список игроков из мок-данных.</li>
                <li><code>[football_data_matches]</code> — ближайшие матчи из мок-данных.</li>
                <li><code>[football_data_bookmakers]</code> — таблица букмекеров из мок-данных.</li>
            </ul>
        </div>
        <?php
    }

    public function render_settings_page(): void
    {
        $settings = $this->get();
        ?>
        <div class="wrap">
            <h1>Настройки API-Football</h1>
            <form method="post" action="options.php">
                <?php settings_fields('football_data_settings_group'); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="football_data_api_host">API host</label></th>
                        <td>
                            <input id="football_data_api_host" class="regular-text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[api_host]" value="<?php echo esc_attr($settings['api_host']); ?>">
                            <p class="description">Обычно: <code>https://v3.football.api-sports.io</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="football_data_api_key">API key</label></th>
                        <td>
                            <input id="football_data_api_key" class="regular-text" type="password" name="<?php echo esc_attr(self::OPTION_NAME); ?>[api_key]" value="<?php echo esc_attr($settings['api_key']); ?>" autocomplete="new-password">
                            <p class="description">Ключ можно хранить здесь или в переменной окружения <code>FOOTBALL_DATA_API_KEY</code>. В код и Git ключ не добавляем.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Мок-режим</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[mock_mode]" value="1" <?php checked($settings['mock_mode'], '1'); ?>>
                                Использовать локальные демо-данные вместо API.
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="football_data_timezone">Часовой пояс</label></th>
                        <td>
                            <input id="football_data_timezone" class="regular-text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[timezone]" value="<?php echo esc_attr($settings['timezone']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="football_data_season">Сезон по умолчанию</label></th>
                        <td>
                            <input id="football_data_season" class="regular-text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[default_season]" value="<?php echo esc_attr($settings['default_season']); ?>">
                            <p class="description">В API-Football сезон — это год старта сезона. Например, для 2024/25 укажите <code>2024</code>, для 2025/26 — <code>2025</code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="football_data_selected_leagues">Лиги для загрузки</label></th>
                        <td>
                            <textarea id="football_data_selected_leagues" class="large-text code" rows="6" name="<?php echo esc_attr(self::OPTION_NAME); ?>[selected_leagues]"><?php echo esc_textarea($settings['selected_leagues']); ?></textarea>
                            <p class="description">ID лиг API-Football, по одному на строку или через запятую. Синхронизация должна ходить только по этому списку, чтобы не грузить все турниры подряд.</p>
                            <p class="description">Примеры ID: АПЛ — <code>39</code>, Ла Лига — <code>140</code>, Бундеслига — <code>78</code>, Серия A — <code>135</code>, Лига 1 — <code>61</code>, Лига чемпионов — <code>2</code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="football_data_api_cache_ttl">Кэш API, секунд</label></th>
                        <td>
                            <input id="football_data_api_cache_ttl" class="regular-text" type="number" min="300" step="60" name="<?php echo esc_attr(self::OPTION_NAME); ?>[api_cache_ttl]" value="<?php echo esc_attr($settings['api_cache_ttl']); ?>">
                            <p class="description">Минимум 300 секунд. Нужен, чтобы повторные проверки не тратили лимиты API.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="football_data_languages">Языки</label></th>
                        <td>
                            <input id="football_data_languages" class="regular-text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[languages]" value="<?php echo esc_attr($settings['languages']); ?>">
                            <p class="description">Через запятую: <code>ru,en,es,pt,fr,de</code>. URL под папками настроим на фазе мультиязычности.</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Сохранить настройки'); ?>
            </form>
        </div>
        <?php
    }
}
