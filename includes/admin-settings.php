<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'pbp_add_admin_menu');
function pbp_add_admin_menu() {
    add_options_page('Popup & Banner', 'Popup & Banner', 'manage_options', 'popup-banner-settings', 'pbp_settings_page');
}

add_action('admin_init', 'pbp_register_settings');
function pbp_register_settings() {
    register_setting('pbp_settings_group', 'pbp_settings', 'pbp_sanitize_settings');
    
    add_settings_section('pbp_popup_section', 'Setări Popup', 'pbp_popup_section_cb', 'popup-banner-settings');
    add_settings_section('pbp_banner_section', 'Setări Banner', 'pbp_banner_section_cb', 'popup-banner-settings');
    
    // Popup fields
    $popup_fields = [
        'popup_enabled' => 'Activare Popup',
        'popup_delay' => 'Delay afișare (secunde)',
        'popup_image' => 'Imagine Popup',
        'popup_redirect_type' => 'Acțiune la click',
        'popup_redirect_url' => 'URL Redirect',
        'popup_redirect_page' => 'Pagină Redirect',
        'popup_bg_color' => 'Culoare fundal',
        'popup_close_color' => 'Culoare buton X',
        'popup_close_bg' => 'Fundal buton X',
    ];
    
    foreach ($popup_fields as $field => $label) {
        add_settings_field($field, $label, "pbp_{$field}_cb", 'popup-banner-settings', 'pbp_popup_section');
    }
    
    // Banner fields
    $banner_fields = [
        'banner_enabled' => 'Activare Banner',
        'banner_text' => 'Text Banner',
        'banner_url_type' => 'Acțiune link',
        'banner_url_text' => 'Text pentru link',
        'banner_custom_url' => 'URL personalizat',
        'banner_page_url' => 'Pagină website',
        'banner_bg_color' => 'Culoare fundal',
        'banner_text_color' => 'Culoare text',
        'banner_link_color' => 'Culoare link',
        'banner_close_color' => 'Culoare buton închidere',
    ];
    
    foreach ($banner_fields as $field => $label) {
        add_settings_field($field, $label, "pbp_{$field}_cb", 'popup-banner-settings', 'pbp_banner_section');
    }
}

// Callback functions
function pbp_popup_section_cb() { echo '<p>Configurează setările pentru popup</p>'; }
function pbp_banner_section_cb() { echo '<p>Configurează setările pentru banner</p>'; }

function pbp_popup_enabled_cb() {
    $options = get_option('pbp_settings');
    echo '<label><input type="checkbox" name="pbp_settings[popup_enabled]" value="1" ' . checked($options['popup_enabled'] ?? '0', '1', false) . '> Activează popup</label>';
}

function pbp_popup_delay_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="number" name="pbp_settings[popup_delay]" value="' . esc_attr($options['popup_delay'] ?? '5') . '" min="1" max="60">';
    echo '<p class="description">Secunde după care apare popup-ul</p>';
}

function pbp_popup_image_cb() {
    $options = get_option('pbp_settings');
    $image_id = $options['popup_image'] ?? '';
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    
    echo '<div class="pbp-image-upload">';
    echo '<input type="hidden" name="pbp_settings[popup_image]" id="popup_image_id" value="' . esc_attr($image_id) . '">';
    echo '<div id="popup_image_preview">';
    if ($image_url) echo '<img src="' . esc_url($image_url) . '" style="max-width:300px;height:auto;">';
    echo '</div>';
    echo '<button type="button" class="button" id="upload_popup_image">Alege imagine</button>';
    if ($image_url) echo '<button type="button" class="button" id="remove_popup_image">Șterge imagine</button>';
    echo '</div>';
}

function pbp_popup_redirect_type_cb() {
    $options = get_option('pbp_settings');
    $current = $options['popup_redirect_type'] ?? 'none';
    
    echo '<select name="pbp_settings[popup_redirect_type]" id="popup_redirect_type">';
    echo '<option value="none" ' . selected($current, 'none', false) . '>Niciuna</option>';
    echo '<option value="url" ' . selected($current, 'url', false) . '>Redirect către URL</option>';
    echo '<option value="page" ' . selected($current, 'page', false) . '>Redirect către pagină</option>';
    echo '</select>';
}

function pbp_popup_redirect_url_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="url" name="pbp_settings[popup_redirect_url]" value="' . esc_url($options['popup_redirect_url'] ?? '') . '" class="regular-text">';
    echo '<p class="description">Completează doar dacă ai selectat "Redirect către URL"</p>';
}

function pbp_popup_redirect_page_cb() {
    $options = get_option('pbp_settings');
    $page_id = $options['popup_redirect_page'] ?? '';
    
    wp_dropdown_pages([
        'name' => 'pbp_settings[popup_redirect_page]',
        'selected' => $page_id,
        'show_option_none' => 'Selectează o pagină',
        'option_none_value' => ''
    ]);
    echo '<p class="description">Completează doar dacă ai selectat "Redirect către pagină"</p>';
}

function pbp_popup_bg_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[popup_bg_color]" value="' . esc_attr($options['popup_bg_color'] ?? 'rgba(0,0,0,0.8)') . '" class="pbp-color-picker" data-alpha="true">';
}

function pbp_popup_close_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[popup_close_color]" value="' . esc_attr($options['popup_close_color'] ?? '#ffffff') . '" class="pbp-color-picker">';
}

function pbp_popup_close_bg_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[popup_close_bg]" value="' . esc_attr($options['popup_close_bg'] ?? '#000000') . '" class="pbp-color-picker">';
}

// Banner callbacks
function pbp_banner_enabled_cb() {
    $options = get_option('pbp_settings');
    echo '<label><input type="checkbox" name="pbp_settings[banner_enabled]" value="1" ' . checked($options['banner_enabled'] ?? '0', '1', false) . '> Activează banner</label>';
}

function pbp_banner_text_cb() {
    $options = get_option('pbp_settings');
    echo '<textarea name="pbp_settings[banner_text]" rows="3" cols="50" class="large-text">' . esc_textarea($options['banner_text'] ?? '') . '</textarea>';
}

function pbp_banner_url_type_cb() {
    $options = get_option('pbp_settings');
    $current = $options['banner_url_type'] ?? 'none';
    
    echo '<select name="pbp_settings[banner_url_type]" id="banner_url_type">';
    echo '<option value="none" ' . selected($current, 'none', false) . '>Niciuna</option>';
    echo '<option value="url" ' . selected($current, 'url', false) . '>URL personalizat</option>';
    echo '<option value="page" ' . selected($current, 'page', false) . '>Pagină website</option>';
    echo '</select>';
}

function pbp_banner_url_text_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[banner_url_text]" value="' . esc_attr($options['banner_url_text'] ?? 'Află mai multe') . '" class="regular-text">';
}

function pbp_banner_custom_url_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="url" name="pbp_settings[banner_custom_url]" value="' . esc_url($options['banner_custom_url'] ?? '') . '" class="regular-text">';
    echo '<p class="description">Completează doar dacă ai selectat "URL personalizat"</p>';
}

function pbp_banner_page_url_cb() {
    $options = get_option('pbp_settings');
    $page_id = $options['banner_page_url'] ?? '';
    
    wp_dropdown_pages([
        'name' => 'pbp_settings[banner_page_url]',
        'selected' => $page_id,
        'show_option_none' => 'Selectează o pagină',
        'option_none_value' => ''
    ]);
    echo '<p class="description">Completează doar dacă ai selectat "Pagină website"</p>';
}

function pbp_banner_bg_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[banner_bg_color]" value="' . esc_attr($options['banner_bg_color'] ?? '#f8d7da') . '" class="pbp-color-picker">';
}

function pbp_banner_text_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[banner_text_color]" value="' . esc_attr($options['banner_text_color'] ?? '#721c24') . '" class="pbp-color-picker">';
}

function pbp_banner_link_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[banner_link_color]" value="' . esc_attr($options['banner_link_color'] ?? '#721c24') . '" class="pbp-color-picker">';
}

function pbp_banner_close_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[banner_close_color]" value="' . esc_attr($options['banner_close_color'] ?? '#000000') . '" class="pbp-color-picker">';
}

function pbp_sanitize_settings($input) {
    return [
        'popup_enabled' => isset($input['popup_enabled']) ? '1' : '0',
        'popup_delay' => absint($input['popup_delay']),
        'popup_image' => absint($input['popup_image']),
        'popup_redirect_type' => sanitize_text_field($input['popup_redirect_type']),
        'popup_redirect_url' => esc_url_raw($input['popup_redirect_url']),
        'popup_redirect_page' => absint($input['popup_redirect_page']),
        'popup_bg_color' => sanitize_text_field($input['popup_bg_color']),
        'popup_close_color' => sanitize_hex_color($input['popup_close_color']),
        'popup_close_bg' => sanitize_hex_color($input['popup_close_bg']),
        'banner_enabled' => isset($input['banner_enabled']) ? '1' : '0',
        'banner_text' => wp_kses_post($input['banner_text']),
        'banner_url_type' => sanitize_text_field($input['banner_url_type']),
        'banner_url_text' => sanitize_text_field($input['banner_url_text']),
        'banner_custom_url' => esc_url_raw($input['banner_custom_url']),
        'banner_page_url' => absint($input['banner_page_url']),
        'banner_bg_color' => sanitize_hex_color($input['banner_bg_color']),
        'banner_text_color' => sanitize_hex_color($input['banner_text_color']),
        'banner_link_color' => sanitize_hex_color($input['banner_link_color']),
        'banner_close_color' => sanitize_hex_color($input['banner_close_color']),
    ];
}

function pbp_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1>Popup & Banner Settings</h1>
        
        <form method="post" action="options.php" id="pbp-settings-form">
            <?php
            settings_fields('pbp_settings_group');
            do_settings_sections('popup-banner-settings');
            submit_button();
            ?>
        </form>
    </div>
    
    <style>
    .pbp-image-upload {margin-bottom:10px;}
    .pbp-image-upload img {max-width:300px;height:auto;border:1px solid #ddd;margin:10px 0;}
    .pbp-color-picker {width:100px;}
    </style>
    
    <script>
    jQuery(function($) {
        // Upload imagine
        $('#upload_popup_image').click(function(e) {
            e.preventDefault();
            var frame = wp.media({title:'Alege imaginea', button:{text:'Utilizează'}, multiple:false});
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#popup_image_id').val(attachment.id);
                $('#popup_image_preview').html('<img src="'+attachment.url+'" style="max-width:300px;height:auto;">');
                $('#remove_popup_image').show();
            });
            frame.open();
        });
        
        // Șterge imagine
        $('#remove_popup_image').click(function(e) {
            e.preventDefault();
            $('#popup_image_id').val('');
            $('#popup_image_preview').html('');
            $(this).hide();
        });
        
        // Color picker
        $('.pbp-color-picker').each(function() {
            var $this = $(this);
            var alpha = $this.data('alpha') === true;
            $this.wpColorPicker();
        });
        
        // Ascunde/arată câmpuri condiționale
        function toggleFields() {
            var popupType = $('#popup_redirect_type').val();
            $('input[name="pbp_settings[popup_redirect_url]"]').closest('tr').toggle(popupType === 'url');
            $('select[name="pbp_settings[popup_redirect_page]"]').closest('tr').toggle(popupType === 'page');
            
            var bannerType = $('#banner_url_type').val();
            $('input[name="pbp_settings[banner_custom_url]"]').closest('tr').toggle(bannerType === 'url');
            $('select[name="pbp_settings[banner_page_url]"]').closest('tr').toggle(bannerType === 'page');
        }
        
        // La încărcare
        toggleFields();
        
        // La schimbare
        $('form').on('change', '#popup_redirect_type, #banner_url_type', function() {
            toggleFields();
        });
    });
    </script>
    <?php
}