<?php
/**
 * Plugin Name: Football Data
 * Description: Данные футбольного сайта: API-Football, мок-режим, игроки, команды, турниры, матчи и букмекеры.
 * Version: 0.1.0
 * Author: PerfZero
 * Author URI: https://t.me/Perf_zero
 * Text Domain: football-data
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FOOTBALL_DATA_VERSION', '0.1.0');
define('FOOTBALL_DATA_FILE', __FILE__);
define('FOOTBALL_DATA_DIR', plugin_dir_path(__FILE__));
define('FOOTBALL_DATA_URL', plugin_dir_url(__FILE__));

$football_data_autoload = FOOTBALL_DATA_DIR . 'vendor/autoload.php';
if (file_exists($football_data_autoload)) {
    require_once $football_data_autoload;
}

require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-plugin.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-settings.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-cpt.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-api-client.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-mock-repository.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-rest.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-sync.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-shortcodes.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-carbon-fields.php';
require_once FOOTBALL_DATA_DIR . 'includes/class-football-data-i18n.php';

function football_data(): Football_Data_Plugin
{
    return Football_Data_Plugin::instance();
}

football_data();

register_activation_hook(__FILE__, ['Football_Data_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Football_Data_Plugin', 'deactivate']);
