<?php
/**
 * Course card used in grids and carousels.
 *
 * @var int $post_id Course post ID.
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $post_id ) ? absint( $post_id ) : get_the_ID();

if ( ! $post_id ) {
	return;
}

$permalink = get_permalink( $post_id );
$summary   = WCI_Fields::get( $post_id, 'summary' );

if ( '' === $summary ) {
	$summary = wp_strip_all_tags( get_the_excerpt( $post_id ) );
}

$summary = wp_trim_words( $summary, 22, '…' );
$format  = WCI_Fields::get( $post_id, 'format' );
?>
<article class="wci-card">
	<a class="wci-card__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'loading' => 'lazy', 'class' => 'wci-card__img' ) );
		} else {
			echo '<span class="wci-card__img wci-card__img--placeholder" aria-hidden="true"></span>';
		}
		?>
	</a>
	<div class="wci-card__body">
		<h3 class="wci-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
		</h3>

		<?php if ( $format ) : ?>
			<p class="wci-card__meta"><?php echo esc_html( wp_trim_words( $format, 10, '…' ) ); ?></p>
		<?php endif; ?>

		<?php if ( $summary ) : ?>
			<p class="wci-card__excerpt"><?php echo esc_html( $summary ); ?></p>
		<?php endif; ?>

		<span class="wci-card__cta"><?php esc_html_e( 'Xem chi tiết', 'wp-course-intro' ); ?> &rarr;</span>
	</div>
</article>
