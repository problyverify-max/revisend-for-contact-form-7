<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$cf7rb_form_ids = get_posts(
	array(
		'post_type'      => 'wpcf7_contact_form',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	)
);

foreach ( (array) $cf7rb_form_ids as $cf7rb_form_id ) {
	delete_post_meta( (int) $cf7rb_form_id, '_cf7rb_enabled' );
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- deleting transient rows by pattern requires a direct query; caching does not apply during uninstall.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'_transient_cf7rb_%'
	)
);

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- deleting transient rows by pattern requires a direct query; caching does not apply during uninstall.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		'_transient_timeout_cf7rb_%'
	)
);

$cf7rb_uploads = wp_upload_dir();
$cf7rb_dir     = trailingslashit( $cf7rb_uploads['basedir'] ) . 'revisend-for-contact-form-7';

if ( is_dir( $cf7rb_dir ) ) {
	foreach ( glob( trailingslashit( $cf7rb_dir ) . '*' ) as $cf7rb_entry ) {
		if ( is_dir( $cf7rb_entry ) ) {
			foreach ( glob( trailingslashit( $cf7rb_entry ) . '*' ) as $cf7rb_file ) {
				wp_delete_file( $cf7rb_file );
			}

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- WordPress has no native alternative for removing directories.
			@rmdir( $cf7rb_entry );
		} else {
			wp_delete_file( $cf7rb_entry );
		}
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- WordPress has no native alternative for removing directories.
	@rmdir( $cf7rb_dir );
}
