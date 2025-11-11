<?php
/**
 * Admin functionality
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LRob_AgeGate_Admin {

    private $option_key = 'lrob_age_gate_options';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_lrob_invalidate_cookies', array( $this, 'handle_invalidate' ) );
        add_action( 'wp_ajax_lrob_get_decline_url', array( $this, 'ajax_get_decline_url' ) );
    }

    public function add_menu() {
        add_menu_page(
            __( 'Age Gate', 'lrob-age-gate' ),
            __( 'Age Gate', 'lrob-age-gate' ),
            'manage_options',
            'lrob-age-gate',
            array( $this, 'render_page' ),
            'dashicons-lock',
            30
        );
    }

    public function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'lrob-age-gate' ) === false ) return;

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        wp_add_inline_script( 'wp-color-picker', "jQuery(function($){
            var \$theme = $('select[name=\"lrob_age_gate_options[theme]\"]');
            var \$colorInputs = $('input[data-color-field=\"true\"]');
            var \$colorRows = \$colorInputs.closest('tr');

            function toggleColors() {
                var isCustom = \$theme.val() === 'custom';

                if (isCustom) {
                    \$colorRows.show();
                    \$colorInputs.each(function() {
                        if (!$(this).hasClass('wp-color-picker')) {
                            var \$input = $(this);
                            \$input.wpColorPicker({
                                change: function(event, ui) {
                                    // Update input value and trigger preview update
                                    \$input.val(ui.color.toString()).trigger('input');
                                }
                            });
                        }
                    });
                } else {
                    \$colorRows.hide();
                    \$colorInputs.each(function() {
                        if ($(this).hasClass('wp-color-picker')) {
                            $(this).iris('hide');
                        }
                    });
                }
            }

            \$theme.on('change', toggleColors);
            toggleColors();
        });" );
        wp_add_inline_style( 'wp-color-picker', '.lrob-card{background:#fff;padding:20px;margin:20px 0;border:1px solid #ccd0d4;box-shadow:0 1px 1px rgba(0,0,0,.04)} .lrob-info{background:#f0f6fc;border-left:4px solid #72aee6;padding:12px;margin:10px 0} .lrob-warning{background:#fcf3cd;border-left:4px solid #dba617;padding:12px;margin:10px 0}' );
    }

    public function register_settings() {
        register_setting( 'lrob-age-gate', $this->option_key, array(
            'sanitize_callback' => array( $this, 'sanitize_options' )
        ) );

        add_settings_section( 'general', __( 'General Settings', 'lrob-age-gate' ), array( $this, 'section_general' ), 'lrob-age-gate' );
        add_settings_section( 'content', __( 'Content & Messages', 'lrob-age-gate' ), array( $this, 'section_content' ), 'lrob-age-gate' );
        add_settings_section( 'appearance', __( 'Appearance', 'lrob-age-gate' ), null, 'lrob-age-gate' );
        add_settings_section( 'tools', __( 'Tools', 'lrob-age-gate' ), null, 'lrob-age-gate' );

        $this->add_field( 'enabled', 'checkbox', __( 'Enable Age Gate', 'lrob-age-gate' ), 'general' );
        $this->add_field( 'cookie_days', 'number', __( 'Cookie duration (days)', 'lrob-age-gate' ), 'general' );

        $this->add_field( 'message_template', 'message_select', __( 'Quick Load', 'lrob-age-gate' ), 'content' );
        $this->add_field( 'min_age', 'number', __( 'Minimum age', 'lrob-age-gate' ), 'content' );
        $this->add_field( 'title', 'text', __( 'Title', 'lrob-age-gate' ), 'content' );
        $this->add_field( 'message', 'textarea', __( 'Message', 'lrob-age-gate' ), 'content' );
        $this->add_field( 'legal', 'textarea', __( 'Legal notice', 'lrob-age-gate' ), 'content' );
        $this->add_field( 'accept_label', 'text', __( 'Accept button', 'lrob-age-gate' ), 'content' );
        $this->add_field( 'decline_label', 'text', __( 'Decline button', 'lrob-age-gate' ), 'content' );
        $this->add_field( 'decline_url', 'url', __( 'Decline redirect URL', 'lrob-age-gate' ), 'content' );

        $this->add_field( 'theme', 'select', __( 'Theme', 'lrob-age-gate' ), 'appearance', array( 'options' => array(
            'auto' => __( 'Auto (follows system)', 'lrob-age-gate' ),
            'light' => __( 'Light', 'lrob-age-gate' ),
            'dark' => __( 'Dark', 'lrob-age-gate' ),
            'custom' => __( 'Custom', 'lrob-age-gate' )
        ) ) );
        $this->add_field( 'modal_bg', 'color', __( 'Background', 'lrob-age-gate' ), 'appearance' );
        $this->add_field( 'modal_text', 'color', __( 'Text color', 'lrob-age-gate' ), 'appearance' );
        $this->add_field( 'btn_accept_bg', 'color', __( 'Accept bg', 'lrob-age-gate' ), 'appearance' );
        $this->add_field( 'btn_accept_text', 'color', __( 'Accept text', 'lrob-age-gate' ), 'appearance' );
        $this->add_field( 'btn_decline_bg', 'color', __( 'Decline bg', 'lrob-age-gate' ), 'appearance' );
        $this->add_field( 'btn_decline_text', 'color', __( 'Decline text', 'lrob-age-gate' ), 'appearance' );
        $this->add_field( 'modal_border_radius', 'number', __( 'Border radius (px)', 'lrob-age-gate' ), 'appearance' );
        $this->add_field( 'backdrop_blur', 'number', __( 'Backdrop blur (px)', 'lrob-age-gate' ), 'appearance' );

        $this->add_field( 'invalidate', 'custom', __( 'Invalidate all cookies', 'lrob-age-gate' ), 'tools' );
    }

    public function section_general() {
        echo '<p>' . esc_html__( 'Configure age verification behavior.', 'lrob-age-gate' ) . '</p>';
    }

    public function section_content() {
        echo '<p>' . esc_html__( 'Select a preset to quick-load a message template, then customize the fields as needed.', 'lrob-age-gate' ) . '</p>';

        $opts = get_option( $this->option_key, array() );
        if ( empty( $opts['title'] ) || empty( $opts['message'] ) ) {
            echo '<div class="lrob-warning"><strong>' . esc_html__( 'Configuration Required:', 'lrob-age-gate' ) . '</strong> ';
            echo esc_html__( 'Please select a preset or configure all content fields below.', 'lrob-age-gate' ) . '</div>';
        }
    }

    private function add_field( $id, $type, $label, $section, $attrs = array() ) {
        add_settings_field( $id, $label, array( $this, 'render_field' ), 'lrob-age-gate', $section,
            array_merge( $attrs, array( 'id' => $id, 'type' => $type ) ) );
    }

    public function render_field( $args ) {
        $opts = get_option( $this->option_key, array() );
        $id = $args['id'];
        $type = $args['type'];
        $name = $this->option_key . '[' . $id . ']';
        $value = $opts[$id] ?? '';

        switch ( $type ) {
            case 'checkbox':
                printf( '<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
                    esc_attr( $name ), checked( $value, 1, false ), esc_html__( 'Enable', 'lrob-age-gate' ) );
                break;

            case 'message_select':
                require_once LROB_AGEGATE_PATH . 'includes/presets.php';
                $templates = lrob_agegate_get_message_templates();
                $countries = lrob_agegate_get_available_countries();
                $default_country = lrob_agegate_locale_to_country_code();

                $message_value = $opts['message_template'] ?? '';
                $country_value = ! empty( $opts['decline_country'] ) ? $opts['decline_country'] : $default_country;

                // Message template dropdown
                printf( '<select name="%s" id="lrob-message-select" class="lrob-preset-field" style="width:200px;">',
                    esc_attr( $this->option_key . '[message_template]' ) );
                echo '<option value="">' . esc_html__( '-- Select Message --', 'lrob-age-gate' ) . '</option>';

                foreach ( $templates as $code => $template ) {
                    printf( '<option value="%s"%s>%s</option>',
                        esc_attr( $code ),
                        selected( $message_value, $code, false ),
                        esc_html( $template['message_label'] )
                    );
                }
                echo '</select>';

                // Country dropdown for decline URL
                printf( ' <select name="%s" id="lrob-country-select" class="lrob-preset-field" style="width:120px; margin-left:8px;">',
                    esc_attr( $this->option_key . '[decline_country]' ) );
                echo '<option value="">' . esc_html__( '-- Country --', 'lrob-age-gate' ) . '</option>';

                foreach ( $countries as $country ) {
                    printf( '<option value="%s"%s>%s</option>',
                        esc_attr( $country ),
                        selected( $country_value, $country, false ),
                        esc_html( strtoupper( $country ) )
                    );
                }
                echo '</select>';

                // Load button
                echo ' <button type="button" id="lrob-load-preset" class="button" title="' . esc_attr__( 'Load Preset', 'lrob-age-gate' ) . '" style="margin-left:8px; min-width:0; padding:4px 8px; height:30px;" disabled>';
                echo '<span class="dashicons dashicons-update" style="font-size:16px; width:16px; height:16px; line-height:1;"></span>';
                echo '</button>';

                echo '<p class="description" style="margin-top:8px;">';
                echo esc_html__( 'Message template is universal. Country is used for decline URL only.', 'lrob-age-gate' );
                echo '</p>';

                // Legal disclaimer
                echo '<p class="description" style="margin-top:12px; padding:12px; background:#fff3cd; border-left:4px solid #ffc107;">';
                echo '<strong>' . esc_html__( 'Legal Disclaimer:', 'lrob-age-gate' ) . '</strong> ';
                echo esc_html__( 'Laws vary by jurisdiction and change over time. You are responsible for ensuring compliance with your local regulations. Always consult legal counsel and adapt messages to your specific requirements.', 'lrob-age-gate' );
                echo '</p>';
                break;

            case 'number':
                printf( '<input type="number" name="%s" value="%s" class="small-text">',
                    esc_attr( $name ), esc_attr( $value ) );
                break;

            case 'color':
                printf( '<input type="text" name="%s" value="%s" class="lrob-color" data-color-field="true">',
                    esc_attr( $name ), esc_attr( $value ) );
                break;

            case 'textarea':
                printf( '<textarea name="%s" rows="3" class="large-text">%s</textarea>',
                    esc_attr( $name ), esc_textarea( $value ) );
                echo '<p class="description">' . esc_html__( 'Use {age} for minimum age.', 'lrob-age-gate' ) . '</p>';
                break;

            case 'select':
                $options = $args['options'] ?? array();
                printf( '<select name="%s">', esc_attr( $name ) );
                foreach ( $options as $key => $label ) {
                    printf( '<option value="%s"%s>%s</option>',
                        esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
                }
                echo '</select>';
                break;

            case 'url':
                printf( '<input type="url" name="%s" value="%s" class="regular-text">',
                    esc_attr( $name ), esc_url( $value ) );
                break;

            case 'custom':
                if ( $id === 'invalidate' ) {
                    echo '<div id="lrob-invalidate-placeholder"></div>';
                    echo '<script>jQuery(function($){ $("#lrob-invalidate-section").appendTo("#lrob-invalidate-placeholder").show(); });</script>';
                }
                break;

            default:
                printf( '<input type="text" name="%s" value="%s" class="regular-text">',
                    esc_attr( $name ), esc_attr( $value ) );
        }
    }

    public function handle_invalidate() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'lrob-age-gate' ) );
        }

        check_admin_referer( 'lrob_invalidate_cookies', 'lrob_nonce' );

        // Change token to invalidate all existing cookies globally
        $opts = get_option( $this->option_key, array() );
        $opts['token'] = time();
        update_option( $this->option_key, $opts );

        wp_redirect( add_query_arg( array(
            'page' => 'lrob-age-gate',
            'invalidated' => '1'
        ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function ajax_get_decline_url() {
        check_ajax_referer( 'lrob_get_decline_url', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized', 'lrob-age-gate' ) ) );
        }

        $country = sanitize_text_field( $_POST['country'] ?? '' );
        $message = sanitize_text_field( $_POST['message'] ?? '' );

        if ( empty( $country ) || empty( $message ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing parameters', 'lrob-age-gate' ) ) );
        }

        require_once LROB_AGEGATE_PATH . 'includes/presets.php';
        $url = lrob_agegate_get_decline_url( $country, $message );

        wp_send_json_success( array( 'url' => $url ) );
    }

    public function sanitize_options( $input ) {
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

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        require_once LROB_AGEGATE_PATH . 'views/admin-page.php';
    }
}
