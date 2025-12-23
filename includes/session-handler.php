<?php
// Evită accesul direct
if (!defined('ABSPATH')) {
    exit;
}

// Verifică și setează cookie pentru banner (sesiune browser)
function pbp_check_banner_dismissed() {
    if (isset($_COOKIE['pbp_banner_dismissed']) && $_COOKIE['pbp_banner_dismissed'] === 'true') {
        return true;
    }
    return false;
}

// Verifică și setează cookie pentru popup (sesiune browser)
function pbp_check_popup_dismissed() {
    if (isset($_COOKIE['pbp_popup_dismissed']) && $_COOKIE['pbp_popup_dismissed'] === 'true') {
        return true;
    }
    return false;
}

// AJAX pentru setare cookie banner (sesiune browser)
add_action('wp_ajax_pbp_dismiss_banner', 'pbp_ajax_dismiss_banner');
add_action('wp_ajax_nopriv_pbp_dismiss_banner', 'pbp_ajax_dismiss_banner');
function pbp_ajax_dismiss_banner() {
    // Cookie pentru sesiune (se va șterge la închiderea browserului)
    // 0 = expire la sfârșitul sesiunii browserului
    setcookie('pbp_banner_dismissed', 'true', 0, '/', '', false, true);
    wp_send_json_success();
}

// AJAX pentru setare cookie popup (sesiune browser)
add_action('wp_ajax_pbp_dismiss_popup', 'pbp_ajax_dismiss_popup');
add_action('wp_ajax_nopriv_pbp_dismiss_popup', 'pbp_ajax_dismiss_popup');
function pbp_ajax_dismiss_popup() {
    // Cookie pentru sesiune (se va șterge la închiderea browserului)
    // 0 = expire la sfârșitul sesiunii browserului
    setcookie('pbp_popup_dismissed', 'true', 0, '/', '', false, true);
    wp_send_json_success();
}