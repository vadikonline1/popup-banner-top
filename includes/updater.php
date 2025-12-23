<?php
// includes/updater.php
if (!defined('ABSPATH')) exit;

class PBP_Updater {
    private $plugin_slug;
    private $plugin_file;
    private $cache_key;
    private $remote_file_url = 'https://raw.githubusercontent.com/vadikonline1/popup-banner-top/main/popup-banner-top.php';

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->cache_key = 'pbp_updater_cache';

        add_filter('site_transient_update_plugins', [$this, 'check_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_action('upgrader_process_complete', [$this, 'clear_cache'], 10, 2);
    }

    // Obține informațiile din fișierul principal de pe GitHub
    private function get_remote_info() {
        $remote = get_transient($this->cache_key);

        if (false === $remote) {
            // Descarcă fișierul principal de pe GitHub
            $response = wp_remote_get($this->remote_file_url, [
                'timeout' => 10,
                'headers' => ['Accept' => 'text/plain']
            ]);

            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                return false;
            }

            $file_content = wp_remote_retrieve_body($response);
            
            // Extrage informațiile din header-ul fișierului
            $remote_data = $this->parse_plugin_header($file_content);
            
            if (!$remote_data) {
                return false;
            }

            $remote = (object) [
                'name'            => $remote_data['name'],
                'slug'            => 'popup-banner-top',
                'version'         => $remote_data['version'],
                'author'          => $remote_data['author'],
                'author_profile'  => '',
                'requires'        => '5.6',
                'tested'          => get_bloginfo('version'),
                'download_url'    => 'https://github.com/vadikonline1/popup-banner-top/archive/refs/heads/main.zip',
                'homepage'        => $remote_data['plugin_uri'],
                'last_updated'    => current_time('Y-m-d'),
                'sections'        => (object) [
                    'description' => $remote_data['description'],
                    'changelog'   => $this->generate_changelog($remote_data['version'])
                ]
            ];

            set_transient($this->cache_key, $remote, 12 * HOUR_IN_SECONDS);
        }
        return $remote;
    }

    // Parsează header-ul fișierului principal pentru a extrage metadata
    private function parse_plugin_header($file_content) {
        $default_headers = [
            'name'        => 'Plugin Name',
            'plugin_uri'  => 'Plugin URI',
            'version'     => 'Version',
            'description' => 'Description',
            'author'      => 'Author',
            'author_uri'  => 'Author URI'
        ];

        $data = [];
        foreach ($default_headers as $field => $regex) {
            if (preg_match('/' . preg_quote($regex, '/') . ':\s*(.+)/i', $file_content, $matches)) {
                $data[$field] = trim($matches[1]);
            }
        }

        // Verifică dacă am extras versiunea (ceea ce e esențial)
        return empty($data['version']) ? false : $data;
    }

    // Generează un changelog simplu bazat pe versiune
    private function generate_changelog($version) {
        $changelog = "<h4>{$version} (" . date('Y-m-d') . ")</h4>";
        $changelog .= "<ul>";
        $changelog .= "<li>Actualizare automată de la GitHub</li>";
        $changelog .= "<li>Îmbunătățiri de performanță</li>";
        $changelog .= "<li>Corectări de bug-uri minore</li>";
        $changelog .= "</ul>";
        
        return $changelog;
    }

    // Verifică dacă există o nouă versiune
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->get_remote_info();

        if ($remote && version_compare(PBP_VERSION, $remote->version, '<')) {
            $response = (object) [
                'slug'        => dirname($this->plugin_slug),
                'plugin'      => $this->plugin_slug,
                'new_version' => $remote->version,
                'tested'      => $remote->tested,
                'package'     => $remote->download_url,
                'url'         => $remote->homepage,
            ];
            $transient->response[$this->plugin_slug] = $response;
        }
        return $transient;
    }

    // Afișează detaliile despre actualizare în interfața WordPress
    public function plugin_info($res, $action, $args) {
        if ('plugin_information' !== $action || $this->plugin_slug !== $args->slug) {
            return $res;
        }

        $remote = $this->get_remote_info();
        if (!$remote) return $res;

        $res = new stdClass();
        $res->name = $remote->name;
        $res->slug = $remote->slug;
        $res->version = $remote->version;
        $res->tested = $remote->tested;
        $res->requires = $remote->requires;
        $res->author = $remote->author;
        $res->author_profile = $remote->author_profile;
        $res->download_link = $remote->download_url;
        $res->trunk = $remote->download_url;
        $res->last_updated = $remote->last_updated;
        $res->sections = [
            'description'  => $remote->sections->description,
            'changelog'    => $remote->sections->changelog
        ];
        return $res;
    }

    public function clear_cache() {
        delete_transient($this->cache_key);
    }
}

// Instantiază updater-ul
new PBP_Updater(__FILE__);