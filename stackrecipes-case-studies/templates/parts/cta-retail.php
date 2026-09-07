<?php
/**
 * Conversion banner pointing at /retail/.
 *
 * Override by copying to: your-theme/stackrecipes/case-studies/parts/cta-retail.php
 *
 * @package StackRecipes\CaseStudies
 *
 * @var array $cta Heading, body and button copy/URLs.
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $cta ) || ! is_array( $cta ) ) {
	return;
}
?>

<aside class="srcs-cta" role="complementary" aria-labelledby="srcs-cta-heading">
	<div class="srcs-cta__text">
		<h2 class="srcs-cta__heading" id="srcs-cta-heading"><?php echo esc_html( $cta['heading'] ); ?></h2>
		<p class="srcs-cta__body"><?php echo esc_html( $cta['body'] ); ?></p>
	</div>

	<div class="srcs-cta__actions">
		<?php if ( ! empty( $cta['primary_url'] ) && ! empty( $cta['primary_label'] ) ) : ?>
			<a class="srcs-btn srcs-btn--primary" href="<?php echo esc_url( $cta['primary_url'] ); ?>">
				<?php echo esc_html( $cta['primary_label'] ); ?>
			</a>
		<?php endif; ?>

		<?php if ( ! empty( $cta['secondary_url'] ) && ! empty( $cta['secondary_label'] ) ) : ?>
			<a class="srcs-btn srcs-btn--ghost" href="<?php echo esc_url( $cta['secondary_url'] ); ?>">
				<?php echo esc_html( $cta['secondary_label'] ); ?>
			</a>
		<?php endif; ?>
	</div>
</aside>
