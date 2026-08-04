<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CF7RB_Ajax {

	private static $pending_files = array();

	public static function register() {
		add_action( 'wp_ajax_cf7rb_review', array( __CLASS__, 'review' ) );
		add_action( 'wp_ajax_nopriv_cf7rb_review', array( __CLASS__, 'review' ) );

		add_action(
			'wpcf7_before_send_mail',
			array( __CLASS__, 'gate_submission' ),
			10,
			3
		);

		add_action( 'wpcf7_mail_sent', array( __CLASS__, 'cleanup' ) );
		add_action( 'wpcf7_mail_failed', array( __CLASS__, 'cleanup' ) );

		add_filter( 'wpcf7_mail_components', array( __CLASS__, 'filter_attachments' ), 10, 3 );
	}

	public static function review() {
		if ( ! check_ajax_referer( 'cf7rb_review', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => CF7RB_Renderer::labels()['error'] ) );
		}

		CF7RB_Session::cleanup_old_files();

		$form_id = isset( $_POST['cf7rb_form'] ) ? absint( $_POST['cf7rb_form'] ) : 0;
		$form    = $form_id ? wpcf7_contact_form( $form_id ) : null;

		if ( ! $form || ! CF7RB_Settings::is_enabled( $form_id ) ) {
			wp_send_json_error( array( 'message' => CF7RB_Renderer::labels()['error'] ) );
		}

		if ( ! self::passes_spam_checks() ) {
			wp_send_json_error( array( 'message' => CF7RB_Renderer::labels()['error'] ) );
		}

		$validation = self::validate_fields( $form );

		$invalid = $validation->is_valid()
			? array()
			: array_keys( $validation->get_invalid_fields() );

		$posted = wp_unslash( (array) $_POST );

		$rows  = CF7RB_Renderer::build_rows( $form, $posted, array() );
		$files = self::collect_files( $form );

		if ( false === $files ) {
			wp_send_json_error(
				array(
					'message' => __( 'One of the uploaded files is too large or has an invalid type.', 'cf7-review-before-send' ),
				)
			);
		}

		if ( ! empty( $files ) ) {
			$rows = CF7RB_Renderer::build_rows( $form, $posted, $files );
		}

		$labels = CF7RB_Renderer::labels();
		$token  = CF7RB_Session::create( $form_id, $rows, $files );

		$file_names = array();

		foreach ( $files as $field => $file ) {
			$file_names[ $field ] = $file['orig'];
		}

		wp_send_json_success(
			array(
				'token'   => $token,
				'summary' => CF7RB_Renderer::render_summary( $rows, $labels ),
				'files'   => $file_names,
				'invalid' => $invalid,
			)
		);
	}

	public static function gate_submission( $contact_form, &$abort, $submission ) {
		if ( ! CF7RB_Settings::is_enabled( $contact_form->id() ) ) {
			return;
		}

		$token = isset( $_POST['cf7rb_token'] )
			? sanitize_text_field( wp_unslash( $_POST['cf7rb_token'] ) )
			: '';

		$data = $token ? CF7RB_Session::consume( $token ) : null;

		if ( ! $data || (int) $data['form_id'] !== (int) $contact_form->id() ) {
			$abort = true;

			$submission->set_response(
				__( 'Your submission was not confirmed. Please submit the form again using the confirmation step.', 'cf7-review-before-send' )
			);

			return;
		}

		self::$pending_files = $data['files'];

		if ( ! empty( self::$pending_files ) ) {
			$rel_paths = array_column( self::$pending_files, 'rel' );

			$submission->add_extra_attachments( $rel_paths, 'mail' );
		}
	}

	public static function cleanup() {
		if ( ! empty( self::$pending_files ) ) {
			CF7RB_Session::delete_files( self::$pending_files );
			self::$pending_files = array();
		}
	}

	public static function filter_attachments( $components, $contact_form, $mail ) {
		if ( ! CF7RB_Settings::is_enabled( $contact_form->id() ) ) {
			return $components;
		}

		$components['attachments'] = array_filter(
			(array) $components['attachments'],
			static function ( $attachment ) {
				$path = path_join( WP_CONTENT_DIR, $attachment );

				return wpcf7_is_file_path_in_content_dir( $path ) && is_file( $path );
			}
		);

		return $components;
	}

	private static function validate_fields( $form ) {
		$result = new WPCF7_Validation();

		$form->validate_schema(
			array(
				'text'  => true,
				'file'  => false,
				'field' => array(),
			),
			$result
		);

		$tags = $form->scan_form_tags(
			array(
				'feature' => '! file-uploading',
			)
		);

		foreach ( $tags as $tag ) {
			$type   = $tag->type;
			$result = apply_filters( "wpcf7_validate_{$type}", $result, $tag );
		}

		return apply_filters( 'wpcf7_validate', $result, $tags );
	}

	private static function passes_spam_checks() {
		if ( ! isset( $_POST['cf7rb_hp'] ) || '' !== (string) $_POST['cf7rb_hp'] ) {
			return false;
		}

		$start  = isset( $_POST['cf7rb_start'] ) ? (int) $_POST['cf7rb_start'] : 0;
		$min_ts = (float) apply_filters( 'cf7rb_min_submit_seconds', 2.5 );

		if ( $start <= 0 ) {
			return false;
		}

		$elapsed = microtime( true ) - ( $start / 1000 );

		if ( $elapsed < $min_ts || $elapsed > CF7RB_Session::TTL ) {
			return false;
		}

		return true;
	}

	private static function collect_files( $form ) {
		$files = array();

		$tags = $form->scan_form_tags(
			array(
				'feature' => 'file-uploading',
			)
		);

		foreach ( $tags as $tag ) {
			if ( empty( $_FILES[ $tag->name ] ) ) {
				continue;
			}

			$file = $_FILES[ $tag->name ];

			if ( ! empty( $file['error'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
				continue;
			}

			$limit = $tag->get_limit_option();

			if ( $file['size'] > $limit ) {
				return false;
			}

			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

			if ( ! self::is_allowed_ext( $ext, $tag ) ) {
				return false;
			}

			$dir = CF7RB_Session::dir( true );

			if ( ! $dir ) {
				return false;
			}

			$orig = sanitize_file_name( wp_basename( $file['name'] ) );
			$name = wp_unique_filename( $dir, $orig );

			if ( ! move_uploaded_file( $file['tmp_name'], trailingslashit( $dir ) . $name ) ) {
				return false;
			}

			$files[ $tag->name ] = array(
				'rel'  => CF7RB_Session::rel_path( trailingslashit( $dir ) . $name ),
				'orig' => $orig,
			);
		}

		return $files;
	}

	private static function is_allowed_ext( $ext, $tag ) {
		$accept = $tag->get_option( 'filetypes' );

		if ( empty( $accept ) ) {
			return true;
		}

		$allowed = explode( ',', wpcf7_acceptable_filetypes( $accept, 'attr' ) );

		foreach ( $allowed as $type ) {
			$type = trim( strtolower( $type ) );

			if ( '' === $type ) {
				continue;
			}

			if ( '.' === $type[0] && ltrim( $type, '.' ) === $ext ) {
				return true;
			}

			if ( preg_match( '|^[a-z]+/\*$|', $type ) ) {
				return true;
			}
		}

		return false;
	}
}
