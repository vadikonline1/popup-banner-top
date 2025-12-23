<?php
// includes/updater.php - UNIVERSAL GITHUB UPDATER
if (!defined('ABSPATH')) exit;

/**
 * UNIVERSAL GitHub Updater Class
 * Folosește: new GitHub_Plugin_Updater(__FILE__, 'username', 'repository');
 */
class GitHub_Plugin_Updater {
    
    private $plugin_file;
    private $plugin_slug;
    private $github_username;
    private $github_repository;
    private $cache_key;
    private $current_version;
    
    /**
     * Constructor
     * @param string $plugin_file    __FILE__ from main plugin file
     * @param string $github_user    GitHub username
     * @param string $github_repo    GitHub repository name
     */
    public function __construct($plugin_file, $github_user = '', $github_repo = '') {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        
        // Dacă nu sunt specificate, încercă să le obțin din header-ul pluginului
        if (empty($github_user) || empty($github_repo)) {
            $this->auto_detect_github_info();
        } else {
            $this->github_username = $github_user;
            $this->github_repository = $github_repo;
        }
        
        // Obține versiunea curentă din header
        $this->current_version = $this->get_current_version();
        
        $this->cache_key = 'github_updater_' . md5($this->plugin_slug . $this->github_username . $this->github_repository);
        
        // Hook-uri WordPress
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_api_info'], 20, 3);
        add_filter('plugin_row_meta', [$this, 'add_update_button'], 10, 4);
        add_filter('plugin_action_links_' . $this->plugin_slug, [$this, 'add_check_update_link']);
        add_action('admin_bar_menu', [$this, 'admin_bar_notice'], 999);
        add_action('admin_head', [$this, 'admin_styles']);
        
        // Debug (opțional - dezactivează în producție)
        // add_action('admin_notices', [$this, 'debug_info']);
    }
    
    /**
     * Auto-detect GitHub info from plugin header
     */
    private function auto_detect_github_info() {
        $plugin_data = get_file_data($this->plugin_file, [
            'github_user' => 'GitHub Username',
            'github_repo' => 'GitHub Repository',
            'plugin_uri'  => 'Plugin URI'
        ]);
        
        // Încearcă să extragi din Plugin URI
        if (!empty($plugin_data['plugin_uri']) && strpos($plugin_data['plugin_uri'], 'github.com') !== false) {
            preg_match('/github\.com\/([^\/]+)\/([^\/]+)/', $plugin_data['plugin_uri'], $matches);
            if (!empty($matches[1]) && !empty($matches[2])) {
                $this->github_username = $matches[1];
                $this->github_repository = rtrim($matches[2], '/');
                return;
            }
        }
        
        // Folosește valorile din header
        $this->github_username = $plugin_data['github_user'] ?: 'vadikonline1';
        $this->github_repository = $plugin_data['github_repo'] ?: basename(dirname($this->plugin_file));
    }
    
    /**
     * Get current version from plugin header
     */
    private function get_current_version() {
        $plugin_data = get_file_data($this->plugin_file, ['version' => 'Version']);
        return $plugin_data['version'] ?: '1.0.0';
    }
    
    /**
     * Get latest version from GitHub
     */
    private function get_github_version() {
        $cache_key = $this->cache_key . '_version';
        $version = get_transient($cache_key);
        
        if (false === $version || $version === 'error') {
            $url = "https://raw.githubusercontent.com/{$this->github_username}/{$this->github_repository}/main/" . basename($this->plugin_file);
            
            $response = wp_remote_get($url, [
                'timeout' => 8,
                'headers' => [
                    'Accept' => 'text/plain',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url')
                ]
            ]);
            
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                // Încearcă și cu master branch dacă main nu funcționează
                $url = str_replace('/main/', '/master/', $url);
                $response = wp_remote_get($url, ['timeout' => 8]);
                
                if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                    set_transient($cache_key, 'error', 15 * MINUTE_IN_SECONDS);
                    return false;
                }
            }
            
            $file_content = wp_remote_retrieve_body($response);
            
            if (preg_match('/\*\s*Version:\s*([0-9.]+)/i', $file_content, $matches)) {
                $version = trim($matches[1]);
                set_transient($cache_key, $version, 30 * MINUTE_IN_SECONDS);
            } else {
                set_transient($cache_key, 'error', 15 * MINUTE_IN_SECONDS);
                $version = false;
            }
        }
        
        return $version;
    }
    
    /**
     * Check if update is available
     */
    public function is_update_available() {
        $github_version = $this->get_github_version();
        return $github_version && version_compare($this->current_version, $github_version, '<');
    }
    
    /**
     * WordPress update check hook
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $github_version = $this->get_github_version();
        
        if ($github_version && version_compare($this->current_version, $github_version, '<')) {
            $plugin_data = (object) [
                'id'          => $this->plugin_slug,
                'slug'        => dirname($this->plugin_slug),
                'plugin'      => $this->plugin_slug,
                'new_version' => $github_version,
                'url'         => "https://github.com/{$this->github_username}/{$this->github_repository}",
                'package'     => "https://github.com/{$this->github_username}/{$this->github_repository}/archive/refs/heads/main.zip",
                'tested'      => get_bloginfo('version'),
                'requires'    => '5.6',
            ];
            
            $transient->response[$this->plugin_slug] = $plugin_data;
        }
        
        return $transient;
    }
    
    /**
     * Plugin info for WordPress update screen
     */
    public function plugin_api_info($result, $action, $args) {
        if ('plugin_information' !== $action || !isset($args->slug) || $args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }
        
        $github_version = $this->get_github_version();
        
        if (!$github_version) {
            return $result;
        }
        
        $plugin_data = get_plugin_data($this->plugin_file);
        
        $info = new stdClass();
        $info->name = $plugin_data['Name'];
        $info->slug = dirname($this->plugin_slug);
        $info->version = $github_version;
        $info->author = $plugin_data['Author'];
        $info->author_profile = $plugin_data['AuthorURI'];
        $info->requires = '5.6';
        $info->tested = get_bloginfo('version');
        $info->last_updated = current_time('Y-m-d');
        $info->download_link = "https://github.com/{$this->github_username}/{$this->github_repository}/archive/refs/heads/main.zip";
        $info->sections = [
            'description' => $plugin_data['Description'],
            'changelog' => $this->get_changelog($github_version)
        ];
        
        return $info;
    }
    
    /**
     * Generate changelog
     */
    private function get_changelog($version) {
        $changelog_url = "https://raw.githubusercontent.com/{$this->github_username}/{$this->github_repository}/main/CHANGELOG.md";
        $response = wp_remote_get($changelog_url, ['timeout' => 5]);
        
        if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $changelog = wp_remote_retrieve_body($response);
            return "<pre>" . esc_html($changelog) . "</pre>";
        }
        
        // Fallback changelog
        return "<h4>Version {$version}</h4>
                <ul>
                    <li>Update from GitHub repository</li>
                    <li>Bug fixes and improvements</li>
                    <li>Tested with WordPress " . get_bloginfo('version') . "</li>
                </ul>";
    }
    
    /**
     * Add update button in plugin row
     */
    public function add_update_button($links, $file, $plugin_data, $status) {
        if ($file !== $this->plugin_slug) {
            return $links;
        }
        
        if ($this->is_update_available()) {
            $update_url = wp_nonce_url(
                add_query_arg([
                    'action' => 'upgrade-plugin',
                    'plugin' => $file,
                ], self_admin_url('update.php')),
                'upgrade-plugin_' . $file
            );
            
            $links['update'] = '<a href="' . esc_url($update_url) . '" class="github-update-btn" style="font-weight:bold;color:#fff;background:#d63638;padding:3px 10px;border-radius:3px;text-decoration:none;">⬆️ Update Now</a>';
        }
        
        // Add GitHub link
        $links['github'] = '<a href="' . esc_url("https://github.com/{$this->github_username}/{$this->github_repository}") . '" target="_blank" rel="noopener">🐙 GitHub</a>';
        
        return $links;
    }
    
    /**
     * Add check update link in action links
     */
    public function add_check_update_link($actions) {
        $check_url = wp_nonce_url(
            add_query_arg([
                'github_updater_action' => 'force_check',
                'plugin' => $this->plugin_slug,
            ], admin_url('plugins.php')),
            'github_force_check'
        );
        
        $actions['check_update'] = '<a href="' . esc_url($check_url) . '" title="Check for updates">🔄 Check Update</a>';
        return $actions;
    }
    
    /**
     * Admin bar update notification
     */
    public function admin_bar_notice($admin_bar) {
        if (!current_user_can('update_plugins') || !is_admin_bar_showing()) {
            return;
        }
        
        if ($this->is_update_available()) {
            $plugin_name = get_plugin_data($this->plugin_file)['Name'];
            $admin_bar->add_node([
                'id'    => 'github-update-' . sanitize_title($plugin_name),
                'title' => '⬆️ ' . substr($plugin_name, 0, 15) . '...',
                'href'  => admin_url('plugins.php'),
                'meta'  => [
                    'class' => 'github-update-notice',
                    'title' => 'Update available for ' . $plugin_name
                ]
            ]);
        }
    }
    
    /**
     * Admin styles
     */
    public function admin_styles() {
        echo '<style>
        .github-update-btn {
            background: linear-gradient(135deg, #d63638, #a71d2a) !important;
            color: white !important;
            padding: 4px 12px !important;
            border-radius: 4px !important;
            border: none !important;
            text-decoration: none !important;
            margin: 0 5px 0 0 !important;
            display: inline-block !important;
            box-shadow: 0 2px 4px rgba(166, 29, 42, 0.3) !important;
            transition: all 0.2s ease !important;
        }
        .github-update-btn:hover {
            background: linear-gradient(135deg, #a71d2a, #851825) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(166, 29, 42, 0.4) !important;
        }
        #wpadminbar .github-update-notice > .ab-item {
            background: linear-gradient(135deg, #d63638, #a71d2a) !important;
            color: white !important;
        }
        #wpadminbar .github-update-notice > .ab-item:hover {
            background: linear-gradient(135deg, #a71d2a, #851825) !important;
        }
        </style>';
    }
    
    /**
     * Debug info (optional)
     */
    public function debug_info() {
        if (!current_user_can('manage_options') || !isset($_GET['github_debug'])) {
            return;
        }
        
        $github_version = $this->get_github_version();
        $has_update = $this->is_update_available();
        
        echo '<div class="notice notice-info"><p>';
        echo '<strong>GitHub Updater Debug (' . get_plugin_data($this->plugin_file)['Name'] . '):</strong><br>';
        echo 'Local Version: <code>' . $this->current_version . '</code><br>';
        echo 'GitHub Version: <code>' . ($github_version ?: 'Cannot fetch') . '</code><br>';
        echo 'GitHub URL: <code>' . $this->github_username . '/' . $this->github_repository . '</code><br>';
        echo 'Update Available: <code>' . ($has_update ? 'YES' : 'NO') . '</code><br>';
        echo 'Plugin Slug: <code>' . $this->plugin_slug . '</code><br>';
        echo '<small><a href="' . remove_query_arg('github_debug') . '">Hide debug</a></small>';
        echo '</p></div>';
    }
}

/**
 * Universal force check handler for all plugins
 */
add_action('admin_init', 'github_updater_handle_actions');
function github_updater_handle_actions() {
    if (!isset($_GET['github_updater_action']) || $_GET['github_updater_action'] !== 'force_check') {
        return;
    }
    
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'github_force_check')) {
        wp_die('Security check failed');
    }
    
    if (!isset($_GET['plugin'])) {
        wp_die('No plugin specified');
    }
    
    // Clear all update caches
    global $wpdb;
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_github_updater_') . '%'
        )
    );
    
    delete_site_transient('update_plugins');
    
    // Show success message
    $plugin_name = get_plugin_data(WP_PLUGIN_DIR . '/' . $_GET['plugin'])['Name'] ?: $_GET['plugin'];
    
    add_action('admin_notices', function() use ($plugin_name) {
        echo '<div class="notice notice-success is-dismissible"><p>✅ <strong>' . esc_html($plugin_name) . ':</strong> Update cache cleared. Checking for new version...</p></div>';
    });
}

/**
 * Simple function for quick implementation (backward compatibility)
 */
function github_plugin_updater($plugin_file, $github_user = '', $github_repo = '') {
    return new GitHub_Plugin_Updater($plugin_file, $github_user, $github_repo);
}
