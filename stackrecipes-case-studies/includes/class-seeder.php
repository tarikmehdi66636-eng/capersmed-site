<?php
/**
 * Publishes the shipped teardowns on activation.
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

/**
 * Idempotent content seeder: never overwrites an existing post at the same slug.
 */
class SRCS_Seeder {

	/**
	 * Teardowns shipped with the plugin.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function entries() {
		return array(
			array(
				'slug'    => 'independent-wine-merchant-lightspeed-migration',
				'title'   => 'Case Breakdown: Migrating a Specialty Wine & Spirits Merchant Off Lightspeed',
				'file'    => 'independent-wine-merchant-lightspeed-migration.html',
				'excerpt' => 'A line-by-line teardown of moving a two-register, 1,800-SKU wine and spirits shop off Lightspeed: what hardware we kept, how mixed 20% / zero-rated VAT prints on one receipt without cashier input, the 48-hour cutover, and the three-year cost comparison against £6,048 of SaaS rent.',
				'topics'  => array( 'Point of Sale', 'Retail', 'UK VAT' ),
				'meta'    => array(
					'vertical'  => 'Independent wine & spirits retail',
					'migrating' => 'Lightspeed Retail (X-Series), 2 registers',
					'stack'     => 'Self-hosted POS + Stripe Terminal',
					'baseline'  => '£168/month recurring SaaS',
					'outcome'   => '≈ £5,374 retained over 3 years',
					'timeline'  => '48-hour cutover',
				),
			),
		);
	}

	/**
	 * Insert any shipped teardown that is not already present.
	 *
	 * @return array<int,int> IDs of posts created by this run.
	 */
	public static function seed() {
		$created = array();

		foreach ( self::entries() as $entry ) {
			$existing = get_page_by_path( $entry['slug'], OBJECT, SRCS_POST_TYPE );

			if ( $existing ) {
				continue;
			}

			$file = SRCS_PATH . 'content/' . $entry['file'];

			if ( ! is_readable( $file ) ) {
				continue;
			}

			$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local plugin asset.

			if ( false === $content ) {
				continue;
			}

			// wp_insert_post() expects slashed data and unslashes it internally.
			// Without this, a backslash in a teardown body is silently eaten.
			$post_id = wp_insert_post(
				wp_slash(
					array(
						'post_type'    => SRCS_POST_TYPE,
						'post_status'  => 'publish',
						'post_title'   => $entry['title'],
						'post_name'    => $entry['slug'],
						'post_excerpt' => $entry['excerpt'],
						'post_content' => $content,
					)
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			foreach ( $entry['meta'] as $key => $value ) {
				update_post_meta( $post_id, '_srcs_' . $key, $value );
			}

			if ( ! empty( $entry['topics'] ) ) {
				wp_set_object_terms( $post_id, $entry['topics'], 'case_study_topic' );
			}

			$created[] = $post_id;
		}

		return $created;
	}
}
