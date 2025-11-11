<?php
/**
 * Age gate presets - loaded from JSON files with caching
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function lrob_agegate_get_message_templates() {
    $templates = array();
    $messages_dir = LROB_AGEGATE_PATH . 'messages/';

    if ( ! is_dir( $messages_dir ) ) {
        return $templates;
    }

    $files = glob( $messages_dir . '*.json' );
    if ( false === $files ) {
        return $templates;
    }

    foreach ( $files as $file ) {
        $basename = basename( $file, '.json' );

        // Skip decline_urls.json
        if ( $basename === 'decline_urls' ) {
            continue;
        }

        $json = @file_get_contents( $file );
        if ( false === $json ) {
            continue;
        }

        $data = json_decode( $json, true );

        if ( ! is_array( $data ) || ! isset( $data['message_code'] ) ) {
            continue;
        }

        $code = sanitize_key( $data['message_code'] );

        $templates[ $code ] = array(
            'message_code' => $code,
            'message_label' => sanitize_text_field( __( $data['message_label'] ?? '', 'lrob-age-gate' ) ),
            'min_age' => absint( $data['min_age'] ?? 18 ),
            'title' => sanitize_text_field( __( $data['title'] ?? '', 'lrob-age-gate' ) ),
            'message' => wp_kses_post( __( $data['message'] ?? '', 'lrob-age-gate' ) ),
            'legal' => wp_kses_post( __( $data['legal'] ?? '', 'lrob-age-gate' ) )
        );
    }

    return $templates;
}

function lrob_agegate_get_decline_urls() {
    $urls = array();
    $decline_file = LROB_AGEGATE_PATH . 'messages/decline_urls.json';

    if ( ! file_exists( $decline_file ) ) {
        return $urls;
    }

    $json = @file_get_contents( $decline_file );
    if ( false === $json ) {
        return $urls;
    }

    $data = json_decode( $json, true );

    if ( ! is_array( $data ) ) {
        return $urls;
    }

    foreach ( $data as $entry ) {
        if ( ! isset( $entry['country_code'], $entry['message_code'], $entry['decline_url'] ) ) {
            continue;
        }

        $country = strtoupper( sanitize_text_field( $entry['country_code'] ) );
        $message = sanitize_key( $entry['message_code'] );
        $url = esc_url_raw( $entry['decline_url'] );

        if ( empty( $country ) || empty( $message ) || empty( $url ) ) {
            continue;
        }

        $key = $country . '_' . $message;
        $urls[ $key ] = $url;
    }

    return $urls;
}

function lrob_agegate_locale_to_country_code() {
    $locale = determine_locale();
    $parts = explode( '_', $locale );

    if ( count( $parts ) >= 2 ) {
        return strtoupper( sanitize_text_field( $parts[1] ) );
    }

    // Fallback: try to map language to primary country
    $lang_map = array(
        'fr' => 'FR',
        'en' => 'US',
        'de' => 'DE',
        'es' => 'ES',
        'it' => 'IT',
        'pt' => 'PT',
        'nl' => 'NL',
        'ja' => 'JP',
        'zh' => 'CN',
        'ko' => 'KR',
        'ru' => 'RU',
        'ar' => 'SA',
        'pl' => 'PL',
        'sv' => 'SE',
        'da' => 'DK',
        'fi' => 'FI',
        'no' => 'NO',
        'cs' => 'CZ',
        'el' => 'GR',
        'tr' => 'TR',
        'th' => 'TH',
        'vi' => 'VN',
        'id' => 'ID',
        'ms' => 'MY',
        'hi' => 'IN'
    );

    $lang = strtolower( $parts[0] );
    return isset( $lang_map[ $lang ] ) ? $lang_map[ $lang ] : 'US';
}

function lrob_agegate_get_available_countries() {
    $decline_urls = lrob_agegate_get_decline_urls();
    $countries = array();

    foreach ( $decline_urls as $key => $url ) {
        $parts = explode( '_', $key );
        if ( count( $parts ) >= 2 ) {
            $country = $parts[0];
            if ( ! in_array( $country, $countries, true ) ) {
                $countries[] = $country;
            }
        }
    }

    sort( $countries );
    return $countries;
}

function lrob_agegate_get_decline_url( $country_code, $message_code, $fallback = 'about:blank' ) {
    $decline_urls = lrob_agegate_get_decline_urls();
    $country_code = strtoupper( sanitize_text_field( $country_code ) );
    $message_code = sanitize_key( $message_code );

    $key = $country_code . '_' . $message_code;

    if ( isset( $decline_urls[ $key ] ) ) {
        return $decline_urls[ $key ];
    }

    // Try locale-based fallback
    $locale_country = lrob_agegate_locale_to_country_code();
    if ( $locale_country !== $country_code ) {
        $fallback_key = $locale_country . '_' . $message_code;
        if ( isset( $decline_urls[ $fallback_key ] ) ) {
            return $decline_urls[ $fallback_key ];
        }
    }

    return esc_url_raw( $fallback );
}
