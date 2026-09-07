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
			'migrating' => __( 'Migrating from', 'stackrecipes-cs' ),
			'stack'     => __( 'Migrating to', 'stackrecipes-cs' ),
			'baseline'  => __( 'Baseline cost', 'stackrecipes-cs' ),
			'outcome'   => __( 'Headline outcome', 'stackrecipes-cs' ),
			'timeline'  => __( 'Cutover window', 'stackrecipes-cs' ),
			'read_time' => __( 'Read time override (minutes)', 'stackrecipes-cs' ),
		);
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
		foreach ( array_keys( self::fields() ) as $key ) {
			register_post_meta(
				SRCS_POST_TYPE,
				'_srcs_' . $key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
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

		foreach ( self::fields() as $key => $label ) {
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

		foreach ( array_keys( self::fields() ) as $key ) {
			$field = 'srcs_' . $key;

			if ( ! isset( $_POST[ $field ] ) ) {
				continue;
			}

			$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );

			if ( '' === $value ) {
				delete_post_meta( $post_id, '_srcs_' . $key );
				continue;
			}

			update_post_meta( $post_id, '_srcs_' . $key, $value );
		}
	}
}
