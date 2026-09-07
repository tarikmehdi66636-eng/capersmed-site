<?php
/**
 * Template routing, assets and document head output.
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

/**
 * Points /case-studies/ at the plugin templates and enqueues the section CSS.
 */
class SRCS_Templates {

	/**
	 * Hook registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'template_include', array( __CLASS__, 'route' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_head', array( __CLASS__, 'schema' ) );
		add_filter( 'excerpt_length', array( __CLASS__, 'excerpt_length' ), 20 );
		add_action( 'pre_get_posts', array( __CLASS__, 'archive_query' ) );
	}

	/**
	 * Whether the current request belongs to the case studies section.
	 *
	 * @return bool
	 */
	public static function is_section() {
		return is_singular( SRCS_POST_TYPE )
			|| is_post_type_archive( SRCS_POST_TYPE )
			|| is_tax( 'case_study_topic' );
	}

	/**
	 * Swap in the plugin template when the theme has none of its own.
	 *
	 * A theme file (single-case_study.php / archive-case_study.php) always wins;
	 * WordPress will have resolved it before this filter runs.
	 *
	 * @param string $template Template chosen by WordPress.
	 * @return string
	 */
	public static function route( $template ) {
		$theme_dirs = array( get_stylesheet_directory(), get_template_directory() );

		// Respect a template the theme itself provides.
		foreach ( $theme_dirs as $dir ) {
			if ( $template && 0 === strpos( wp_normalize_path( $template ), wp_normalize_path( $dir ) ) ) {
				$basename = basename( $template );

				if ( in_array( $basename, array( 'single-' . SRCS_POST_TYPE . '.php', 'archive-' . SRCS_POST_TYPE . '.php' ), true ) ) {
					return $template;
				}
			}
		}

		if ( is_singular( SRCS_POST_TYPE ) ) {
			return srcs_locate_template( 'single-case_study.php' );
		}

		if ( is_post_type_archive( SRCS_POST_TYPE ) || is_tax( 'case_study_topic' ) ) {
			return srcs_locate_template( 'archive-case_study.php' );
		}

		return $template;
	}

	/**
	 * Section stylesheet. Loaded only where it is used.
	 *
	 * @return void
	 */
	public static function assets() {
		if ( ! self::is_section() ) {
			return;
		}

		wp_enqueue_style(
			'srcs-case-studies',
			SRCS_URL . 'assets/css/case-studies.css',
			array(),
			SRCS_VERSION
		);
	}

	/**
	 * Newest first, and show every teardown before paginating.
	 *
	 * @param WP_Query $query Query being prepared.
	 * @return void
	 */
	public static function archive_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_post_type_archive( SRCS_POST_TYPE ) && ! $query->is_tax( 'case_study_topic' ) ) {
			return;
		}

		$query->set( 'posts_per_page', 12 );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}

	/**
	 * Longer excerpts read better on the teardown cards.
	 *
	 * @param int $length Default word count.
	 * @return int
	 */
	public static function excerpt_length( $length ) {
		return self::is_section() ? 42 : $length;
	}

	/**
	 * Article schema for single teardowns.
	 *
	 * @return void
	 */
	public static function schema() {
		if ( ! is_singular( SRCS_POST_TYPE ) ) {
			return;
		}

		$post_id = get_the_ID();

		$data = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'TechArticle',
			'headline'         => wp_strip_all_tags( get_the_title( $post_id ) ),
			'description'      => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
			'datePublished'    => get_the_date( DATE_W3C, $post_id ),
			'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
			'mainEntityOfPage' => get_permalink( $post_id ),
			'author'           => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		if ( has_post_thumbnail( $post_id ) ) {
			$data['image'] = get_the_post_thumbnail_url( $post_id, 'full' );
		}

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}
}
