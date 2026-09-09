<?php
/**
 * Course archive listing.
 *
 * Override by copying to your theme as archive-wci_course.php.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="wci-archive">
	<header class="wci-archive__head">
		<h1 class="wci-archive__title"><?php post_type_archive_title(); ?></h1>
		<?php
		$pt_obj      = get_post_type_object( WCI_POST_TYPE );
		$description = $pt_obj && ! empty( $pt_obj->description ) ? $pt_obj->description : '';
		if ( $description ) :
			?>
			<p class="wci-archive__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="wci-grid wci-grid--cols-3">
			<?php
			while ( have_posts() ) :
				the_post();
				WCI_Template::get_part( 'course-card', array( 'post_id' => get_the_ID() ) );
			endwhile;
			?>
		</div>

		<div class="wci-archive__pagination">
			<?php
			the_posts_pagination(
				array(
					'mid_size'  => 1,
					'prev_text' => __( '&laquo; Trước', 'wp-course-intro' ),
					'next_text' => __( 'Sau &raquo;', 'wp-course-intro' ),
				)
			);
			?>
		</div>
	<?php else : ?>
		<p class="wci-archive__empty"><?php esc_html_e( 'Chưa có khóa học nào.', 'wp-course-intro' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
