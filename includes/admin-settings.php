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
    
    // ========== POPUP FIELDS ==========
    
    add_settings_field('popup_enabled', 'Activare Popup', 'pbp_popup_enabled_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_delay', 'Delay afișare (secunde)', 'pbp_popup_delay_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_content_type', 'Tip conținut', 'pbp_popup_content_type_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_image', 'Imagine Popup', 'pbp_popup_image_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_html_content', 'Conținut HTML / Shortcoduri', 'pbp_popup_html_content_cb', 'popup-banner-settings', 'pbp_popup_section');
    
    // ========== COUNTDOWN SETTINGS ==========
    add_settings_field('countdown_enabled', 'Activare Countdown', 'pbp_countdown_enabled_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('countdown_target_date', 'Data țintă', 'pbp_countdown_target_date_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('countdown_on_image', 'Afișare countdown pe imagine', 'pbp_countdown_on_image_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('countdown_position', 'Poziție countdown pe imagine', 'pbp_countdown_position_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('countdown_style', 'Stil Countdown', 'pbp_countdown_style_cb', 'popup-banner-settings', 'pbp_popup_section');
    
    // ========== REDIRECT ==========
    add_settings_field('popup_redirect_type', 'Acțiune la click', 'pbp_popup_redirect_type_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_redirect_url', 'URL Redirect', 'pbp_popup_redirect_url_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_redirect_page', 'Pagină Redirect', 'pbp_popup_redirect_page_cb', 'popup-banner-settings', 'pbp_popup_section');
    
    // ========== STYLE ==========
    add_settings_field('popup_bg_color', 'Culoare fundal overlay', 'pbp_popup_bg_color_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_close_color', 'Culoare buton X', 'pbp_popup_close_color_cb', 'popup-banner-settings', 'pbp_popup_section');
    add_settings_field('popup_content_bg', 'Culoare fundal conținut', 'pbp_popup_content_bg_cb', 'popup-banner-settings', 'pbp_popup_section');
    
    // ========== BANNER FIELDS ==========
    add_settings_field('banner_enabled', 'Activare Banner', 'pbp_banner_enabled_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_text', 'Text Banner', 'pbp_banner_text_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_url_type', 'Acțiune link', 'pbp_banner_url_type_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_url_text', 'Text pentru link', 'pbp_banner_url_text_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_custom_url', 'URL personalizat', 'pbp_banner_custom_url_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_page_url', 'Pagină website', 'pbp_banner_page_url_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_bg_color', 'Culoare fundal', 'pbp_banner_bg_color_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_text_color', 'Culoare text', 'pbp_banner_text_color_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_link_color', 'Culoare link', 'pbp_banner_link_color_cb', 'popup-banner-settings', 'pbp_banner_section');
    add_settings_field('banner_close_color', 'Culoare buton închidere', 'pbp_banner_close_color_cb', 'popup-banner-settings', 'pbp_banner_section');
}

// ========== SECTION CALLBACKS ==========
function pbp_popup_section_cb() { echo '<p>Configurează setările pentru popup</p>'; }
function pbp_banner_section_cb() { echo '<p>Configurează setările pentru banner</p>'; }

// ========== HELPER FUNCTIONS ==========
function pbp_is_date_expired($date_string) {
    if (empty($date_string)) return false;
    $target = strtotime($date_string);
    $now = current_time('timestamp');
    return ($target && $now > $target);
}

function pbp_is_countdown_expired() {
    $options = get_option('pbp_settings');
    if (($options['countdown_enabled'] ?? '0') != '1') return false;
    $target_date = $options['countdown_target_date'] ?? '';
    return pbp_is_date_expired($target_date);
}

// ========== POPUP CALLBACKS ==========
function pbp_popup_enabled_cb() {
    $options = get_option('pbp_settings');
    $checked = checked(($options['popup_enabled'] ?? '0'), '1', false);
    echo '<label><input type="checkbox" name="pbp_settings[popup_enabled]" value="1" ' . $checked . '> Activează popup</label>';
    
    if (pbp_is_countdown_expired()) {
        echo '<p style="color:red;margin-top:5px;">⚠️ <strong>Countdown-ul a expirat! Popup-ul este dezactivat automat.</strong></p>';
    }
}

function pbp_popup_delay_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="number" name="pbp_settings[popup_delay]" value="' . esc_attr($options['popup_delay'] ?? '5') . '" min="1" max="60">';
    echo '<p class="description">Secunde după care apare popup-ul</p>';
}

function pbp_popup_content_type_cb() {
    $options = get_option('pbp_settings');
    $current = $options['popup_content_type'] ?? 'image';
    ?>
    <select name="pbp_settings[popup_content_type]" id="popup_content_type">
        <option value="image" <?php selected($current, 'image'); ?>>📷 Imagine</option>
        <option value="html" <?php selected($current, 'html'); ?>>💻 HTML / Shortcoduri</option>
    </select>
    <?php
}

function pbp_popup_image_cb() {
    $options = get_option('pbp_settings');
    $image_id = $options['popup_image'] ?? '';
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    
    echo '<div class="pbp-image-upload" id="pbp_image_upload_wrapper">';
    echo '<input type="hidden" name="pbp_settings[popup_image]" id="popup_image_id" value="' . esc_attr($image_id) . '">';
    echo '<div id="popup_image_preview">';
    if ($image_url) echo '<img src="' . esc_url($image_url) . '" style="max-width:300px;height:auto;border-radius:8px;">';
    echo '</div>';
    echo '<button type="button" class="button" id="upload_popup_image">📁 Alege imagine</button>';
    if ($image_url) echo '<button type="button" class="button" id="remove_popup_image">🗑️ Șterge imagine</button>';
    echo '</div>';
}

function pbp_popup_html_content_cb() {
    $options = get_option('pbp_settings');
    $content = $options['popup_html_content'] ?? '';
    
    // Setări pentru wp_editor cu suport shortcoduri
    $editor_settings = array(
        'textarea_name' => 'pbp_settings[popup_html_content]',
        'textarea_rows' => 20,
        'media_buttons' => true,
        'drag_drop_upload' => true,
        'teeny' => false,
        'editor_class' => 'pbp-html-editor',
        'quicktags' => true,
        'tinymce' => array(
            'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
            'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
        ),
        'wpautop' => true,
    );
    
    echo '<div id="pbp_html_wrapper">';
    echo '<div class="pbp-shortcode-notice" style="background:#f0f6ff;border-left:4px solid #007cba;padding:10px;margin-bottom:15px;">';
    echo '<strong>📌 Suport pentru CSS și Shortcoduri</strong><br>';
    echo '✅ Poți folosi <code>&lt;style&gt;...&lt;/style&gt;</code> pentru CSS personalizat.<br>';
    echo '✅ Poți folosi orice shortcod WordPress: <code>[contact-form-7 id="123"]</code>, <code>[products]</code>, etc.<br>';
    echo '✅ Countdown placeholder: <code>&lt;div id="pbp-countdown"&gt;&lt;/div&gt;</code>';
    echo '</div>';
    
    wp_editor($content, 'pbp_html_content_editor', $editor_settings);
    
    echo '<p class="description" style="margin-top:10px;">';
    echo '<strong>💡 Exemplu complet cu CSS și countdown:</strong><br>';
    echo '<code>&lt;style&gt;<br>';
    echo '.my-box { background: #f5f5f5; padding: 20px; border-radius: 10px; }<br>';
    echo '.my-title { color: #c41e3a; font-size: 24px; }<br>';
    echo '&lt;/style&gt;<br>';
    echo '&lt;div class="my-box"&gt;<br>';
    echo '&nbsp;&nbsp;&lt;h2 class="my-title"&gt;Ofertă Specială!&lt;/h2&gt;<br>';
    echo '&nbsp;&nbsp;&lt;div id="pbp-countdown"&gt;&lt;/div&gt;<br>';
    echo '&nbsp;&nbsp;[contact-form-7 id="123"]<br>';
    echo '&lt;/div&gt;</code>';
    echo '</p>';
    echo '</div>';
}

// ========== COUNTDOWN CALLBACKS ==========
function pbp_countdown_enabled_cb() {
    $options = get_option('pbp_settings');
    $checked = checked($options['countdown_enabled'] ?? '0', '1', false);
    echo '<label><input type="checkbox" name="pbp_settings[countdown_enabled]" value="1" id="countdown_enabled" ' . $checked . '> Activează countdown timer</label>';
    echo '<p class="description">Dacă activezi countdown-ul și timpul a expirat, popup-ul NU se va mai afișa</p>';
}

function pbp_countdown_target_date_cb() {
    $options = get_option('pbp_settings');
    $date = $options['countdown_target_date'] ?? '';
    ?>
    <div id="countdown_date_wrapper">
        <input type="datetime-local" name="pbp_settings[countdown_target_date]" value="<?php echo esc_attr($date); ?>" class="regular-text" style="width: 250px;">
        <p class="description">Alege data și ora exactă când expiră countdown-ul</p>
        <?php
        if (!empty($date) && pbp_is_date_expired($date)) {
            echo '<p style="color:red;"><strong>⚠️ Data introdusă a expirat deja! Popup-ul nu se va afișa.</strong></p>';
        }
        ?>
    </div>
    <?php
}

function pbp_countdown_on_image_cb() {
    $options = get_option('pbp_settings');
    $checked = checked($options['countdown_on_image'] ?? '1', '1', false);
    echo '<label><input type="checkbox" name="pbp_settings[countdown_on_image]" value="1" id="countdown_on_image" ' . $checked . '> Afișează countdown-ul suprapus pe imagine</label>';
    echo '<p class="description">Dacă debifezi, countdown-ul nu se va afișa deloc pe imagine</p>';
}

function pbp_countdown_position_cb() {
    $options = get_option('pbp_settings');
    $position = $options['countdown_position'] ?? 'bottom-center';
    ?>
    <select name="pbp_settings[countdown_position]" id="countdown_position">
        <option value="top-left" <?php selected($position, 'top-left'); ?>>Stânga sus</option>
        <option value="top-center" <?php selected($position, 'top-center'); ?>>Centru sus</option>
        <option value="top-right" <?php selected($position, 'top-right'); ?>>Dreapta sus</option>
        <option value="middle-left" <?php selected($position, 'middle-left'); ?>>Stânga mijloc</option>
        <option value="middle-center" <?php selected($position, 'middle-center'); ?>>Centru mijloc</option>
        <option value="middle-right" <?php selected($position, 'middle-right'); ?>>Dreapta mijloc</option>
        <option value="bottom-left" <?php selected($position, 'bottom-left'); ?>>Stânga jos</option>
        <option value="bottom-center" <?php selected($position, 'bottom-center'); ?>>Centru jos</option>
        <option value="bottom-right" <?php selected($position, 'bottom-right'); ?>>Dreapta jos</option>
    </select>
    <p class="description">Poziția countdown-ului pe imagine</p>
    <?php
}

function pbp_countdown_style_cb() {
    $options = get_option('pbp_settings');
    $style = $options['countdown_style'] ?? 'default';
    ?>
    <select name="pbp_settings[countdown_style]" id="countdown_style">
        <option value="default" <?php selected($style, 'default'); ?>>🔥 Default (gradient mov)</option>
        <option value="dark" <?php selected($style, 'dark'); ?>>🌙 Dark (negru cu text alb)</option>
        <option value="light" <?php selected($style, 'light'); ?>>☀️ Light (alb cu umbră)</option>
        <option value="custom" <?php selected($style, 'custom'); ?>>🎨 Personalizat (folosește CSS-ul tău)</option>
    </select>
    <?php
}

// ========== REDIRECT CALLBACKS ==========
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

// ========== STYLE CALLBACKS ==========
function pbp_popup_bg_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[popup_bg_color]" value="' . esc_attr($options['popup_bg_color'] ?? 'rgba(0,0,0,0.8)') . '" class="pbp-color-picker" data-alpha="true">';
}

function pbp_popup_close_color_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[popup_close_color]" value="' . esc_attr($options['popup_close_color'] ?? '#ffffff') . '" class="pbp-color-picker">';
}

function pbp_popup_content_bg_cb() {
    $options = get_option('pbp_settings');
    echo '<input type="text" name="pbp_settings[popup_content_bg]" value="' . esc_attr($options['popup_content_bg'] ?? '#ffffff') . '" class="pbp-color-picker">';
}

// ========== BANNER CALLBACKS ==========
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

// ========== FUNCȚIE SANITIZARE PERMISIVĂ PENTRU CSS ==========
function pbp_sanitize_html_with_css($content) {
    // Permite mai multe tag-uri și atribute
    $allowed_tags = wp_kses_allowed_html('post');
    
    // Adaugă tag-ul style
    $allowed_tags['style'] = array(
        'type' => true,
        'media' => true,
        'scoped' => true,
    );
    
    // Adaugă atributul 'style' pentru toate tag-urile existente
    foreach ($allowed_tags as $tag => $attributes) {
        $allowed_tags[$tag]['style'] = true;
        $allowed_tags[$tag]['class'] = true;
        $allowed_tags[$tag]['id'] = true;
    }
    
    // Permite și tag-ul link pentru fonturi externe
    $allowed_tags['link'] = array(
        'href' => true,
        'rel' => true,
        'type' => true,
        'media' => true,
    );
    
    // Permite meta viewport
    $allowed_tags['meta'] = array(
        'name' => true,
        'content' => true,
        'charset' => true,
    );
    
    return wp_kses($content, $allowed_tags);
}

// ========== SANITIZE ==========
function pbp_sanitize_settings($input) {
    $html_content = isset($input['popup_html_content']) ? $input['popup_html_content'] : '';
    
    return [
        'popup_enabled' => (isset($input['popup_enabled']) && !pbp_is_countdown_expired()) ? '1' : '0',
        'popup_delay' => absint($input['popup_delay']),
        'popup_content_type' => sanitize_text_field($input['popup_content_type']),
        'popup_image' => absint($input['popup_image']),
        'popup_html_content' => pbp_sanitize_html_with_css($html_content),
        'countdown_enabled' => isset($input['countdown_enabled']) ? '1' : '0',
        'countdown_target_date' => sanitize_text_field($input['countdown_target_date']),
        'countdown_on_image' => isset($input['countdown_on_image']) ? '1' : '0',
        'countdown_position' => sanitize_text_field($input['countdown_position']),
        'countdown_style' => sanitize_text_field($input['countdown_style']),
        'popup_redirect_type' => sanitize_text_field($input['popup_redirect_type']),
        'popup_redirect_url' => esc_url_raw($input['popup_redirect_url']),
        'popup_redirect_page' => absint($input['popup_redirect_page']),
        'popup_bg_color' => sanitize_text_field($input['popup_bg_color']),
        'popup_close_color' => sanitize_hex_color($input['popup_close_color']),
        'popup_content_bg' => sanitize_hex_color($input['popup_content_bg']),
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

// ========== SETTINGS PAGE ==========
function pbp_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1>Popup & Banner Settings</h1>
        
        <?php if (pbp_is_countdown_expired()): ?>
        <div class="notice notice-warning">
            <p>⚠️ <strong>Countdown-ul a expirat!</strong> Popup-ul este dezactivat automat. Pentru a reactiva popup-ul, modifică data țintă sau dezactivează countdown-ul.</p>
        </div>
        <?php endif; ?>
        
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
    .pbp-image-upload img {max-width:300px;height:auto;border:1px solid #ddd;margin:10px 0;border-radius:8px;}
    .pbp-color-picker {width:120px;}
    .form-table th {width:220px;}
    #pbp_html_wrapper .wp-editor-container {border-radius:8px;}
    #pbp_html_wrapper .wp-editor-area {background:#f9f9f9;}
    .pbp-shortcode-notice {margin-bottom:15px;}
    </style>
    
    <script>
    jQuery(function($) {
        function toggleContentFields() {
            var contentType = $('#popup_content_type').val();
            var countdownEnabled = $('#countdown_enabled').is(':checked');
            
            if (contentType === 'image') {
                $('#pbp_image_upload_wrapper').closest('tr').show();
                $('#pbp_html_wrapper').closest('tr').hide();
                if (countdownEnabled) {
                    $('#countdown_on_image').closest('tr').show();
                    $('#countdown_position').closest('tr').show();
                } else {
                    $('#countdown_on_image').closest('tr').hide();
                    $('#countdown_position').closest('tr').hide();
                }
            } else {
                $('#pbp_image_upload_wrapper').closest('tr').hide();
                $('#pbp_html_wrapper').closest('tr').show();
                $('#countdown_on_image').closest('tr').hide();
                $('#countdown_position').closest('tr').hide();
            }
            
            if (countdownEnabled) {
                $('#countdown_date_wrapper').closest('tr').show();
                $('#countdown_style').closest('tr').show();
            } else {
                $('#countdown_date_wrapper').closest('tr').hide();
                $('#countdown_style').closest('tr').hide();
                $('#countdown_on_image').closest('tr').hide();
                $('#countdown_position').closest('tr').hide();
            }
        }
        
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
        
        $('#remove_popup_image').click(function(e) {
            e.preventDefault();
            $('#popup_image_id').val('');
            $('#popup_image_preview').html('');
            $(this).hide();
        });
        
        $('.pbp-color-picker').each(function() {
            $(this).wpColorPicker();
        });
        
        function toggleRedirectFields() {
            var popupType = $('#popup_redirect_type').val();
            $('input[name="pbp_settings[popup_redirect_url]"]').closest('tr').toggle(popupType === 'url');
            $('select[name="pbp_settings[popup_redirect_page]"]').closest('tr').toggle(popupType === 'page');
            
            var bannerType = $('#banner_url_type').val();
            $('input[name="pbp_settings[banner_custom_url]"]').closest('tr').toggle(bannerType === 'url');
            $('select[name="pbp_settings[banner_page_url]"]').closest('tr').toggle(bannerType === 'page');
            
            toggleContentFields();
        }
        
        toggleRedirectFields();
        
        $('#popup_redirect_type, #banner_url_type, #popup_content_type, #countdown_enabled').on('change', function() {
            toggleRedirectFields();
        });
    });
    </script>
    <?php
}
