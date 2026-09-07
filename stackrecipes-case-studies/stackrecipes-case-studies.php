<?php
/**
 * Plugin Name:       StackRecipes — Case Studies / Architecture Teardowns
 * Plugin URI:        https://stackrecipes.com/case-studies/
 * Description:       Adds the /case-studies/ section: a "case_study" post type, archive + single templates, an "at a glance" meta panel, the retail CTA callout, and the first published teardown.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            StackRecipes
 * License:           GPL-2.0-or-later
 * Text Domain:       stackrecipes-cs
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

define( 'SRCS_VERSION', '1.0.0' );
define( 'SRCS_FILE', __FILE__ );
define( 'SRCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SRCS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Post type key. Kept short because WordPress caps post type keys at 20 chars.
 */
define( 'SRCS_POST_TYPE', 'case_study' );

/**
 * URL segment for the section. Change this constant (and re-save permalinks)
 * to move the section to /blueprints/ instead.
 */
define( 'SRCS_SLUG', 'case-studies' );

require_once SRCS_PATH . 'includes/helpers.php';
require_once SRCS_PATH . 'includes/class-cpt.php';
require_once SRCS_PATH . 'includes/class-meta.php';
require_once SRCS_PATH . 'includes/class-templates.php';
require_once SRCS_PATH . 'includes/class-cta.php';
require_once SRCS_PATH . 'includes/class-seeder.php';

add_action(
	'plugins_loaded',
	static function () {
		SRCS_CPT::init();
		SRCS_Meta::init();
		SRCS_Templates::init();
		SRCS_CTA::init();
	}
);

/**
 * Activation: register rewrites, seed the first teardown, then flush.
 */
register_activation_hook(
	__FILE__,
	static function () {
		SRCS_CPT::register_post_type();
		SRCS_CPT::register_taxonomy();
		SRCS_Seeder::seed();
		flush_rewrite_rules();
	}
);

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
