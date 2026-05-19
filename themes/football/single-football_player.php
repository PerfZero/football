<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

function football_player_meta(string $key, mixed $default = ''): mixed
{
    if (function_exists('carbon_get_post_meta')) {
        $value = carbon_get_post_meta(get_the_ID(), $key);
    } else {
        $value = get_post_meta(get_the_ID(), $key, true);
    }

    return $value !== '' && $value !== [] && $value !== null ? $value : $default;
}
?>

<main id="main" class="site-main player-profile">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>

        <section class="player-hero">
            <div class="container player-hero__grid">
                <div class="player-hero__content">
                    <p class="breadcrumbs">
                        <a href="<?php echo esc_url(home_url('/')); ?>"><?php football_esc_html_t('site.back_home'); ?></a>
                        <span>/</span>
                        <span><?php football_esc_html_t('section.players'); ?></span>
                    </p>

                    <h1><?php the_title(); ?> <span><?php echo esc_html((string) football_player_meta('football_number')); ?></span></h1>

                    <p class="player-hero__subtitle">
                        <?php echo esc_html(football_player_meta('football_nationality', 'Норвегия')); ?>
                        <span>·</span>
                        <?php echo esc_html(football_player_meta('football_position', 'Нападающий')); ?>
                        <span>·</span>
                        <?php echo esc_html(football_player_meta('football_current_team', 'Манчестер Сити')); ?>
                    </p>

                    <dl class="player-facts">
                        <div><dt><?php football_esc_html_t('player.birth_date'); ?></dt><dd><?php echo esc_html(football_player_meta('football_birth_date')); ?></dd></div>
                        <div><dt><?php football_esc_html_t('player.birth_place'); ?></dt><dd><?php echo esc_html(football_player_meta('football_birth_place')); ?></dd></div>
                        <div><dt><?php football_esc_html_t('player.nationality'); ?></dt><dd><?php echo esc_html(football_player_meta('football_nationality')); ?></dd></div>
                        <div><dt><?php football_esc_html_t('player.height'); ?></dt><dd><?php echo esc_html(football_player_meta('football_height')); ?></dd></div>
                        <div><dt><?php football_esc_html_t('player.weight'); ?></dt><dd><?php echo esc_html(football_player_meta('football_weight')); ?></dd></div>
                        <div><dt><?php football_esc_html_t('player.average_rating'); ?></dt><dd><?php echo esc_html(football_player_meta('football_average_rating')); ?></dd></div>
                    </dl>
                </div>

                <aside class="player-summary">
                    <h2><?php football_esc_html_t('player.about'); ?></h2>
                    <p><?php echo esc_html(football_player_meta('football_about', wp_strip_all_tags(get_the_content()))); ?></p>
                    <div class="player-summary__stats">
                        <div><strong>239</strong><span><?php football_esc_html_t('player.matches'); ?></span></div>
                        <div><strong>215</strong><span><?php football_esc_html_t('player.goals'); ?></span></div>
                        <div><strong>40</strong><span><?php football_esc_html_t('player.assists'); ?></span></div>
                    </div>
                </aside>
            </div>
        </section>

        <section class="container player-profile__body">
            <div class="player-panel">
                <h2><?php football_esc_html_t('player.characteristics'); ?></h2>
                <?php
                $skills = [
                    'Скорость' => 94,
                    'Удары' => 96,
                    'Пасы' => 72,
                    'Дриблинг' => 83,
                    'Физика' => 94,
                ];
                ?>
                <?php foreach ($skills as $label => $value) : ?>
                    <div class="skill-row">
                        <span><?php echo esc_html($label); ?></span>
                        <div><i style="width: <?php echo esc_attr((string) $value); ?>%"></i></div>
                        <strong><?php echo esc_html((string) $value); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="player-panel">
                <h2><?php football_esc_html_t('player.positions'); ?></h2>
                <div class="pitch-mini">
                    <span class="pitch-dot pitch-dot--main"></span>
                    <span class="pitch-dot pitch-dot--side"></span>
                </div>
                <p><strong><?php football_esc_html_t('player.main_position'); ?></strong> <?php echo esc_html(football_player_meta('football_position')); ?></p>
                <p><?php football_esc_html_t('player.additional_positions'); ?></p>
            </div>

            <div class="player-panel player-panel--wide">
                <h2><?php football_esc_html_t('player.season_stats'); ?></h2>
                <?php $season_stats = football_player_meta('football_season_stats', []); ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th><?php football_esc_html_t('player.league'); ?></th>
                            <th><?php football_esc_html_t('player.matches'); ?></th>
                            <th><?php football_esc_html_t('player.goals'); ?></th>
                            <th><?php football_esc_html_t('player.assists_short'); ?></th>
                            <th><?php football_esc_html_t('player.yellow_cards_short'); ?></th>
                            <th><?php football_esc_html_t('player.red_cards_short'); ?></th>
                            <th><?php football_esc_html_t('player.minutes'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($season_stats as $row) : ?>
                            <tr>
                                <td><?php echo esc_html($row['league'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['matches'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['goals'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['assists'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['yellow_cards'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['red_cards'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['minutes'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="player-panel player-panel--wide">
                <h2><?php football_esc_html_t('player.career'); ?></h2>
                <?php $career = football_player_meta('football_career', []); ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th><?php football_esc_html_t('player.period'); ?></th>
                            <th><?php football_esc_html_t('player.team'); ?></th>
                            <th><?php football_esc_html_t('player.matches'); ?></th>
                            <th><?php football_esc_html_t('player.goals'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($career as $row) : ?>
                            <tr>
                                <td><?php echo esc_html($row['period'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['team'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['matches'] ?? ''); ?></td>
                                <td><?php echo esc_html($row['goals'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
