<?php
/**
 * The /case-studies/ landing page.
 *
 * Override by copying to: your-theme/stackrecipes/case-studies/archive-case_study.php
 *
 * @package StackRecipes\CaseStudies
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="srcs srcs-archive">

	<header class="srcs-archive__intro">
		<p class="srcs-eyebrow"><?php esc_html_e( 'Architecture teardowns', 'stackrecipes-cs' ); ?></p>

		<h1 class="srcs-archive__title">
			<?php
			if ( is_tax( 'case_study_topic' ) ) {
				single_term_title();
			} else {
				post_type_archive_title();
			}
			?>
		</h1>

		<p class="srcs-archive__standfirst">
			<?php
			esc_html_e(
				'Real migrations, costed line by line. Each teardown covers the hardware we kept, the hardware we replaced, the tax and catalogue work nobody quotes for, the cutover timeline, and the money it moved.',
				'stackrecipes-cs'
			);
			?>
		</p>

		<?php
		$srcs_terms = get_terms(
			array(
				'taxonomy'   => 'case_study_topic',
				'hide_empty' => true,
			)
		);

		if ( ! is_wp_error( $srcs_terms ) && ! empty( $srcs_terms ) ) :
			?>
			<nav class="srcs-topics" aria-label="<?php esc_attr_e( 'Filter teardowns by topic', 'stackrecipes-cs' ); ?>">
				<a class="srcs-topics__link<?php echo is_post_type_archive( SRCS_POST_TYPE ) ? ' is-current' : ''; ?>" href="<?php echo esc_url( srcs_archive_url() ); ?>">
					<?php esc_html_e( 'All', 'stackrecipes-cs' ); ?>
				</a>
				<?php foreach ( $srcs_terms as $srcs_term ) : ?>
					<a class="srcs-topics__link<?php echo is_tax( 'case_study_topic', $srcs_term->term_id ) ? ' is-current' : ''; ?>"
						href="<?php echo esc_url( get_term_link( $srcs_term ) ); ?>">
						<?php echo esc_html( $srcs_term->name ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>

		<div class="srcs-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'srcs-card' ); ?>>

					<?php if ( has_post_thumbnail() ) : ?>
						<a class="srcs-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
						</a>
					<?php endif; ?>

					<div class="srcs-card__body">
						<?php if ( srcs_meta( 'vertical' ) ) : ?>
							<p class="srcs-eyebrow"><?php echo esc_html( srcs_meta( 'vertical' ) ); ?></p>
						<?php endif; ?>

						<h2 class="srcs-card__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>

						<p class="srcs-card__excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>

						<?php
						$srcs_stats = array_filter(
							array(
								__( 'Baseline', 'stackrecipes-cs' ) => srcs_meta( 'baseline' ),
								__( 'Outcome', 'stackrecipes-cs' )  => srcs_meta( 'outcome' ),
								__( 'Cutover', 'stackrecipes-cs' )  => srcs_meta( 'timeline' ),
							)
						);

						if ( $srcs_stats ) :
							?>
							<dl class="srcs-card__stats">
								<?php foreach ( $srcs_stats as $srcs_label => $srcs_value ) : ?>
									<div class="srcs-card__stat">
										<dt><?php echo esc_html( $srcs_label ); ?></dt>
										<dd><?php echo esc_html( $srcs_value ); ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
						<?php endif; ?>

						<p class="srcs-card__more">
							<a href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'Read the teardown', 'stackrecipes-cs' ); ?>
								<span aria-hidden="true">&rarr;</span>
								<span class="screen-reader-text"><?php the_title(); ?></span>
							</a>
						</p>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'class'     => 'srcs-pagination',
				'mid_size'  => 2,
				'prev_text' => __( '&larr; Newer', 'stackrecipes-cs' ),
				'next_text' => __( 'Older &rarr;', 'stackrecipes-cs' ),
			)
		);
		?>

	<?php else : ?>

		<p class="srcs-empty"><?php esc_html_e( 'No teardowns published yet.', 'stackrecipes-cs' ); ?></p>

	<?php endif; ?>

	<?php echo SRCS_CTA::render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the template part. ?>

</main>

<?php
get_footer();
