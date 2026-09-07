<?php
/**
 * "At a glance" meta fields shown on archive cards and in the single header.
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the case study meta panel.
 */
class SRCS_Meta {

	/**
	 * Field definitions: key => label.
	 *
	 * @return array<string,string>
	 */
	public static function fields() {
		return array(
			'vertical'  => __( 'Vertical', 'stackrecipes-cs' ),
			'scale'     => __( 'Scale', 'stackrecipes-cs' ),
			'migrating' => __( 'Migrating from', 'stackrecipes-cs' ),
			'stack'     => __( 'Migrating to', 'stackrecipes-cs' ),
			'baseline'  => __( 'Baseline cost', 'stackrecipes-cs' ),
			'outcome'   => __( 'Headline outcome', 'stackrecipes-cs' ),
			'timeline'  => __( 'Cutover window', 'stackrecipes-cs' ),
			'read_time' => __( 'Read time override (minutes)', 'stackrecipes-cs' ),
		);
	}

	/**
	 * Per-post CTA overrides. Blank falls back to the site-wide defaults.
	 *
	 * @return array<string,string>
	 */
	public static function cta_fields() {
		return array(
			'cta_heading' => __( 'Heading', 'stackrecipes-cs' ),
			'cta_body'    => __( 'Body', 'stackrecipes-cs' ),
			'cta_label'   => __( 'Button label', 'stackrecipes-cs' ),
			'cta_url'     => __( 'Button URL', 'stackrecipes-cs' ),
		);
	}

	/**
	 * Every editable field, across both boxes.
	 *
	 * @return array<string,string>
	 */
	public static function all_fields() {
		return array_merge( self::fields(), self::cta_fields() );
	}

	/**
	 * Hook registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_box' ) );
		add_action( 'save_post_' . SRCS_POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Expose the fields to REST so the block editor sidebar can read them too.
	 *
	 * @return void
	 */
	public static function register_meta() {
		foreach ( array_keys( self::all_fields() ) as $key ) {
			register_post_meta(
				SRCS_POST_TYPE,
				'_srcs_' . $key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => ( 'cta_url' === $key ) ? 'esc_url_raw' : 'sanitize_text_field',
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Add the classic-editor meta box.
	 *
	 * @return void
	 */
	public static function add_box() {
		add_meta_box(
			'srcs_at_a_glance',
			__( 'At a glance', 'stackrecipes-cs' ),
			array( __CLASS__, 'render_box' ),
			SRCS_POST_TYPE,
			'side',
			'high'
		);

		add_meta_box(
			'srcs_cta',
			__( 'CTA banner', 'stackrecipes-cs' ),
			array( __CLASS__, 'render_cta_box' ),
			SRCS_POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Render the CTA override box.
	 *
	 * @param WP_Post $post Post being edited.
	 * @return void
	 */
	public static function render_cta_box( $post ) {
		wp_nonce_field( 'srcs_save_meta', 'srcs_meta_nonce' );

		echo '<p class="description">' . esc_html__( 'Overrides the banner shown at the foot of this teardown. Leave blank to use the site-wide copy.', 'stackrecipes-cs' ) . '</p>';

		self::render_fields( $post, self::cta_fields() );
	}

	/**
	 * Print one text input per field.
	 *
	 * @param WP_Post              $post   Post being edited.
	 * @param array<string,string> $fields Field key => label.
	 * @return void
	 */
	protected static function render_fields( $post, array $fields ) {
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, '_srcs_' . $key, true );
			printf(
				'<p><label for="%1$s" style="display:block;font-weight:600;margin-bottom:4px;">%2$s</label>'
				. '<input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s" /></p>',
				esc_attr( 'srcs_' . $key ),
				esc_html( $label ),
				esc_attr( (string) $value )
			);
		}
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Post being edited.
	 * @return void
	 */
	public static function render_box( $post ) {
		wp_nonce_field( 'srcs_save_meta', 'srcs_meta_nonce' );

		echo '<p class="description">' . esc_html__( 'Shown on the archive card and in the teardown header. Leave blank to hide a row.', 'stackrecipes-cs' ) . '</p>';

		self::render_fields( $post, self::fields() );
	}

	/**
	 * Persist the meta box values.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || 'auto-draft' === $post->post_status ) {
			return;
		}

		$nonce = isset( $_POST['srcs_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['srcs_meta_nonce'] ) ) : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'srcs_save_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( array_keys( self::all_fields() ) as $key ) {
			$field = 'srcs_' . $key;

			if ( ! isset( $_POST[ $field ] ) ) {
				continue;
			}

			$raw = wp_unslash( $_POST[ $field ] );

			$value = ( 'cta_url' === $key )
				? esc_url_raw( $raw )
				: sanitize_text_field( $raw );

			if ( '' === $value ) {
				delete_post_meta( $post_id, '_srcs_' . $key );
				continue;
			}

			update_post_meta( $post_id, '_srcs_' . $key, $value );
		}
	}
}
