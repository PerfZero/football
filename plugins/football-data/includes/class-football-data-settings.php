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

        return $settings['mock_mode'] === '1' || empty($settings['api_key']);
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
            'languages' => sanitize_text_field($input['languages'] ?? $defaults['languages']),
        ];
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
                            <p class="description">Пока ключа нет, оставляем пустым и работаем на мок-данных.</p>
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
