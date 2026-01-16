<?php
/**
 * Plugin Name: Popup & Banner Pro
 * Plugin URI: https://github.com/vadikonline1/popup-banner-top/
 * Description: Plugin pentru afișarea unui popup și banner top cu personalizare avansată
 * Version: 2.1.0
 * Author: Steel..xD
 * GitHub Username: vadikonline1
 * GitHub Repository: popup-banner-top
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;
add_action('admin_init', function() {
    // Only run in admin area
    if (!is_admin()) return;
    
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $required_plugin = 'github-plugin-manager/github-plugin-manager.php';
    $current_plugin = plugin_basename(__FILE__);
    
    // If current plugin is active but required plugin is not
    if (is_plugin_active($current_plugin) && !is_plugin_active($required_plugin)) {
        // Deactivate current plugin
        deactivate_plugins($current_plugin);
        
        // Show admin notice
        add_action('admin_notices', function() {
            $plugin_name = get_plugin_data(__FILE__)['Name'] ?? 'This plugin';
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php echo esc_html($plugin_name); ?></strong> has been deactivated.
                    <br>
                    This plugin requires <strong>GitHub Plugin Manager</strong> to function properly.
                </p>
                <p>
                    <strong>How to fix:</strong>
                    <ol style="margin-left: 20px;">
                        <li>Download <a href="https://github.com/vadikonline1/github-plugin-manager" target="_blank">GitHub Plugin Manager from GitHub</a></li>
                        <li>Go to WordPress Admin → Plugins → Add New → Upload Plugin</li>
                        <li>Upload the downloaded ZIP file and activate it</li>
                        <li>Reactivate <?php echo esc_html($plugin_name); ?></li>
                    </ol>
                </p>
                <p>
                    <a href="https://github.com/vadikonline1/github-plugin-manager/archive/refs/heads/main.zip" 
                       class="button button-primary"
                       style="margin-right: 10px;">
                        ⬇️ Download Plugin (ZIP)
                    </a>
                    <a href="<?php echo admin_url('plugin-install.php?tab=upload'); ?>" 
                       class="button">
                        📤 Upload to WordPress
                    </a>
                </p>
            </div>
            <?php
        });
    }
});

// Prevent activation without required plugin
register_activation_hook(__FILE__, function() {
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    if (!is_plugin_active('github-plugin-manager/github-plugin-manager.php')) {
        $plugin_name = get_plugin_data(__FILE__)['Name'] ?? 'This plugin';
        
        // Create a user-friendly error message
        $error_message = '
        <div style="max-width: 700px; margin: 50px auto; padding: 30px; background: #fff; border: 2px solid #d63638; border-radius: 5px;">
            <h2 style="color: #d63638; margin-top: 0;">
                <span style="font-size: 24px;">⚠️</span> Missing Required Plugin
            </h2>
            
            <p><strong>' . esc_html($plugin_name) . '</strong> cannot be activated because it requires another plugin to be installed first.</p>
            
            <div style="background: #f0f6fc; padding: 20px; border-radius: 4px; margin: 20px 0;">
                <h3 style="margin-top: 0;">Required Plugin: GitHub Plugin Manager</h3>
                <p>This plugin manages GitHub repositories directly from your WordPress dashboard.</p>
            </div>
            
            <h3>Installation Steps:</h3>
            <ol>
                <li><strong>Download:</strong> Get the plugin from <a href="https://github.com/vadikonline1/github-plugin-manager" target="_blank">GitHub</a></li>
                <li><strong>Upload:</strong> Go to <a href="' . admin_url('plugin-install.php?tab=upload') . '">Plugins → Add New → Upload Plugin</a></li>
                <li><strong>Activate:</strong> Activate the GitHub Plugin Manager</li>
                <li><strong>Return:</strong> Come back and activate ' . esc_html($plugin_name) . '</li>
            </ol>
            
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #ddd;">
                <a href="https://github.com/vadikonline1/github-plugin-manager/archive/refs/heads/main.zip" 
                   class="button button-primary button-large"
                   style="margin-right: 10px;">
                    Download ZIP File
                </a>
                <a href="' . admin_url('plugins.php') . '" class="button button-large">
                    Return to Plugins
                </a>
            </div>
            
            <p style="margin-top: 20px; color: #666; font-size: 13px;">
                <strong>Note:</strong> All plugins that require GitHub Plugin Manager will be deactivated until it is installed.
            </p>
        </div>';
        
        // Stop activation with the error message
        wp_die($error_message, 'Missing Required Plugin', 200);
    }
});


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

