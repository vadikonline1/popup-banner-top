<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', 'pbp_frontend_assets');
add_action('admin_enqueue_scripts', 'pbp_admin_assets');

function pbp_frontend_assets() {
    $options = get_option('pbp_settings');
    
    if ((isset($options['popup_enabled']) && $options['popup_enabled'] == '1') || 
        (isset($options['banner_enabled']) && $options['banner_enabled'] == '1')) {
        
        // CSS simplificat
        echo '<style>
        .pbp-banner-top {
            position:fixed;
            top:0;
            left:0;
            width:100%;
            z-index:9998;
            margin:0;
            transition:opacity 0.3s, transform 0.3s;
        }
        .pbp-container {
            width:auto;
            padding:0 10px;
            margin:0 auto;
        }
        .pbp-row {
            display:flex;
            align-items:center;
            justify-content:space-between;
        }
        .pbp-col-10 {flex:0 0 83.33%;max-width:83.33%;}
        .pbp-col-2 {flex:0 0 16.66%;max-width:16.66%;text-align:right;}
        .pbp-btn-close {
            background:none;
            border:none;
            font-size:20px;
            cursor:pointer;
            padding:0;
            line-height:1;
        }
        @media (max-width:768px) {
            .pbp-col-10 {flex:0 0 70%;max-width:70%;}
            .pbp-col-2 {flex:0 0 30%;max-width:30%;}
        }
        @media (max-width:480px) {
            .pbp-col-10 {flex:0 0 65%;max-width:65%;}
            .pbp-col-2 {flex:0 0 35%;max-width:35%;}
        }
        </style>';
    }
}

function pbp_admin_assets($hook) {
    if ($hook != 'settings_page_popup-banner-settings') return;
    
    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_script('pbp-color-picker-alpha', PBP_PLUGIN_URL . 'assets/js/wp-color-picker-alpha.min.js', ['wp-color-picker'], PBP_VERSION, true);
    wp_enqueue_script('jquery');

}
