<?php
/**
 * Uninstall script
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;
delete_option( 'lrob_age_gate_options' );
