<?php
/**
 * Plugin Name:       Review Before Send for Contact Form 7
 * Plugin URI:        https://github.com/slobostep/review-before-send-for-contact-form-7
 * Description:       Adds a review-and-confirm step to Contact Form 7 forms before the mail is sent. Built-in honeypot and time-trap spam protection.
 * Version:           0.1.9
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Slobodan Stepic
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       review-before-send-for-contact-form-7
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CF7RB_VERSION', '0.1.9' );
define( 'CF7RB_PLUGIN_FILE', __FILE__ );
define( 'CF7RB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CF7RB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CF7RB_SLUG', 'cf7rb' );

require_once CF7RB_PLUGIN_DIR . 'includes/class-settings.php';
require_once CF7RB_PLUGIN_DIR . 'includes/class-session.php';
require_once CF7RB_PLUGIN_DIR . 'includes/class-renderer.php';
require_once CF7RB_PLUGIN_DIR . 'includes/class-ajax.php';

function cf7rb_init() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		add_action( 'admin_notices', 'cf7rb_missing_cf7_notice' );
		return;
	}

	load_plugin_textdomain( 'review-before-send-for-contact-form-7', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	CF7RB_Settings::register();
	CF7RB_Ajax::register();

	add_action( 'wp_enqueue_scripts', 'cf7rb_enqueue_assets' );
	add_filter( 'wpcf7_form_hidden_fields', 'cf7rb_add_hidden_fields', 10, 1 );
	add_filter( 'wpcf7_form_class_attr', 'cf7rb_add_form_class', 10, 1 );
}

function cf7rb_add_form_class( $class ) {
	$form = wpcf7_get_current_contact_form();

	if ( $form && CF7RB_Settings::is_enabled( $form->id() ) ) {
		$class .= ' cf7rb-form';
	}

	return $class;
}

function cf7rb_missing_cf7_notice() {
	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'Review Before Send for Contact Form 7 requires the Contact Form 7 plugin to be installed and active.', 'review-before-send-for-contact-form-7' );
	echo '</p></div>';
}

function cf7rb_add_hidden_fields( $fields ) {
	$form = wpcf7_get_current_contact_form();

	if ( ! $form || ! CF7RB_Settings::is_enabled( $form->id() ) ) {
		return $fields;
	}

	$fields['cf7rb_hp']    = '';
	$fields['cf7rb_start'] = '';

	return $fields;
}

function cf7rb_enqueue_assets() {
	$form_ids = CF7RB_Settings::enabled_form_ids();

	if ( empty( $form_ids ) ) {
		return;
	}

	wp_enqueue_style( 'cf7rb-style', CF7RB_PLUGIN_URL . 'assets/css/cf7rb.css', array(), CF7RB_VERSION );
	wp_enqueue_script( 'cf7rb-script', CF7RB_PLUGIN_URL . 'assets/js/cf7rb.js', array(), CF7RB_VERSION, true );

	wp_localize_script(
		'cf7rb-script',
		'cf7rbConfig',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'cf7rb_review' ),
			'forms'   => array_fill_keys( $form_ids, true ),
			'labels'  => CF7RB_Renderer::labels(),
		)
	);
}

add_action( 'plugins_loaded', 'cf7rb_init' );
