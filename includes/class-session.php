<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CF7RB_Session {

	const TTL = 900;
	const DIR = 'uploads/review-before-send-for-contact-form-7';

	public static function create( $form_id, $rows, $files ) {
		$token = wp_generate_password( 32, false );

		set_transient(
			'cf7rb_' . $token,
			array(
				'form_id' => (int) $form_id,
				'rows'    => $rows,
				'files'   => $files,
				'created' => time(),
			),
			self::TTL
		);

		return $token;
	}

	public static function get( $token ) {
		if ( ! is_string( $token ) || 32 !== strlen( $token ) || ! preg_match( '/^[A-Za-z0-9]{32}$/', $token ) ) {
			return null;
		}

		$data = get_transient( 'cf7rb_' . $token );

		return is_array( $data ) ? $data : null;
	}

	public static function consume( $token ) {
		$data = self::get( $token );

		if ( null === $data ) {
			return null;
		}

		delete_transient( 'cf7rb_' . $token );

		return $data;
	}

	public static function dir( $random = false ) {
		$basedir = wp_upload_dir();

		if ( ! empty( $basedir['error'] ) ) {
			return '';
		}

		$base = trailingslashit( $basedir['basedir'] ) . 'review-before-send-for-contact-form-7';
		wp_mkdir_p( $base );

		if ( ! wp_is_writable( $base ) ) {
			return '';
		}

		self::protect( $base );

		if ( ! $random ) {
			return $base;
		}

		$dir = trailingslashit( $base ) . wp_generate_password( 10, false );

		if ( wp_mkdir_p( $dir ) ) {
			self::protect( $dir );
			return $dir;
		}

		return $base;
	}

	public static function rel_path( $absolute ) {
		$content = trailingslashit( WP_CONTENT_DIR );

		return 0 === strpos( $absolute, $content ) ? substr( $absolute, strlen( $content ) ) : '';
	}

	private static function protect( $dir ) {
		$htaccess = trailingslashit( $dir ) . '.htaccess';

		if ( ! file_exists( $htaccess ) ) {
			@file_put_contents( $htaccess, "Require all denied\n" );
		}

		$index = trailingslashit( $dir ) . 'index.php';

		if ( ! file_exists( $index ) ) {
			@file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}

	public static function delete_files( $files ) {
		foreach ( (array) $files as $file ) {
			$path = trailingslashit( WP_CONTENT_DIR ) . $file['rel'];

			if ( wpcf7_is_file_path_in_content_dir( $path ) && is_file( $path ) ) {
				wp_delete_file( $path );
			}

			$dir = dirname( $path );

			if ( is_dir( $dir ) ) {
				foreach ( array( '.htaccess', 'index.php' ) as $extra ) {
					$extra_path = trailingslashit( $dir ) . $extra;

					if ( is_file( $extra_path ) ) {
						wp_delete_file( $extra_path );
					}
				}

				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- WordPress has no native alternative for removing directories.
				@rmdir( $dir );
			}
		}
	}

	public static function cleanup_old_files() {
		$base = self::dir( false );

		if ( ! $base ) {
			return;
		}

		$now     = time();
		$entries = scandir( $base );

		foreach ( (array) $entries as $entry ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$path = trailingslashit( $base ) . $entry;

			if ( is_dir( $path ) && $now - @filemtime( $path ) > 3600 ) {
				foreach ( glob( trailingslashit( $path ) . '*' ) as $file ) {
					wp_delete_file( $file );
				}

				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- WordPress has no native alternative for removing directories.
				@rmdir( $path );
			}
		}
	}
}
