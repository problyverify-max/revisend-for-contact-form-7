<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CF7RB_Renderer {

	const DISPLAY_BASETYPES = array(
		'text',
		'email',
		'url',
		'tel',
		'number',
		'date',
		'textarea',
		'select',
		'checkbox',
		'radio',
		'acceptance',
		'quiz',
		'file',
	);

	public static function labels() {
		$labels = array(
			'heading'  => __( 'Review your submission', 'cf7-review-before-send' ),
			'intro'    => __( 'Please check the information below before sending.', 'cf7-review-before-send' ),
			'edit'     => __( 'Edit', 'cf7-review-before-send' ),
			'confirm'  => __( 'Confirm &amp; Send', 'cf7-review-before-send' ),
			'files'    => __( 'Files', 'cf7-review-before-send' ),
			'error'    => __( 'Something went wrong. Please try again.', 'cf7-review-before-send' ),
			'accepted' => __( 'Yes', 'cf7-review-before-send' ),
			'declined' => __( 'No', 'cf7-review-before-send' ),
			'fileNote' => __( 'File selected', 'cf7-review-before-send' ),
		);

		return apply_filters( 'cf7rb_review_labels', $labels );
	}

	public static function build_rows( $form, $posted, $files ) {
		$rows = array();

		$tags = $form->scan_form_tags(
			array(
				'name-attr' => true,
				'! not-for-mail' => true,
			)
		);

		foreach ( $tags as $tag ) {
			if ( ! in_array( $tag->basetype, self::DISPLAY_BASETYPES, true ) ) {
				continue;
			}

			$name  = $tag->name;
			$label = self::pretty_name( $name );

			if ( 'file' === $tag->basetype ) {
				if ( ! empty( $files[ $name ] ) ) {
					$rows[] = array(
						'label' => $label,
						'value' => $files[ $name ]['orig'],
					);
				} elseif ( $tag->is_required() ) {
					$rows[] = array(
						'label' => $label,
						'value' => '—',
					);
				}

				continue;
			}

			$value = isset( $posted[ $name ] ) ? $posted[ $name ] : '';

			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			if ( 'acceptance' === $tag->basetype ) {
				$value = ! empty( $value )
					? self::labels()['accepted']
					: self::labels()['declined'];
			}

			$value = trim( (string) $value );

			if ( '' === $value ) {
				if ( $tag->is_required() ) {
					$rows[] = array(
						'label' => $label,
						'value' => '—',
					);
				}

				continue;
			}

			$rows[] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		return $rows;
	}

	public static function render_summary( $rows, $labels ) {
		$heading = esc_html( $labels['heading'] );
		$intro   = esc_html( $labels['intro'] );
		$edit    = esc_html( $labels['edit'] );
		$confirm = wp_kses_post( $labels['confirm'] );

		$html = '<div class="cf7rb-summary">';
		$html .= '<h3 class="cf7rb-heading">' . $heading . '</h3>';
		$html .= '<p class="cf7rb-intro">' . $intro . '</p>';

		if ( empty( $rows ) ) {
			$html .= '<p class="cf7rb-empty">' . esc_html__( 'No data to review.', 'cf7-review-before-send' ) . '</p>';
		} else {
			$html .= '<dl class="cf7rb-fields">';

			foreach ( $rows as $row ) {
				$value = str_replace( "\n", "<br />\n", esc_html( $row['value'] ) );

				$html .= '<dt>' . esc_html( $row['label'] ) . '</dt>';
				$html .= '<dd>' . $value . '</dd>';
			}

			$html .= '</dl>';
		}

		$html .= '<div class="cf7rb-actions">';
		$html .= '<button type="button" class="cf7rb-btn cf7rb-edit">' . $edit . '</button>';
		$html .= '<button type="button" class="cf7rb-btn cf7rb-btn-primary cf7rb-confirm">' . $confirm . '</button>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	private static function pretty_name( $name ) {
		$name = str_replace( array( '-', '_' ), ' ', $name );

		return ucwords( $name );
	}
}
