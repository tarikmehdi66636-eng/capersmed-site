<?php
/**
 * A single architecture teardown.
 *
 * Override by copying to: your-theme/stackrecipes/case-studies/single-case_study.php
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>

	<main id="primary" class="srcs srcs-single">

		<nav class="srcs-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'stackrecipes-cs' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'stackrecipes-cs' ); ?></a>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( srcs_archive_url() ); ?>"><?php esc_html_e( 'Case Studies', 'stackrecipes-cs' ); ?></a>
		</nav>

		<article <?php post_class( 'srcs-teardown' ); ?>>

			<header class="srcs-teardown__header">
				<?php if ( srcs_meta( 'vertical' ) ) : ?>
					<p class="srcs-eyebrow"><?php echo esc_html( srcs_meta( 'vertical' ) ); ?></p>
				<?php endif; ?>

				<h1 class="srcs-teardown__title"><?php the_title(); ?></h1>

				<?php if ( has_excerpt() ) : ?>
					<p class="srcs-teardown__standfirst"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
				<?php endif; ?>

				<p class="srcs-teardown__byline">
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<span aria-hidden="true">&middot;</span>
					<?php
					printf(
						/* translators: %d: estimated reading time in minutes. */
						esc_html__( '%d min read', 'stackrecipes-cs' ),
						(int) srcs_read_time()
					);
					?>
				</p>
			</header>

			<?php
			$srcs_glance = array_filter(
				array(
					__( 'Migrating from', 'stackrecipes-cs' ) => srcs_meta( 'migrating' ),
					__( 'Migrating to', 'stackrecipes-cs' )   => srcs_meta( 'stack' ),
					__( 'Baseline cost', 'stackrecipes-cs' )  => srcs_meta( 'baseline' ),
					__( 'Cutover window', 'stackrecipes-cs' ) => srcs_meta( 'timeline' ),
					__( 'Outcome', 'stackrecipes-cs' )        => srcs_meta( 'outcome' ),
				)
			);

			if ( $srcs_glance ) :
				?>
				<aside class="srcs-glance" aria-label="<?php esc_attr_e( 'Migration at a glance', 'stackrecipes-cs' ); ?>">
					<h2 class="srcs-glance__title"><?php esc_html_e( 'At a glance', 'stackrecipes-cs' ); ?></h2>
					<dl class="srcs-glance__list">
						<?php foreach ( $srcs_glance as $srcs_label => $srcs_value ) : ?>
							<div class="srcs-glance__row">
								<dt><?php echo esc_html( $srcs_label ); ?></dt>
								<dd><?php echo esc_html( $srcs_value ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</aside>
			<?php endif; ?>

			<div class="srcs-prose">
				<?php the_content(); ?>
			</div>

			<?php
			$srcs_topics = get_the_term_list( get_the_ID(), 'case_study_topic', '', ', ' );

			if ( $srcs_topics && ! is_wp_error( $srcs_topics ) ) :
				?>
				<p class="srcs-teardown__topics">
					<span><?php esc_html_e( 'Topics:', 'stackrecipes-cs' ); ?></span>
					<?php echo wp_kses_post( $srcs_topics ); ?>
				</p>
			<?php endif; ?>

		</article>

		<?php echo SRCS_CTA::render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template part. ?>

		<?php
		$srcs_related = new WP_Query(
			array(
				'post_type'           => SRCS_POST_TYPE,
				'posts_per_page'      => 3,
				'post__not_in'        => array( get_the_ID() ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		if ( $srcs_related->have_posts() ) :
			?>
			<section class="srcs-related" aria-label="<?php esc_attr_e( 'More teardowns', 'stackrecipes-cs' ); ?>">
				<h2 class="srcs-related__title"><?php esc_html_e( 'More teardowns', 'stackrecipes-cs' ); ?></h2>
				<ul class="srcs-related__list">
					<?php
					while ( $srcs_related->have_posts() ) :
						$srcs_related->the_post();
						?>
						<li>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<?php if ( srcs_meta( 'outcome' ) ) : ?>
								<span class="srcs-related__outcome"><?php echo esc_html( srcs_meta( 'outcome' ) ); ?></span>
							<?php endif; ?>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</section>
		<?php endif; ?>

	</main>

	<?php
endwhile;

get_footer();
