<?php
/**
 * Plugin Name: LRob - Age Gate
 * Plugin URI: https://www.lrob.fr/
 * Description: Age verification gate with configurable presets for alcohol, adult content, and age-restricted products.
 * Version: 1.0.0
 * Author: LRob
 * Author URI: https://www.lrob.fr/
 * Text Domain: lrob-age-gate
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.8
 * Requires PHP: 8.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'LROB_AGEGATE_VERSION', '1.0.0' );
define( 'LROB_AGEGATE_FILE', __FILE__ );
define( 'LROB_AGEGATE_PATH', plugin_dir_path( __FILE__ ) );
define( 'LROB_AGEGATE_URL', plugin_dir_url( __FILE__ ) );

// Load translations
add_action( 'plugins_loaded', 'lrob_agegate_load_textdomain' );

function lrob_agegate_load_textdomain() {
    load_plugin_textdomain( 'lrob-age-gate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Load appropriate class based on context
if ( is_admin() ) {
    require_once LROB_AGEGATE_PATH . 'includes/class-admin.php';
    new LRob_AgeGate_Admin();
} else {
    require_once LROB_AGEGATE_PATH . 'includes/class-frontend.php';
    new LRob_AgeGate_Frontend();
}

register_activation_hook( __FILE__, 'lrob_agegate_activate' );

function lrob_agegate_activate() {
    $defaults = array(
        'enabled' => 1,
        'message_template' => '',
        'decline_country' => '',
        'min_age' => 18,
        'title' => __( 'Age Verification Required', 'lrob-age-gate' ),
        'message' => __( 'Please select a preset or configure your age verification message.', 'lrob-age-gate' ),
        'legal' => __( 'Configure your legal notice.', 'lrob-age-gate' ),
        'accept_label' => __( 'I confirm I am of legal age', 'lrob-age-gate' ),
        'decline_label' => __( 'I am not of legal age', 'lrob-age-gate' ),
        'decline_url' => 'about:blank',
        'cookie_days' => 30,
        'theme' => 'auto',
        'backdrop_blur' => 8,
        'modal_bg' => '#ffffff',
        'modal_text' => '#111111',
        'modal_border_radius' => 12,
        'btn_accept_bg' => '#111111',
        'btn_accept_text' => '#ffffff',
        'btn_decline_bg' => '#f7f7f7',
        'btn_decline_text' => '#111111',
        'token' => time() // Invalidation token
    );

    // Merge existing options with defaults (adds new fields on upgrade)
    $existing = get_option( 'lrob_age_gate_options', array() );
    $merged = wp_parse_args( $existing, $defaults );
    update_option( 'lrob_age_gate_options', $merged );
}
