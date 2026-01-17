<?php
/**
 * Plugin Name: Popup & Banner Pro
 * Plugin URI: https://github.com/vadikonline1/popup-banner-top/
 * Description: Plugin pentru afișarea unui popup și banner top cu personalizare avansată
 * Version: 2.1.1
 * Author: Steel..xD
 * GitHub Username: vadikonline1
 * GitHub Repository: popup-banner-top
 * License: GPL v2 or later
 * Requires Plugins: github-plugin-manager-main
 */

if (!defined('ABSPATH')) exit;

define('PBP_VERSION', '2.1.0');
define('PBP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PBP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once PBP_PLUGIN_DIR . 'includes/admin-settings.php';
require_once PBP_PLUGIN_DIR . 'includes/frontend-display.php';
require_once PBP_PLUGIN_DIR . 'includes/assets.php';

// INCLUDE UPDATER
require_once PBP_PLUGIN_DIR . 'includes/updater.php';
new GitHub_Plugin_Updater(__FILE__);

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

// Plugin action links (doar pentru Settings)
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($actions) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=popup-banner-settings') . '">⚙️ Settings</a>';
    array_unshift($actions, $settings_link);
    return $actions;
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($actions) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=popup-banner-settings') . '">⚙️ Settings</a>';
    array_unshift($actions, $settings_link);
    
    // Numele plugin-ului necesar
    $required_plugin = 'github-plugin-manager-main/github-plugin-manager.php';
    
    // Asigură-te că funcția is_plugin_active() este disponibilă
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    
    if (!is_plugin_active($required_plugin)) {
        $plugin_path = WP_PLUGIN_DIR . '/' . $required_plugin;
        
        if (!file_exists($plugin_path)) {
            $download_link = '<a href="https://github.com/vadikonline1/github-plugin-manager/archive/refs/heads/main.zip" style="color: red;">
                              ⬇️ Requires Download
                            </a>';
            array_unshift($actions, $download_link);
        } else {
            $activate_link = '<span style="color: #f0ad4e;">⚠️ Plugin installed but not activated</span>';
            array_unshift($actions, $activate_link);
        }
    }    
    return $actions;
});


