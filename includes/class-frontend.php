<?php
/**
 * Frontend functionality
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LRob_AgeGate_Frontend {

    private $option_key = 'lrob_age_gate_options';

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
        add_action( 'wp_footer', array( $this, 'maybe_render' ) );
        add_action( 'wp_head', array( $this, 'maybe_inject_styles' ), 999 );
    }

    private function should_show() {
        $opts = get_option( $this->option_key, array() );

        if ( empty( $opts['enabled'] ) ) return false;
        if ( $this->is_bot() ) return false;
        if ( isset( $_COOKIE['lrob_age_verified'] ) && $_COOKIE['lrob_age_verified'] === '1' ) return false;

        return true;
    }

    private function is_bot() {
        if ( is_admin() ) return false;

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        if ( ! $ua ) return false;

        $ua = strtolower( $ua );
        $bots = array( 'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
                      'facebookexternalhit', 'twitterbot', 'linkedinbot', 'ia_archiver' );

        foreach ( $bots as $bot ) {
            if ( strpos( $ua, $bot ) !== false ) return true;
        }

        return preg_match( '/(bot|crawler|spider)/i', $ua );
    }

    public function maybe_enqueue() {
        if ( ! $this->should_show() ) return;

        $opts = get_option( $this->option_key, array() );

        wp_enqueue_script( 'lrob-age-gate', LROB_AGEGATE_URL . 'assets/age-gate.js', array(), LROB_AGEGATE_VERSION, true );

        // wp_localize_script handles escaping automatically
        wp_localize_script( 'lrob-age-gate', 'LROB_AGEGATE_OPTS', array(
            'title' => $this->replace_age( $opts['title'] ?? '', $opts['min_age'] ?? 18 ),
            'message' => $this->replace_age( $opts['message'] ?? '', $opts['min_age'] ?? 18 ),
            'legal' => $this->replace_age( $opts['legal'] ?? '', $opts['min_age'] ?? 18 ),
            'accept' => ! empty( $opts['accept_label'] ) ? $opts['accept_label'] : __( 'I confirm I am of legal age', 'lrob-age-gate' ),
            'decline' => ! empty( $opts['decline_label'] ) ? $opts['decline_label'] : __( 'I am not of legal age', 'lrob-age-gate' ),
            'declineUrl' => esc_url( $opts['decline_url'] ?? 'about:blank' ),
            'cookieDays' => intval( $opts['cookie_days'] ?? 30 )
        ) );
    }

    public function maybe_inject_styles() {
        if ( ! $this->should_show() ) return;

        $opts = get_option( $this->option_key, array() );
        $theme = $opts['theme'] ?? 'auto';
        $radius = absint( $opts['modal_border_radius'] ?? 12 );
        $blur = absint( $opts['backdrop_blur'] ?? 8 );
        $btn_radius = min( $radius, 8 );

        if ( $theme === 'custom' ) {
            $colors = $this->get_colors( $opts );
            ?>
<style id="lrob-ag-s">
.lrob-age-gate{display:none;position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.85);backdrop-filter:blur(<?php echo esc_attr( $blur ); ?>px);-webkit-backdrop-filter:blur(<?php echo esc_attr( $blur ); ?>px);align-items:center;justify-content:center}
.lrob-age-gate__dialog{position:relative;max-width:540px;width:90%;background:<?php echo esc_attr( $colors['bg'] ); ?>;color:<?php echo esc_attr( $colors['text'] ); ?>;border-radius:<?php echo esc_attr( $radius ); ?>px;box-shadow:0 20px 60px rgba(0,0,0,.4);padding:32px;outline:none;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;animation:fadeIn .3s ease-out}
.lrob-age-gate__dialog h2{margin:0 0 16px;font-size:24px;font-weight:600;line-height:1.2}
.lrob-age-gate__msg{font-size:15px;line-height:1.6;margin-bottom:16px}
.lrob-age-gate__legal{font-size:13px;line-height:1.4;opacity:.7;margin-top:16px}
.lrob-age-gate__btns{display:flex;gap:12px;margin-top:24px}
.lrob-age-gate__btn{flex:1;padding:12px 24px;border-radius:<?php echo esc_attr( $btn_radius ); ?>px;border:none;cursor:pointer;font-size:15px;font-weight:600;transition:all .2s ease}
.lrob-age-gate__btn--accept{background:<?php echo esc_attr( $colors['accept_bg'] ); ?>;color:<?php echo esc_attr( $colors['accept_text'] ); ?>}
.lrob-age-gate__btn--decline{background:<?php echo esc_attr( $colors['decline_bg'] ); ?>;color:<?php echo esc_attr( $colors['decline_text'] ); ?>}
.lrob-age-gate__btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);opacity:.9}
.lrob-age-gate__btn:active{transform:translateY(0)}
@keyframes fadeIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}
html.lrob-age-gate-open{overflow:hidden}
</style>
            <?php
        } else {
            // Auto, Light, or Dark theme
            ?>
<style id="lrob-ag-s">
.lrob-age-gate{display:none;position:fixed;inset:0;z-index:999999;background:rgba(0,0,0,.85);backdrop-filter:blur(<?php echo esc_attr( $blur ); ?>px);-webkit-backdrop-filter:blur(<?php echo esc_attr( $blur ); ?>px);align-items:center;justify-content:center}
.lrob-age-gate__dialog{position:relative;max-width:540px;width:90%;border-radius:<?php echo esc_attr( $radius ); ?>px;box-shadow:0 20px 60px rgba(0,0,0,.4);padding:32px;outline:none;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;animation:fadeIn .3s ease-out}
.lrob-age-gate__dialog h2{margin:0 0 16px;font-size:24px;font-weight:600;line-height:1.2}
.lrob-age-gate__msg{font-size:15px;line-height:1.6;margin-bottom:16px}
.lrob-age-gate__legal{font-size:13px;line-height:1.4;opacity:.7;margin-top:16px}
.lrob-age-gate__btns{display:flex;gap:12px;margin-top:24px}
.lrob-age-gate__btn{flex:1;padding:12px 24px;border-radius:<?php echo esc_attr( $btn_radius ); ?>px;border:none;cursor:pointer;font-size:15px;font-weight:600;transition:all .2s ease}
.lrob-age-gate__btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);opacity:.9}
.lrob-age-gate__btn:active{transform:translateY(0)}
@keyframes fadeIn{from{transform:scale(.9);opacity:0}to{transform:scale(1);opacity:1}}
html.lrob-age-gate-open{overflow:hidden}
<?php if ( $theme === 'auto' ): ?>
/* Auto: Follow WordPress theme colors with fallbacks */
.lrob-age-gate__dialog{
    background:var(--wp--preset--color--base, var(--wp--preset--color--background, #ffffff));
    color:var(--wp--preset--color--contrast, var(--wp--preset--color--foreground, #111111));
}
.lrob-age-gate__btn--accept{
    background:var(--wp--preset--color--contrast, var(--wp--preset--color--foreground, #111111));
    color:var(--wp--preset--color--base, var(--wp--preset--color--background, #ffffff));
}
.lrob-age-gate__btn--decline{
    background:var(--wp--preset--color--secondary, rgba(128,128,128,.15));
    color:var(--wp--preset--color--contrast, var(--wp--preset--color--foreground, #111111));
}
/* Fallback for themes without proper color definition */
@supports not (color: var(--wp--preset--color--base)) {
    .lrob-age-gate__dialog{background:#ffffff;color:#111111}
    .lrob-age-gate__btn--accept{background:#111111;color:#ffffff}
    .lrob-age-gate__btn--decline{background:#f7f7f7;color:#111111}
}
/* Additional system dark mode support */
@media (prefers-color-scheme: dark) {
    @supports not (color: var(--wp--preset--color--base)) {
        .lrob-age-gate__dialog{background:#1a1a1a;color:#f5f5f5}
        .lrob-age-gate__btn--accept{background:#ffffff;color:#111111}
        .lrob-age-gate__btn--decline{background:#333333;color:#f5f5f5}
    }
}
<?php elseif ( $theme === 'dark' ): ?>
/* Dark: Hardcoded dark colors */
.lrob-age-gate__dialog{background:#1a1a1a;color:#f5f5f5}
.lrob-age-gate__dialog h2{color:#f5f5f5}
.lrob-age-gate__msg{color:#f5f5f5}
.lrob-age-gate__legal{color:#f5f5f5}
.lrob-age-gate__btn--accept{background:#ffffff;color:#111111}
.lrob-age-gate__btn--decline{background:#333333;color:#f5f5f5}
<?php else: /* light */ ?>
/* Light: Hardcoded light colors */
.lrob-age-gate__dialog{background:#ffffff;color:#111111}
.lrob-age-gate__dialog h2{color:#111111}
.lrob-age-gate__msg{color:#111111}
.lrob-age-gate__legal{color:#111111}
.lrob-age-gate__btn--accept{background:#111111;color:#ffffff}
.lrob-age-gate__btn--decline{background:#f7f7f7;color:#111111}
<?php endif; ?>
</style>
            <?php
        }
    }

    public function maybe_render() {
        if ( ! $this->should_show() ) return;
        ?>
<div id="lrob-age-gate" class="lrob-age-gate" role="dialog" aria-modal="true">
    <div class="lrob-age-gate__dialog" role="document" tabindex="-1">
        <h2 id="lrob-age-title"></h2>
        <div id="lrob-age-desc" class="lrob-age-gate__msg"></div>
        <div class="lrob-age-gate__btns">
            <button type="button" class="lrob-age-gate__btn lrob-age-gate__btn--accept"></button>
            <button type="button" class="lrob-age-gate__btn lrob-age-gate__btn--decline"></button>
        </div>
        <p class="lrob-age-gate__legal"></p>
    </div>
</div>
        <?php
    }

    private function get_colors( $opts ) {
        $theme = $opts['theme'] ?? 'auto';

        if ( $theme === 'custom' ) {
            return array(
                'bg' => sanitize_hex_color( $opts['modal_bg'] ?? '#ffffff' ),
                'text' => sanitize_hex_color( $opts['modal_text'] ?? '#111111' ),
                'accept_bg' => sanitize_hex_color( $opts['btn_accept_bg'] ?? '#111111' ),
                'accept_text' => sanitize_hex_color( $opts['btn_accept_text'] ?? '#ffffff' ),
                'decline_bg' => sanitize_hex_color( $opts['btn_decline_bg'] ?? '#f7f7f7' ),
                'decline_text' => sanitize_hex_color( $opts['btn_decline_text'] ?? '#111111' )
            );
        }

        if ( $theme === 'dark' ) {
            return array(
                'bg' => '#1a1a1a',
                'text' => '#f5f5f5',
                'accept_bg' => '#ffffff',
                'accept_text' => '#111111',
                'decline_bg' => '#333333',
                'decline_text' => '#f5f5f5'
            );
        }

        // Light theme (default)
        return array(
            'bg' => '#ffffff',
            'text' => '#111111',
            'accept_bg' => '#111111',
            'accept_text' => '#ffffff',
            'decline_bg' => '#f7f7f7',
            'decline_text' => '#111111'
        );
    }

    private function replace_age( $text, $age ) {
        return str_replace( '{age}', absint( $age ), $text );
    }
}
