<?php
/**
 * The /retail/ conversion callout.
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the CTA banner and exposes [case_study_cta].
 */
class SRCS_CTA {

	/**
	 * Friendlier attribute names accepted by the shortcode, mapped to the
	 * canonical keys. shortcode_atts() drops unknown attributes silently, so
	 * without this a [case_study_cta title="..."] call renders the defaults
	 * and looks like it worked.
	 *
	 * @var array<string,string>
	 */
	const ALIASES = array(
		'title'        => 'heading',
		'subtitle'     => 'body',
		'text'         => 'body',
		'button_label' => 'primary_label',
		'button_url'   => 'primary_url',
		'cta_label'    => 'primary_label',
		'cta_url'      => 'primary_url',
	);

	/**
	 * Hook registration.
	 *
	 * @return void
	 */
	public static function init() {
		add_shortcode( 'case_study_cta', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Default copy and links, filterable so the wording can be tuned site-wide.
	 *
	 * @return array<string,string>
	 */
	public static function defaults() {
		return apply_filters(
			'srcs_cta_defaults',
			array(
				'heading'         => __( 'Running a similar store layout?', 'stackrecipes-cs' ),
				'body'            => __( 'Check your hardware compatibility or request a turnkey migration.', 'stackrecipes-cs' ),
				'primary_label'   => __( 'Check hardware compatibility', 'stackrecipes-cs' ),
				'primary_url'     => home_url( '/retail/' ),
				'secondary_label' => __( 'Request a turnkey migration', 'stackrecipes-cs' ),
				'secondary_url'   => home_url( '/retail/' ),
			)
		);
	}

	/**
	 * Translate alias keys to canonical ones, keeping explicit canonical values.
	 *
	 * @param array $atts Raw attributes.
	 * @return array
	 */
	protected static function normalize( array $atts ) {
		foreach ( self::ALIASES as $alias => $canonical ) {
			if ( isset( $atts[ $alias ] ) && ! isset( $atts[ $canonical ] ) ) {
				$atts[ $canonical ] = $atts[ $alias ];
			}

			unset( $atts[ $alias ] );
		}

		return $atts;
	}

	/**
	 * Build the banner markup.
	 *
	 * @param array $args Overrides for defaults().
	 * @return string
	 */
	public static function render( array $args = array() ) {
		$args = self::normalize( $args );
		$cta  = wp_parse_args( array_filter( $args, 'strlen' ), self::defaults() );

		/*
		 * A caller that names its own single button means one button. Without
		 * this the default second button tags along and both point at /retail/.
		 */
		$wants_one_button = ( isset( $args['primary_label'] ) || isset( $args['primary_url'] ) )
			&& ! isset( $args['secondary_label'] )
			&& ! isset( $args['secondary_url'] );

		if ( $wants_one_button ) {
			$cta['secondary_label'] = '';
			$cta['secondary_url']   = '';
		}

		ob_start();
		srcs_get_part( 'cta-retail.php', array( 'cta' => $cta ) );

		return (string) ob_get_clean();
	}

	/**
	 * The banner for one case study, with its per-post copy applied.
	 *
	 * Lets a teardown carry its own CTA without embedding a shortcode in the
	 * post body, where wpautop mangles multi-line attributes and the banner
	 * would render twice alongside the one in the template.
	 *
	 * @param int|null $post_id Post ID. Defaults to the current post.
	 * @return string
	 */
	public static function for_post( $post_id = null ) {
		$post_id = $post_id ? (int) $post_id : get_the_ID();

		$overrides = array(
			'heading'       => srcs_meta( 'cta_heading', $post_id ),
			'body'          => srcs_meta( 'cta_body', $post_id ),
			'primary_label' => srcs_meta( 'cta_label', $post_id ),
			'primary_url'   => srcs_meta( 'cta_url', $post_id ),
		);

		return self::render( array_filter( $overrides, 'strlen' ) );
	}

	/**
	 * [case_study_cta title="..." subtitle="..." button_url="/retail/" button_label="..."]
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		return self::render( (array) $atts );
	}
}
