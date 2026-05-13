<?php
if (!defined('ABSPATH')) exit;

add_action('wp_footer', 'pbp_display_popup');
add_action('wp_body_open', 'pbp_display_banner');

function pbp_should_show_popup() {
    $options = get_option('pbp_settings');
    
    if (($options['popup_enabled'] ?? '0') != '1') return false;
    if (isset($_COOKIE['pbp_popup_dismissed'])) return false;
    
    if (isset($options['countdown_enabled']) && $options['countdown_enabled'] == '1') {
        $target_date = $options['countdown_target_date'] ?? '';
        if (!empty($target_date)) {
            $target_timestamp = strtotime($target_date);
            $now = current_time('timestamp');
            if ($now > $target_timestamp) return false;
        }
    }
    
    return true;
}

function pbp_display_banner() {
    $options = get_option('pbp_settings');
    if (($options['banner_enabled'] ?? '0') != '1') return;
    if (isset($_COOKIE['pbp_banner_dismissed'])) return;
    
    $text = $options['banner_text'] ?? '';
    if (empty($text)) return;
    
    $url_type = $options['banner_url_type'] ?? 'none';
    $url_text = $options['banner_url_text'] ?? 'Află mai multe';
    
    $url = '#';
    if ($url_type === 'url' && !empty($options['banner_custom_url'])) {
        $url = esc_url($options['banner_custom_url']);
    } elseif ($url_type === 'page' && !empty($options['banner_page_url'])) {
        $url = get_permalink(intval($options['banner_page_url']));
    }
    
    $bg_color = esc_attr($options['banner_bg_color'] ?? '#f8d7da');
    $text_color = esc_attr($options['banner_text_color'] ?? '#721c24');
    $link_color = esc_attr($options['banner_link_color'] ?? '#721c24');
    $close_color = esc_attr($options['banner_close_color'] ?? '#000000');
    ?>
    <div class="pbp-banner-top" id="pbpTopBanner" style="background:<?php echo $bg_color; ?>;color:<?php echo $text_color; ?>; font-size: small;">
        <div class="pbp-container">
            <div class="pbp-row">
                <div class="pbp-col-10">
                    <p style="color:<?php echo $text_color; ?>;margin:0;padding:5px 0;"><?php echo wp_kses_post($text); ?></p>
                </div>
                <div class="pbp-col-2">
                    <?php if ($url_type !== 'none'): ?>
                        <a href="<?php echo $url; ?>" class="pbp-btn-link" target="_blank" style="color:<?php echo $link_color; ?>;text-decoration:underline;margin-right:10px;">
                            <?php echo esc_html($url_text); ?>
                        </a>
                    <?php endif; ?>
                    <button class="pbp-btn-close" id="pbpBannerClose" style="color:<?php echo $close_color; ?>;background:none;border:none;font-size:20px;cursor:pointer;padding:0;line-height:1;">×</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var banner = document.getElementById('pbpTopBanner');
        var closeBtn = document.getElementById('pbpBannerClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                if (banner) {
                    banner.style.opacity = '0';
                    banner.style.transform = 'translateY(-100%)';
                    setTimeout(function() {
                        banner.style.display = 'none';
                        document.cookie = "pbp_banner_dismissed=true; path=/; max-age=86400";
                        document.body.style.paddingTop = '0';
                    }, 300);
                }
            });
        }
        if (banner) {
            var bannerHeight = banner.offsetHeight;
            document.body.style.paddingTop = bannerHeight + 'px';
        }
    });
    </script>
    <?php
}

function pbp_get_countdown_position_css($position) {
    $positions = [
        'top-left' => 'top:20px;left:20px;transform:none;',
        'top-center' => 'top:20px;left:50%;transform:translateX(-50%);',
        'top-right' => 'top:20px;right:20px;transform:none;',
        'middle-left' => 'top:50%;left:20px;transform:translateY(-50%);',
        'middle-center' => 'top:50%;left:50%;transform:translate(-50%,-50%);',
        'middle-right' => 'top:50%;right:20px;transform:translateY(-50%);',
        'bottom-left' => 'bottom:20px;left:20px;transform:none;',
        'bottom-center' => 'bottom:20px;left:50%;transform:translateX(-50%);',
        'bottom-right' => 'bottom:20px;right:20px;transform:none;',
    ];
    return $positions[$position] ?? 'bottom:20px;left:50%;transform:translateX(-50%);';
}

function pbp_display_popup() {
    if (!pbp_should_show_popup()) return;
    
    $options = get_option('pbp_settings');
    $content_type = $options['popup_content_type'] ?? 'image';
    $delay = intval($options['popup_delay'] ?? 5) * 1000;
    $redirect_type = $options['popup_redirect_type'] ?? 'none';
    $countdown_enabled = ($options['countdown_enabled'] ?? '0') == '1';
    $target_date = $options['countdown_target_date'] ?? '';
    $countdown_on_image = ($options['countdown_on_image'] ?? '1') == '1';
    $countdown_position = $options['countdown_position'] ?? 'bottom-center';
    $countdown_style = $options['countdown_style'] ?? 'default';
    
    $redirect_url = '#';
    if ($redirect_type === 'url' && !empty($options['popup_redirect_url'])) {
        $redirect_url = esc_url($options['popup_redirect_url']);
    } elseif ($redirect_type === 'page' && !empty($options['popup_redirect_page'])) {
        $redirect_url = get_permalink(intval($options['popup_redirect_page']));
    }
    
    $image_id = intval($options['popup_image'] ?? 0);
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    $html_content = $options['popup_html_content'] ?? '';
    
    // Procesează shortcodurile din conținutul HTML
    if (!empty($html_content)) {
        $html_content = do_shortcode($html_content);
    }
    
    if ($content_type === 'image' && !$image_url) return;
    if ($content_type === 'html' && empty($html_content)) return;
    
    $bg_color = esc_attr($options['popup_bg_color'] ?? 'rgba(0,0,0,0.8)');
    $close_color = esc_attr($options['popup_close_color'] ?? '#ffffff');
    $content_bg = esc_attr($options['popup_content_bg'] ?? '#ffffff');
    
    $countdown_css = '';
    if ($countdown_enabled) {
        switch ($countdown_style) {
            case 'dark':
                $countdown_css = 'background:#1a1a1a;color:#fff;box-shadow:0 4px 15px rgba(0,0,0,0.3);border-radius:12px;';
                break;
            case 'light':
                $countdown_css = 'background:#fff;color:#333;box-shadow:0 4px 15px rgba(0,0,0,0.1);border:1px solid #ddd;border-radius:12px;';
                break;
            case 'custom':
                $countdown_css = '';
                break;
            default:
                $countdown_css = 'background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);color:#fff;box-shadow:0 4px 15px rgba(0,0,0,0.2);border-radius:12px;';
        }
    }
    
    $image_style = 'max-width:100%;height:auto;display:block;margin:0 auto;';
    $content_style = 'position:relative;margin:auto;text-align:center;background:' . $content_bg . ';border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.3);width:fit-content;overflow:auto;';
    ?>
    <div id="pbp-popup" style="display:none;background:<?php echo $bg_color; ?>;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;opacity:0;transition:opacity 0.3s;">
        <div id="pbp-popup-content" style="<?php echo $content_style; ?> top:50%;transform:translateY(-50%);">
            <button id="pbp-popup-close" style="position:fixed;top:20px;right:20px;width:40px;height:40px;border-radius:50%;border:none;background:<?php echo $close_color; ?>;font-size:24px;cursor:pointer;z-index:10001;line-height:1;color:<?php echo $close_color === '#ffffff' ? '#333' : '#fff'; ?>;box-shadow:0 2px 10px rgba(0,0,0,0.3);">×</button>
            
            <?php if ($content_type === 'image'): ?>
                <?php if ($redirect_type !== 'none' && $redirect_url !== '#'): ?>
                    <a href="<?php echo $redirect_url; ?>" target="_blank" style="display:block;position:relative;">
                        <img src="<?php echo esc_url($image_url); ?>" alt="Popup" style="<?php echo $image_style; ?>">
                        <?php if ($countdown_enabled && $countdown_on_image && !empty($target_date)): ?>
                            <div class="pbp-countdown-overlay" style="position:absolute;<?php echo pbp_get_countdown_position_css($countdown_position); ?>;z-index:10;">
                                <div class="pbp-countdown-timer" style="display:inline-block;font-size:1.5rem;font-weight:bold;font-family:'Courier New',monospace;padding:12px 24px;letter-spacing:3px;<?php echo $countdown_css; ?>">00 : 00 : 00 : 00</div>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <div style="position:relative;">
                        <img src="<?php echo esc_url($image_url); ?>" alt="Popup" style="<?php echo $image_style; ?>">
                        <?php if ($countdown_enabled && $countdown_on_image && !empty($target_date)): ?>
                            <div class="pbp-countdown-overlay" style="position:absolute;<?php echo pbp_get_countdown_position_css($countdown_position); ?>;z-index:10;">
                                <div class="pbp-countdown-timer" style="display:inline-block;font-size:1.5rem;font-weight:bold;font-family:'Courier New',monospace;padding:12px 24px;letter-spacing:3px;<?php echo $countdown_css; ?>">00 : 00 : 00 : 00</div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($content_type === 'html'): ?>
                <?php 
                $final_html = $html_content;
                
                // Procesează shortcodurile din nou (pentru siguranță)
                $final_html = do_shortcode($final_html);
                
                if ($countdown_enabled && !empty($target_date) && strpos($final_html, 'pbp-countdown') !== false) {
                    $final_html = str_replace(
                        '<div id="pbp-countdown"></div>',
                        '<div id="pbp-countdown"><div class="pbp-countdown-timer" style="display:inline-block;font-size:2rem;font-weight:bold;font-family:\'Courier New\',monospace;padding:15px 25px;border-radius:12px;margin:15px auto;' . $countdown_css . '">00 : 00 : 00 : 00</div></div>',
                        $final_html
                    );
                }
                
                if ($redirect_type !== 'none' && $redirect_url !== '#') {
                    echo '<a href="' . $redirect_url . '" target="_blank" style="display:block;text-decoration:none;color:inherit;">' . $final_html . '</a>';
                } else {
                    echo $final_html;
                }
                ?>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    .pbp-countdown-timer {
        font-family: 'Courier New', monospace;
        font-weight: bold;
        text-align: center;
    }
    @media (max-width: 768px) {
        .pbp-countdown-timer {
            font-size: 0.9rem !important;
            padding: 6px 12px !important;
            letter-spacing: 2px;
        }
        #pbp-popup-close {
            width: 35px !important;
            height: 35px !important;
            font-size: 20px !important;
            top: 10px !important;
            right: 10px !important;
        }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var popup = document.getElementById('pbp-popup');
        var closeBtn = document.getElementById('pbp-popup-close');
        
        if (!popup) return;
        
        function disableScroll() {
            document.body.style.overflow = 'hidden';
            document.body.style.height = '100%';
        }
        
        function enableScroll() {
            document.body.style.overflow = '';
            document.body.style.height = '';
        }
        
        setTimeout(function() {
            if (popup) {
                popup.style.display = 'block';
                setTimeout(function() { 
                    popup.style.opacity = '1';
                    disableScroll();
                    
                    <?php if ($countdown_enabled && !empty($target_date)): ?>
                    initCountdown();
                    <?php endif; ?>
                }, 10);
            }
        }, <?php echo $delay; ?>);
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                popup.style.opacity = '0';
                setTimeout(function() {
                    popup.style.display = 'none';
                    document.cookie = "pbp_popup_dismissed=true; path=/; max-age=86400";
                    enableScroll();
                }, 300);
            });
        }
        
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                popup.style.opacity = '0';
                setTimeout(function() {
                    popup.style.display = 'none';
                    document.cookie = "pbp_popup_dismissed=true; path=/; max-age=86400";
                    enableScroll();
                }, 300);
            }
        });
        
        var popupContent = document.getElementById('pbp-popup-content');
        if (popupContent) {
            popupContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        <?php if ($countdown_enabled && !empty($target_date)): ?>
        function initCountdown() {
            var countdownDate = new Date("<?php echo esc_js($target_date); ?>").getTime();
            var timers = document.querySelectorAll('.pbp-countdown-timer');
            if (timers.length === 0) return;
            
            var interval = setInterval(function() {
                var now = new Date().getTime();
                var distance = countdownDate - now;
                
                if (distance < 0) {
                    clearInterval(interval);
                    timers.forEach(function(timer) {
                        timer.innerHTML = "00 : 00 : 00 : 00";
                    });
                    return;
                }
                
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (86400000)) / (3600000));
                var minutes = Math.floor((distance % 3600000) / 60000);
                var seconds = Math.floor((distance % 60000) / 1000);
                
                var display = (days < 10 ? "0" + days : days) + " : " +
                              (hours < 10 ? "0" + hours : hours) + " : " +
                              (minutes < 10 ? "0" + minutes : minutes) + " : " +
                              (seconds < 10 ? "0" + seconds : seconds);
                
                timers.forEach(function(timer) {
                    timer.innerHTML = display;
                });
            }, 1000);
        }
        <?php endif; ?>
    });
    </script>
    <?php
}
