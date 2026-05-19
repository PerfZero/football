<?php

if (!defined('ABSPATH')) {
    exit;
}

final class Football_Data_Shortcodes
{
    public function __construct(private Football_Data_Mock_Repository $mock)
    {
    }

    public function register(): void
    {
        add_shortcode('football_data_status', [$this, 'status']);
        add_shortcode('football_data_players', [$this, 'players']);
        add_shortcode('football_data_matches', [$this, 'matches']);
        add_shortcode('football_data_bookmakers', [$this, 'bookmakers']);
    }

    public function status(): string
    {
        $status = football_data()->i18n->translate('mock_mode_status');

        return '<div class="football-data-box"><strong>Football Data:</strong> ' . esc_html($status) . '</div>';
    }

    public function players(): string
    {
        $items = $this->mock->all('players');
        ob_start();
        ?>
        <div class="football-data-grid">
            <?php foreach ($items as $player) : ?>
                <article class="football-data-card">
                    <h3><?php echo esc_html($player['name']); ?></h3>
                    <p><?php echo esc_html($player['position']); ?> · <?php echo esc_html($player['team']); ?></p>
                    <p>Возраст: <?php echo esc_html((string) $player['age']); ?> · Рейтинг: <?php echo esc_html((string) $player['rating']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public function matches(): string
    {
        $items = $this->mock->all('fixtures');
        ob_start();
        ?>
        <div class="football-data-list">
            <?php foreach ($items as $fixture) : ?>
                <div class="football-data-row">
                    <strong><?php echo esc_html($fixture['home']); ?></strong>
                    <span>vs</span>
                    <strong><?php echo esc_html($fixture['away']); ?></strong>
                    <span><?php echo esc_html($fixture['league']); ?> · <?php echo esc_html($fixture['date']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    public function bookmakers(): string
    {
        $items = $this->mock->all('bookmakers');
        ob_start();
        ?>
        <table class="football-data-table">
            <thead>
            <tr>
                <th>Букмекер</th>
                <th>Рейтинг</th>
                <th>Бонус</th>
                <th>Мин. депозит</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $bookmaker) : ?>
                <tr>
                    <td><?php echo esc_html($bookmaker['name']); ?></td>
                    <td><?php echo esc_html((string) $bookmaker['rating']); ?></td>
                    <td><?php echo esc_html($bookmaker['bonus']); ?></td>
                    <td><?php echo esc_html($bookmaker['minDeposit']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return (string) ob_get_clean();
    }
}
