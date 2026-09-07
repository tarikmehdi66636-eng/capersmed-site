<?php
/**
 * Post type + taxonomy registration for the case studies section.
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the case_study post type and its topic taxonomy.
 */
class SRCS_CPT {

	/**
	 * Hook registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
		add_action( 'init', array( __CLASS__, 'maybe_flush' ), 99 );
		add_filter( 'post_type_link', array( __CLASS__, 'ensure_trailing_slash' ), 10, 2 );
	}

	/**
	 * The case_study post type, archived at /case-studies/.
	 *
	 * @return void
	 */
	public static function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Case Studies', 'post type general name', 'stackrecipes-cs' ),
			'singular_name'         => _x( 'Case Study', 'post type singular name', 'stackrecipes-cs' ),
			'menu_name'             => _x( 'Case Studies', 'admin menu', 'stackrecipes-cs' ),
			'add_new_item'          => __( 'Add New Teardown', 'stackrecipes-cs' ),
			'edit_item'             => __( 'Edit Teardown', 'stackrecipes-cs' ),
			'new_item'              => __( 'New Teardown', 'stackrecipes-cs' ),
			'view_item'             => __( 'View Teardown', 'stackrecipes-cs' ),
			'search_items'          => __( 'Search Teardowns', 'stackrecipes-cs' ),
			'not_found'             => __( 'No teardowns found.', 'stackrecipes-cs' ),
			'not_found_in_trash'    => __( 'No teardowns in the trash.', 'stackrecipes-cs' ),
			'all_items'             => __( 'All Teardowns', 'stackrecipes-cs' ),
			'archives'              => __( 'Case Studies Archive', 'stackrecipes-cs' ),
			'featured_image'        => __( 'Cover image', 'stackrecipes-cs' ),
			'set_featured_image'    => __( 'Set cover image', 'stackrecipes-cs' ),
			'remove_featured_image' => __( 'Remove cover image', 'stackrecipes-cs' ),
		);

		register_post_type(
			SRCS_POST_TYPE,
			array(
				'labels'             => $labels,
				'description'        => __( 'Architecture teardowns: honest, technical migration blueprints.', 'stackrecipes-cs' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_position'      => 21,
				'menu_icon'          => 'dashicons-analytics',
				'has_archive'        => SRCS_SLUG,
				'hierarchical'       => false,
				'rewrite'            => array(
					'slug'       => SRCS_SLUG,
					'with_front' => false,
					'feeds'      => true,
					'pages'      => true,
				),
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'custom-fields' ),
			)
		);
	}

	/**
	 * A light taxonomy so teardowns can be filtered by topic (POS, VAT, hardware...).
	 *
	 * @return void
	 */
	public static function register_taxonomy() {
		register_taxonomy(
			'case_study_topic',
			SRCS_POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Topics', 'stackrecipes-cs' ),
					'singular_name' => __( 'Topic', 'stackrecipes-cs' ),
					'add_new_item'  => __( 'Add Topic', 'stackrecipes-cs' ),
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => SRCS_SLUG . '/topic',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Flush rewrites once after an update changes the routing shape.
	 *
	 * Activation already flushes; this covers plugin updates deployed by file copy,
	 * where no activation hook runs.
	 *
	 * @return void
	 */
	public static function maybe_flush() {
		if ( get_option( 'srcs_rewrite_version' ) === SRCS_VERSION ) {
			return;
		}

		flush_rewrite_rules( false );
		update_option( 'srcs_rewrite_version', SRCS_VERSION, false );
	}

	/**
	 * Keep permalinks trailing-slashed to match the rest of the site.
	 *
	 * @param string  $link Permalink.
	 * @param WP_Post $post Post object.
	 * @return string
	 */
	public static function ensure_trailing_slash( $link, $post ) {
		if ( SRCS_POST_TYPE !== $post->post_type ) {
			return $link;
		}

		return user_trailingslashit( trailingslashit( $link ) );
	}
}
