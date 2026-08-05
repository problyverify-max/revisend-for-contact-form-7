<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CF7RB_Settings {

	const META_KEY = '_cf7rb_enabled';

	public static function register() {
		add_filter( 'wpcf7_editor_panels', array( __CLASS__, 'add_panel' ), 10, 1 );
		add_action( 'wpcf7_save_contact_form', array( __CLASS__, 'save' ), 10, 3 );
	}

	public static function add_panel( $panels ) {
		$panels['cf7rb-panel'] = array(
			'title'    => __( 'Review Before Send', 'review-before-send-for-contact-form-7' ),
			'callback' => array( __CLASS__, 'render_panel' ),
		);

		return $panels;
	}

	public static function render_panel( $contact_form ) {
		$enabled = self::is_enabled( $contact_form->id() );
		?>
		<h2><?php echo esc_html__( 'Review Before Send for Contact Form 7', 'review-before-send-for-contact-form-7' ); ?></h2>

		<fieldset>
			<legend><?php echo esc_html__( 'Confirmation step', 'review-before-send-for-contact-form-7' ); ?></legend>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<?php echo esc_html__( 'Enable review step', 'review-before-send-for-contact-form-7' ); ?>
						</th>
						<td>
							<label for="cf7rb-enable">
								<input
									type="checkbox"
									name="wpcf7-cf7rb[enabled]"
									id="cf7rb-enable"
									value="1"
									<?php checked( $enabled ); ?>
								/>
								<?php echo esc_html__( 'Show a review step before this form is sent', 'review-before-send-for-contact-form-7' ); ?>
							</label>

							<p class="description">
								<?php echo esc_html__( 'Visitors will see a summary of their input and must confirm before the form is submitted.', 'review-before-send-for-contact-form-7' ); ?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}

	public static function save( $contact_form, $args, $context ) {
		$prop = wp_parse_args(
			(array) wpcf7_superglobal_post( 'wpcf7-cf7rb', array() ),
			array(
				'enabled' => false,
			)
		);

		$form_id = $contact_form->id();

		if ( ! empty( $prop['enabled'] ) ) {
			update_post_meta( $form_id, self::META_KEY, '1' );
		} else {
			delete_post_meta( $form_id, self::META_KEY );
		}
	}

	public static function is_enabled( $form_id ) {
		return '1' === get_post_meta( (int) $form_id, self::META_KEY, true );
	}

	public static function enabled_form_ids() {
		$ids = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- the number of Contact Form 7 forms is small; the query runs once per page load.
				'meta_key'   => self::META_KEY,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- the number of Contact Form 7 forms is small; the query runs once per page load.
				'meta_value' => '1',
				'no_found_rows'  => true,
			)
		);

		return array_map( 'absint', (array) $ids );
	}
}
