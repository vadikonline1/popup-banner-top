<?php
if (!defined('ABSPATH')) exit;

add_action('wp_footer', 'pbp_display_popup');
add_action('wp_body_open', 'pbp_display_banner');

function pbp_display_banner() {
    $options = get_option('pbp_settings');
    if (($options['banner_enabled'] ?? '0') != '1') return;
    
    // Verifică cookie
    if (isset($_COOKIE['pbp_banner_dismissed'])) return;
    
    $text = $options['banner_text'] ?? '';
    if (empty($text)) return;
    
    $url_type = $options['banner_url_type'] ?? 'none';
    $url_text = $options['banner_url_text'] ?? 'Află mai multe';
    
    // Determină URL
    $url = '#';
    if ($url_type === 'url' && !empty($options['banner_custom_url'])) {
        $url = esc_url($options['banner_custom_url']);
    } elseif ($url_type === 'page' && !empty($options['banner_page_url'])) {
        $url = get_permalink(intval($options['banner_page_url']));
    }
    
    // Stiluri inline
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
                        // Elimină padding-ul de la body
                        document.body.style.paddingTop = '0';
                    }, 300);
                }
            });
            
            // Touch event pentru mobil
            closeBtn.addEventListener('touchstart', function(e) {
                e.preventDefault();
                closeBtn.click();
            }, {passive: false});
        }
        
        // Ajustează padding-ul body pentru banner
        if (banner) {
            var bannerHeight = banner.offsetHeight;
            document.body.style.paddingTop = bannerHeight + 'px';
        }
    });
    </script>
    <?php
}

function pbp_display_popup() {
    $options = get_option('pbp_settings');
    if (($options['popup_enabled'] ?? '0') != '1') return;
    
    // Verifică cookie
    if (isset($_COOKIE['pbp_popup_dismissed'])) return;
    
    $image_id = intval($options['popup_image'] ?? 0);
    if (!$image_id) return;
    
    $image_url = wp_get_attachment_url($image_id);
    if (!$image_url) return;
    
    $delay = intval($options['popup_delay'] ?? 5) * 1000;
    $redirect_type = $options['popup_redirect_type'] ?? 'none';
    
    // Determină URL redirect
    $redirect_url = '#';
    if ($redirect_type === 'url' && !empty($options['popup_redirect_url'])) {
        $redirect_url = esc_url($options['popup_redirect_url']);
    } elseif ($redirect_type === 'page' && !empty($options['popup_redirect_page'])) {
        $redirect_url = get_permalink(intval($options['popup_redirect_page']));
    }
    
    // Stiluri
    $bg_color = esc_attr($options['popup_bg_color'] ?? 'rgba(0,0,0,0.8)');
    $close_color = esc_attr($options['popup_close_color'] ?? '#ffffff');
    $close_bg = esc_attr($options['popup_close_bg'] ?? '#000000');
    ?>
    <div id="pbp-popup" style="display:none;background:<?php echo $bg_color; ?>;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;opacity:0;transition:opacity 0.3s;">
        <div id="pbp-popup-content" style="position:relative;width:fit-content;max-width:800px;margin:auto;text-align:center;top:50%;transform:translateY(-50%);">
            <button id="pbp-popup-close" style="position:absolute;top:-15px;right:-15px;width:30px;height:30px;border-radius:50%;border:none;color:<?php echo $close_color; ?>;background:<?php echo $close_bg; ?>;font-size:20px;cursor:pointer;z-index:10;display:flex;align-items:center;justify-content:center;line-height:1;">×</button>
            <?php if ($redirect_type !== 'none'): ?>
                <a href="<?php echo $redirect_url; ?>" target="_blank" style="display:block;">
                    <img src="<?php echo esc_url($image_url); ?>" alt="Popup" style="max-width:100%;max-height:50vh;height:auto;width:auto;display:block;margin:0 auto;">
                </a>
            <?php else: ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="Popup" style="max-width:100%;max-height:50vh;height:auto;width:auto;display:block;margin:0 auto;">
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var popup = document.getElementById('pbp-popup');
        var closeBtn = document.getElementById('pbp-popup-close');
        
        if (!popup) return;
        
        // Blochează scroll când popup este afișat
        function disableScroll() {
            document.body.style.overflow = 'hidden';
            document.body.style.height = '100%';
        }
        
        function enableScroll() {
            document.body.style.overflow = '';
            document.body.style.height = '';
        }
        
        // Afișează după delay
        setTimeout(function() {
            popup.style.display = 'block';
            setTimeout(function() { 
                popup.style.opacity = '1';
                disableScroll(); // Blochează scroll
            }, 10);
        }, <?php echo $delay; ?>);
        
        // Închide popup
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                popup.style.opacity = '0';
                setTimeout(function() {
                    popup.style.display = 'none';
                    document.cookie = "pbp_popup_dismissed=true; path=/; max-age=86400";
                    enableScroll(); // Activează scroll
                }, 300);
            });
            
            // Touch event pentru mobil
            closeBtn.addEventListener('touchstart', function(e) {
                e.preventDefault();
                closeBtn.click();
            }, {passive: false});
        }
        
        // Închide la click pe overlay
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                popup.style.opacity = '0';
                setTimeout(function() {
                    popup.style.display = 'none';
                    document.cookie = "pbp_popup_dismissed=true; path=/; max-age=86400";
                    enableScroll(); // Activează scroll
                }, 300);
            }
        });
        
        // Touch event pentru overlay
        popup.addEventListener('touchstart', function(e) {
            if (e.target === popup) {
                e.preventDefault();
                popup.style.opacity = '0';
                setTimeout(function() {
                    popup.style.display = 'none';
                    document.cookie = "pbp_popup_dismissed=true; path=/; max-age=86400";
                    enableScroll();
                }, 300);
            }
        }, {passive: false});
        
        // Previne închiderea la click pe conținut
        var popupContent = document.getElementById('pbp-popup-content');
        if (popupContent) {
            popupContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    });
    </script>
    <?php
}