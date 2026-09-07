<?php
/**
 * Shared helpers.
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locate a template, letting the active theme override the plugin copy.
 *
 * A theme can override any file by dropping it in:
 *   wp-content/themes/<theme>/stackrecipes/case-studies/<name>
 *
 * @param string $name Template file name, relative to templates/.
 * @return string Absolute path to the template that should be loaded.
 */
function srcs_locate_template( $name ) {
	$theme = locate_template( array( 'stackrecipes/case-studies/' . $name ) );

	return $theme ? $theme : SRCS_PATH . 'templates/' . $name;
}

/**
 * Render a template part with the theme-override lookup applied.
 *
 * @param string $name Part file name, relative to templates/parts/.
 * @param array  $args Variables extracted into the part's scope.
 * @return void
 */
function srcs_get_part( $name, array $args = array() ) {
	$file = srcs_locate_template( 'parts/' . $name );

	if ( ! file_exists( $file ) ) {
		return;
	}

	// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Deliberate, scoped to the part.
	extract( $args, EXTR_SKIP );

	include $file;
}

/**
 * Read one of the "at a glance" meta values for a case study.
 *
 * @param string   $key     Meta key without the _srcs_ prefix.
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return string
 */
function srcs_meta( $key, $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return '';
	}

	return (string) get_post_meta( $post_id, '_srcs_' . $key, true );
}

/**
 * The section's landing URL, honouring the SRCS_SLUG constant.
 *
 * @return string
 */
function srcs_archive_url() {
	$link = get_post_type_archive_link( SRCS_POST_TYPE );

	return $link ? $link : home_url( '/' . SRCS_SLUG . '/' );
}

/**
 * Rough read time, used in the single-post meta strip.
 *
 * @param int|null $post_id Post ID. Defaults to the current post.
 * @return int Minutes, never below 1.
 */
function srcs_read_time( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	$override = (int) srcs_meta( 'read_time', $post_id );
	if ( $override > 0 ) {
		return $override;
	}

	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );

	return max( 1, (int) ceil( $words / 220 ) );
}
