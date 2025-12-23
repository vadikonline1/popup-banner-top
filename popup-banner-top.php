<?php
/**
 * Plugin Name: Popup & Banner Pro
 * Plugin URI: https://github.com/vadikonline1/popup-banner-top/
 * Description: Plugin pentru afișarea unui popup și banner top cu personalizare avansată
 * Version: 2.0.1
 * Author: Steel..xD
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

define('PBP_VERSION', '2.0.0');
define('PBP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PBP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once PBP_PLUGIN_DIR . 'includes/admin-settings.php';
require_once PBP_PLUGIN_DIR . 'includes/frontend-display.php';
require_once PBP_PLUGIN_DIR . 'includes/assets.php';
require_once PBP_PLUGIN_DIR . 'includes/updater.php';

register_activation_hook(__FILE__, 'pbp_activate');
function pbp_activate() {
    $defaults = [
        'popup_enabled' => '1',
        'popup_delay' => '5',
        'popup_image' => '',
        'popup_redirect_type' => 'none',
        'popup_redirect_url' => '',
        'popup_redirect_page' => '',
        'popup_bg_color' => 'rgba(0,0,0,0.8)',
        'popup_close_color' => '#ffffff',
        'popup_close_bg' => '#000000',
        'banner_enabled' => '1',
        'banner_text' => 'Textul personalizat pentru banner',
        'banner_url_type' => 'none',
        'banner_url_text' => 'Află mai multe',
        'banner_custom_url' => '',
        'banner_page_url' => '',
        'banner_bg_color' => '#f8d7da',
        'banner_text_color' => '#721c24',
        'banner_link_color' => '#721c24',
        'banner_close_color' => '#000000',
    ];
    
    add_option('pbp_settings', $defaults);
}

// =======================
// Plugin Meta Links
// =======================
add_filter('plugin_row_meta', 'pbp_plugin_row_meta', 10, 4);
function pbp_plugin_row_meta($links, $file, $plugin_data, $status) {
    if ($file === plugin_basename(__FILE__)) {
        $links[] = '<a href="https://github.com/vadikonline1/popup-banner-top/" target="_blank" rel="noopener noreferrer">📖 Documentation</a>';
        $links[] = '<a href="https://github.com/vadikonline1/popup-banner-top/issues" target="_blank" rel="noopener noreferrer">🐛 Report Issue</a>';
        $links[] = '<a href="https://github.com/vadikonline1/popup-banner-top/" target="_blank" rel="noopener noreferrer">⭐ Rate Plugin</a>';
        $links[] = '<a href="https://github.com/vadikonline1/popup-banner-top/" target="_blank" rel="noopener noreferrer">🐙 GitHub</a>';
    }
    return $links;
}

// =======================
// Plugin Action Links
// =======================
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pbp_plugin_action_links');
function pbp_plugin_action_links($actions) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=popup-banner-settings') . '" style="font-weight:bold;color:#2271b1;">⚙️ Settings</a>';
    array_unshift($actions, $settings_link);
    return $actions;
}
