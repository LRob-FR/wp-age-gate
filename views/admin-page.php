<?php
/**
 * Admin settings page template
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once LROB_AGEGATE_PATH . 'includes/presets.php';
$opts = get_option( 'lrob_age_gate_options', array() );
$active_tab = $_GET['tab'] ?? 'general';

// Extract theme colors for auto preview
$theme_colors = array();
if ( function_exists( 'wp_get_global_settings' ) ) {
    $global_settings = wp_get_global_settings();
    if ( isset( $global_settings['color']['palette']['theme'] ) ) {
        foreach ( $global_settings['color']['palette']['theme'] as $color ) {
            $theme_colors[$color['slug']] = $color['color'];
        }
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if ( isset( $_GET['invalidated'] ) ): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'All age verification cookies have been invalidated. Users will be prompted again.', 'lrob-age-gate' ); ?></p>
        </div>
    <?php endif; ?>

    <div class="lrob-admin-container">
        <nav class="nav-tab-wrapper">
            <a href="?page=lrob-age-gate&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'General', 'lrob-age-gate' ); ?>
            </a>
            <a href="?page=lrob-age-gate&tab=content" class="nav-tab <?php echo $active_tab === 'content' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Content', 'lrob-age-gate' ); ?>
            </a>
            <a href="?page=lrob-age-gate&tab=appearance" class="nav-tab <?php echo $active_tab === 'appearance' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Appearance', 'lrob-age-gate' ); ?>
            </a>
            <a href="?page=lrob-age-gate&tab=tools" class="nav-tab <?php echo $active_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Tools', 'lrob-age-gate' ); ?>
            </a>
        </nav>

        <div class="lrob-admin-content">
            <div class="lrob-admin-main">
                <form method="post" action="options.php" id="lrob-settings-form">
                    <?php settings_fields( 'lrob-age-gate' ); ?>

                    <div class="lrob-tab-content" data-tab="general" style="<?php echo $active_tab !== 'general' ? 'display:none;' : ''; ?>">
                        <div class="lrob-card">
                            <h2><?php esc_html_e( 'General Settings', 'lrob-age-gate' ); ?></h2>
                            <p class="description"><?php esc_html_e( 'Configure age verification behavior.', 'lrob-age-gate' ); ?></p>
                            <table class="form-table">
                                <?php do_settings_fields( 'lrob-age-gate', 'general' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="lrob-tab-content" data-tab="content" style="<?php echo $active_tab !== 'content' ? 'display:none;' : ''; ?>">
                        <div class="lrob-card">
                            <h2><?php esc_html_e( 'Content & Messages', 'lrob-age-gate' ); ?></h2>
                            <p class="description"><?php esc_html_e( 'Select a preset to quick-load a message template, then customize the fields as needed.', 'lrob-age-gate' ); ?></p>
                            <?php
                            if ( empty( $opts['title'] ) || empty( $opts['message'] ) ) {
                                echo '<div class="lrob-warning"><strong>' . esc_html__( 'Configuration Required:', 'lrob-age-gate' ) . '</strong> ';
                                echo esc_html__( 'Please select a preset or configure all content fields.', 'lrob-age-gate' ) . '</div>';
                            }
                            ?>
                            <table class="form-table">
                                <?php do_settings_fields( 'lrob-age-gate', 'content' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="lrob-tab-content" data-tab="appearance" style="<?php echo $active_tab !== 'appearance' ? 'display:none;' : ''; ?>">
                        <div class="lrob-card">
                            <h2><?php esc_html_e( 'Appearance', 'lrob-age-gate' ); ?></h2>
                            <table class="form-table">
                                <?php do_settings_fields( 'lrob-age-gate', 'appearance' ); ?>
                            </table>
                        </div>
                    </div>

                    <div class="lrob-tab-content" data-tab="tools" style="<?php echo $active_tab !== 'tools' ? 'display:none;' : ''; ?>">
                        <div class="lrob-card">
                            <h2><?php esc_html_e( 'Tools', 'lrob-age-gate' ); ?></h2>
                            <table class="form-table">
                                <?php do_settings_fields( 'lrob-age-gate', 'tools' ); ?>
                            </table>
                        </div>
                    </div>

                    <?php submit_button(); ?>
                </form>
            </div>

            <div class="lrob-admin-sidebar">
                <div class="lrob-card">
                    <h3><?php esc_html_e( 'Live Preview', 'lrob-age-gate' ); ?></h3>
                    <p class="description" style="margin-bottom: 10px;">
                        <?php esc_html_e( 'Preview updates as you modify settings.', 'lrob-age-gate' ); ?>
                    </p>
                    <div id="lrob-preview-notice" class="lrob-info" style="display:none; margin-bottom: 10px;">
                        <span id="lrob-preview-notice-text"></span>
                    </div>
                    <div id="lrob-preview-container">
                        <style id="lrob-preview-styles"></style>
                        <div id="lrob-preview-gate" class="lrob-age-gate" style="display:flex !important; position:relative !important; inset:auto !important;">
                            <div class="lrob-age-gate__dialog" role="document" tabindex="-1">
                                <h2 id="lrob-preview-title"></h2>
                                <div id="lrob-preview-message" class="lrob-age-gate__msg"></div>
                                <div class="lrob-age-gate__btns">
                                    <button type="button" class="lrob-age-gate__btn lrob-age-gate__btn--accept" id="lrob-preview-accept"></button>
                                    <button type="button" class="lrob-age-gate__btn lrob-age-gate__btn--decline" id="lrob-preview-decline"></button>
                                </div>
                                <p class="lrob-age-gate__legal" id="lrob-preview-legal"></p>
                            </div>
                        </div>
                    </div>
                    <p class="description" style="margin-top: 10px;">
                        <strong><?php esc_html_e( 'Testing:', 'lrob-age-gate' ); ?></strong>
                        <?php esc_html_e( 'For real-world testing, visit your website in a private/incognito browsing session.', 'lrob-age-gate' ); ?>
                    </p>
                </div>

                <div class="lrob-card" style="text-align: center; padding: 15px;">
                    <span style="color: #646970;">
                        <?php esc_html_e( 'Developed with', 'lrob-age-gate' ); ?>
                        <span style="color: #d63638;">❤️</span>
                        <?php esc_html_e( 'by', 'lrob-age-gate' ); ?>
                        <a href="https://www.lrob.fr/" target="_blank" rel="noopener" style="color: #2271b1; text-decoration: none; font-weight: 500;">
                            <?php esc_html_e( 'LRob, WordPress specialist', 'lrob-age-gate' ); ?>
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invalidate form section -->
<div id="lrob-invalidate-section" style="display:none;">
    <div class="lrob-warning">
        <p><strong><?php esc_html_e( 'Warning:', 'lrob-age-gate' ); ?></strong> <?php esc_html_e( 'This will invalidate all existing user validations. All visitors will see the age gate again.', 'lrob-age-gate' ); ?></p>
    </div>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:10px" onsubmit="document.cookie='lrob_age_verified=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';">
        <?php wp_nonce_field( 'lrob_invalidate_cookies', 'lrob_nonce' ); ?>
        <input type="hidden" name="action" value="lrob_invalidate_cookies">
        <button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure? This will force all users to validate their age again.', 'lrob-age-gate' ); ?>')">
            <?php esc_html_e( 'Invalidate All Cookies', 'lrob-age-gate' ); ?>
        </button>
    </form>
</div>

<style>
.lrob-admin-container { margin-top: 20px; }
.lrob-admin-content { display: flex; gap: 20px; margin-top: 20px; }
.lrob-admin-main { flex: 1; min-width: 0; }
.lrob-admin-sidebar { width: 700px; flex-shrink: 0; }
.lrob-card { background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
.lrob-card h2, .lrob-card h3 { margin-top: 0; }
.lrob-info { background: #f0f6fc; border-left: 4px solid #72aee6; padding: 12px; font-size: 13px; }
.lrob-warning { background: #fcf3cd; border-left: 4px solid #dba617; padding: 12px; margin: 15px 0; }

#lrob-preview-container {
    position: relative;
    width: 100%;
    height: 450px;
    background: rgba(0,0,0,.85);
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
#lrob-preview-gate {
    position: relative !important;
    display: flex !important;
    width: 100% !important;
    height: 100% !important;
    background: transparent !important;
    backdrop-filter: none !important;
}
#lrob-preview-gate .lrob-age-gate__dialog {
    margin: auto;
}

@media (max-width: 1280px) {
    .lrob-admin-content { flex-direction: column; }
    .lrob-admin-sidebar { width: 100%; }
}
</style>

<script>
jQuery(function($) {
    var templates = <?php echo json_encode( lrob_agegate_get_message_templates() ); ?>;
    var themeColors = <?php echo json_encode( $theme_colors ); ?>;
    var $form = $('#lrob-settings-form');

    // Message and country dropdowns
    var $messageSelect = $('#lrob-message-select');
    var $countrySelect = $('#lrob-country-select');
    var $loadBtn = $('#lrob-load-preset');

    function updateLoadButton() {
        var hasMessage = $messageSelect.val() !== '';
        var hasCountry = $countrySelect.val() !== '';
        $loadBtn.prop('disabled', !(hasMessage && hasCountry));
    }

    $messageSelect.on('change', updateLoadButton);
    $countrySelect.on('change', updateLoadButton);

    $loadBtn.on('click', function() {
        var messageCode = $messageSelect.val();
        var countryCode = $countrySelect.val();

        if (!messageCode || !countryCode) return;

        var template = templates[messageCode];
        if (template) {
            $('input[name="lrob_age_gate_options[min_age]"]').val(template.min_age);
            $('input[name="lrob_age_gate_options[title]"]').val(template.title);
            $('textarea[name="lrob_age_gate_options[message]"]').val(template.message);
            $('textarea[name="lrob_age_gate_options[legal]"]').val(template.legal);

            // Fetch decline URL via AJAX or set default
            $.post(ajaxurl, {
                action: 'lrob_get_decline_url',
                country: countryCode,
                message: messageCode,
                nonce: '<?php echo wp_create_nonce( 'lrob_get_decline_url' ); ?>'
            }, function(response) {
                if (response.success && response.data.url) {
                    $('input[name="lrob_age_gate_options[decline_url]"]').val(response.data.url);
                } else {
                    $('input[name="lrob_age_gate_options[decline_url]"]').val('about:blank');
                }
                updatePreview();
            }).fail(function() {
                $('input[name="lrob_age_gate_options[decline_url]"]').val('about:blank');
                updatePreview();
            });

            updatePreview();
        }
    });

    updateLoadButton();

    // Invalidate section placement
    $('#lrob-invalidate-section').appendTo('#lrob-invalidate-placeholder').show();

    // Live preview
    function replaceAge(text, age) {
        return (text || '').replace(/{age}/g, age);
    }

    function getThemeColor(slug, fallback) {
        return themeColors[slug] || fallback;
    }

    function generateStyles(theme, radius, blur, customColors) {
        var baseStyles = '.lrob-age-gate{display:flex;position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.85);backdrop-filter:blur(' + blur + 'px);-webkit-backdrop-filter:blur(' + blur + 'px);align-items:center;justify-content:center}';
        baseStyles += '.lrob-age-gate__dialog{position:relative;max-width:540px;width:90%;border-radius:' + radius + 'px;box-shadow:0 20px 60px rgba(0,0,0,.4);padding:32px;outline:none;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;animation:fadeIn .3s ease-out}';
        baseStyles += '.lrob-age-gate__dialog h2{margin:0 0 16px;font-size:24px;font-weight:600;line-height:1.2}';
        baseStyles += '.lrob-age-gate__msg{font-size:15px;line-height:1.6;margin-bottom:16px}';
        baseStyles += '.lrob-age-gate__legal{font-size:13px;line-height:1.4;opacity:.7;margin-top:16px}';
        baseStyles += '.lrob-age-gate__btns{display:flex;gap:12px;margin-top:24px}';
        baseStyles += '.lrob-age-gate__btn{flex:1;padding:12px 24px;border-radius:' + Math.min(radius, 8) + 'px;border:none;cursor:pointer;font-size:15px;font-weight:600;transition:all .2s ease}';
        baseStyles += '.lrob-age-gate__btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);opacity:.9}';
        baseStyles += '.lrob-age-gate__btn:active{transform:translateY(0)}';
        baseStyles += '@keyframes fadeIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}';

        if (theme === 'custom') {
            baseStyles += '.lrob-age-gate__dialog{background:' + customColors.bg + ';color:' + customColors.text + '}';
            baseStyles += '.lrob-age-gate__dialog h2{color:' + customColors.text + '}';
            baseStyles += '.lrob-age-gate__msg{color:' + customColors.text + '}';
            baseStyles += '.lrob-age-gate__legal{color:' + customColors.text + '}';
            baseStyles += '.lrob-age-gate__btn--accept{background:' + customColors.acceptBg + ';color:' + customColors.acceptText + '}';
            baseStyles += '.lrob-age-gate__btn--decline{background:' + customColors.declineBg + ';color:' + customColors.declineText + '}';
        } else if (theme === 'dark') {
            baseStyles += '.lrob-age-gate__dialog{background:#1a1a1a;color:#f5f5f5}';
            baseStyles += '.lrob-age-gate__dialog h2{color:#f5f5f5}';
            baseStyles += '.lrob-age-gate__msg{color:#f5f5f5}';
            baseStyles += '.lrob-age-gate__legal{color:#f5f5f5}';
            baseStyles += '.lrob-age-gate__btn--accept{background:#ffffff;color:#111111}';
            baseStyles += '.lrob-age-gate__btn--decline{background:#333333;color:#f5f5f5}';
        } else if (theme === 'light') {
            baseStyles += '.lrob-age-gate__dialog{background:#ffffff;color:#111111}';
            baseStyles += '.lrob-age-gate__dialog h2{color:#111111}';
            baseStyles += '.lrob-age-gate__msg{color:#111111}';
            baseStyles += '.lrob-age-gate__legal{color:#111111}';
            baseStyles += '.lrob-age-gate__btn--accept{background:#111111;color:#ffffff}';
            baseStyles += '.lrob-age-gate__btn--decline{background:#f7f7f7;color:#111111}';
        } else { // auto
            var bg = getThemeColor('base', getThemeColor('background', '#ffffff'));
            var text = getThemeColor('contrast', getThemeColor('foreground', '#111111'));
            var acceptBg = getThemeColor('contrast', getThemeColor('foreground', '#111111'));
            var acceptText = getThemeColor('base', getThemeColor('background', '#ffffff'));
            var declineBg = getThemeColor('secondary', 'rgba(128,128,128,.15)');
            var declineText = text;

            baseStyles += '.lrob-age-gate__dialog{background:' + bg + ';color:' + text + '}';
            baseStyles += '.lrob-age-gate__dialog h2{color:' + text + '}';
            baseStyles += '.lrob-age-gate__msg{color:' + text + '}';
            baseStyles += '.lrob-age-gate__legal{color:' + text + '}';
            baseStyles += '.lrob-age-gate__btn--accept{background:' + acceptBg + ';color:' + acceptText + '}';
            baseStyles += '.lrob-age-gate__btn--decline{background:' + declineBg + ';color:' + declineText + '}';
        }

        return baseStyles;
    }

    function updatePreview() {
        var minAge = $('input[name="lrob_age_gate_options[min_age]"]').val() || 18;
        var title = $('input[name="lrob_age_gate_options[title]"]').val();
        var message = $('textarea[name="lrob_age_gate_options[message]"]').val();
        var legal = $('textarea[name="lrob_age_gate_options[legal]"]').val();
        var acceptLabel = $('input[name="lrob_age_gate_options[accept_label]"]').val();
        var declineLabel = $('input[name="lrob_age_gate_options[decline_label]"]').val();
        var theme = $('select[name="lrob_age_gate_options[theme]"]').val();
        var radius = parseInt($('input[name="lrob_age_gate_options[modal_border_radius]"]').val()) || 12;
        var blur = parseInt($('input[name="lrob_age_gate_options[backdrop_blur]"]').val()) || 8;

        $('#lrob-preview-title').text(replaceAge(title, minAge));
        $('#lrob-preview-message').html(replaceAge(message, minAge));
        $('#lrob-preview-legal').html(replaceAge(legal, minAge));
        $('#lrob-preview-accept').text(acceptLabel);
        $('#lrob-preview-decline').text(declineLabel);

        var customColors = {
            bg: $('input[name="lrob_age_gate_options[modal_bg]"]').val() || '#ffffff',
            text: $('input[name="lrob_age_gate_options[modal_text]"]').val() || '#111111',
            acceptBg: $('input[name="lrob_age_gate_options[btn_accept_bg]"]').val() || '#111111',
            acceptText: $('input[name="lrob_age_gate_options[btn_accept_text]"]').val() || '#ffffff',
            declineBg: $('input[name="lrob_age_gate_options[btn_decline_bg]"]').val() || '#f7f7f7',
            declineText: $('input[name="lrob_age_gate_options[btn_decline_text]"]').val() || '#111111'
        };

        var styles = generateStyles(theme, radius, blur, customColors);
        $('#lrob-preview-styles').html(styles);

        if (theme === 'auto') {
            var hasThemeColors = Object.keys(themeColors).length > 0;
            if (hasThemeColors) {
                $('#lrob-preview-notice-text').html('<?php esc_html_e( '"Auto" theme using detected theme colors. Preview may vary from frontend.', 'lrob-age-gate' ); ?>');
            } else {
                $('#lrob-preview-notice-text').html('<?php esc_html_e( '"Auto" theme shown with fallback colors. Theme colors not detected.', 'lrob-age-gate' ); ?>');
            }
            $('#lrob-preview-notice').show();
        } else {
            $('#lrob-preview-notice').hide();
        }
    }

    $form.on('change input', 'input, textarea, select', updatePreview);
    updatePreview();
});
</script>
