<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$form_ids = get_posts(
	array(
		'post_type'      => 'wpcf7_contact_form',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

foreach ( (array) $form_ids as $form_id ) {
	delete_post_meta( (int) $form_id, '_cf7rb_enabled' );
}

global $wpdb;

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'_transient_cf7rb_%'
	)
);

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'_transient_timeout_cf7rb_%'
	)
);

$uploads = wp_upload_dir();
$dir     = trailingslashit( $uploads['basedir'] ) . 'review-before-send-for-contact-form-7';

if ( is_dir( $dir ) ) {
	foreach ( glob( trailingslashit( $dir ) . '*' ) as $entry ) {
		if ( is_dir( $entry ) ) {
			foreach ( glob( trailingslashit( $entry ) . '*' ) as $file ) {
				@unlink( $file );
			}

			@rmdir( $entry );
		} else {
			@unlink( $entry );
		}
	}

	@rmdir( $dir );
}
