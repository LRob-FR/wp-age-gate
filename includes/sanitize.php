<?php
/**
 * Sanitization functions
 *
 * @deprecated Functions moved to LRob_AgeGate_Admin class for better performance
 * This file is kept for backwards compatibility but is no longer loaded by the plugin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lrob_agegate_sanitize_options( $input ) {
    $sanitized = array();

    $sanitized['enabled'] = ! empty( $input['enabled'] ) ? 1 : 0;

    // Store message template and decline country (for UI state)
    $sanitized['message_template'] = sanitize_text_field( $input['message_template'] ?? '' );
    $sanitized['decline_country'] = sanitize_text_field( $input['decline_country'] ?? '' );

    // Always save field values (user can edit preset values)
    $sanitized['min_age'] = max( 1, intval( $input['min_age'] ?? 18 ) );
    $sanitized['title'] = sanitize_text_field( $input['title'] ?? '' );
    $sanitized['message'] = wp_kses_post( $input['message'] ?? '' );
    $sanitized['legal'] = wp_kses_post( $input['legal'] ?? '' );
    $sanitized['decline_url'] = esc_url_raw( $input['decline_url'] ?? 'about:blank' );
    $sanitized['accept_label'] = sanitize_text_field( $input['accept_label'] ?? '' );
    $sanitized['decline_label'] = sanitize_text_field( $input['decline_label'] ?? '' );
    $sanitized['cookie_days'] = max( 1, intval( $input['cookie_days'] ?? 30 ) );

    $sanitized['theme'] = sanitize_text_field( $input['theme'] ?? 'auto' );
    $sanitized['backdrop_blur'] = max( 0, intval( $input['backdrop_blur'] ?? 8 ) );
    $sanitized['modal_bg'] = sanitize_hex_color( $input['modal_bg'] ?? '#ffffff' );
    $sanitized['modal_text'] = sanitize_hex_color( $input['modal_text'] ?? '#111111' );
    $sanitized['modal_border_radius'] = max( 0, intval( $input['modal_border_radius'] ?? 12 ) );
    $sanitized['btn_accept_bg'] = sanitize_hex_color( $input['btn_accept_bg'] ?? '#111111' );
    $sanitized['btn_accept_text'] = sanitize_hex_color( $input['btn_accept_text'] ?? '#ffffff' );
    $sanitized['btn_decline_bg'] = sanitize_hex_color( $input['btn_decline_bg'] ?? '#f7f7f7' );
    $sanitized['btn_decline_text'] = sanitize_hex_color( $input['btn_decline_text'] ?? '#111111' );

    return $sanitized;
}

function lrob_agegate_invalidate_cookies() {
    // Cookie cleared client-side via JS, this is just for confirmation
    return true;
}
