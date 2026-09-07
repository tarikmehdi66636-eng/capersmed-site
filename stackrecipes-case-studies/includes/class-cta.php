<?php
/**
 * The /retail/ conversion callout.
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the CTA banner, appends it to teardowns, and exposes [case_study_cta].
 */
class SRCS_CTA {

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
	 * Build the banner markup.
	 *
	 * @param array $args Overrides for defaults().
	 * @return string
	 */
	public static function render( array $args = array() ) {
		$a = wp_parse_args( $args, self::defaults() );

		ob_start();
		srcs_get_part( 'cta-retail.php', array( 'cta' => $a ) );

		return (string) ob_get_clean();
	}

	/**
	 * [case_study_cta heading="..." body="..." primary_url="/retail/"]
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = shortcode_atts( self::defaults(), (array) $atts, 'case_study_cta' );

		return self::render( $atts );
	}
}
