<?php
/**
 * Single course detail page.
 *
 * Override by copying to your theme as single-wci_course.php.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id      = get_the_ID();
	$summary      = WCI_Fields::get( $post_id, 'summary' );
	$content      = WCI_Fields::get( $post_id, 'content' );
	$requirements = WCI_Fields::get( $post_id, 'requirements' );
	$format       = WCI_Fields::get( $post_id, 'format' );
	$outcome      = WCI_Fields::get( $post_id, 'outcome' );
	$register_url = WCI_Fields::get( $post_id, 'register_url' );
	$instructors  = WCI_Fields::get_people( $post_id, 'instructors' );
	$assistants   = WCI_Fields::get_people( $post_id, 'assistants' );

	// Fall back to the native editor content if the meta field is empty.
	if ( '' === $content ) {
		$content = apply_filters( 'the_content', get_the_content() );
	} else {
		$content = wpautop( $content );
	}
	?>
	<article id="course-<?php echo esc_attr( $post_id ); ?>" <?php post_class( 'wci-single' ); ?>>

		<header class="wci-hero">
			<div class="wci-hero__inner">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="wci-hero__media">
						<?php the_post_thumbnail( 'large', array( 'class' => 'wci-hero__img' ) ); ?>
					</div>
				<?php endif; ?>

				<div class="wci-hero__content">
					<h1 class="wci-hero__title"><?php the_title(); ?></h1>

					<?php if ( $summary ) : ?>
						<p class="wci-hero__summary"><?php echo esc_html( $summary ); ?></p>
					<?php endif; ?>

					<?php if ( $register_url ) : ?>
						<p class="wci-hero__actions">
							<a class="wci-btn wci-btn--primary" href="<?php echo esc_url( $register_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Đăng ký ngay', 'wp-course-intro' ); ?>
							</a>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<div class="wci-single__layout">
			<div class="wci-single__main">

				<?php if ( $content ) : ?>
					<section class="wci-section wci-section--content" aria-labelledby="wci-content-title">
						<h2 id="wci-content-title" class="wci-section__title"><?php esc_html_e( 'Nội dung khóa học', 'wp-course-intro' ); ?></h2>
						<div class="wci-section__body wci-prose"><?php echo wp_kses_post( $content ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $requirements ) : ?>
					<section class="wci-section wci-section--requirements" aria-labelledby="wci-req-title">
						<h2 id="wci-req-title" class="wci-section__title"><?php esc_html_e( 'Yêu cầu đầu vào', 'wp-course-intro' ); ?></h2>
						<div class="wci-section__body wci-prose"><?php echo wp_kses_post( wpautop( $requirements ) ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $outcome ) : ?>
					<section class="wci-section wci-section--outcome" aria-labelledby="wci-outcome-title">
						<h2 id="wci-outcome-title" class="wci-section__title"><?php esc_html_e( 'Cam kết đầu ra', 'wp-course-intro' ); ?></h2>
						<div class="wci-section__body wci-prose"><?php echo wp_kses_post( wpautop( $outcome ) ); ?></div>
					</section>
				<?php endif; ?>

				<?php if ( $instructors || $assistants ) : ?>
					<section class="wci-section wci-section--team" aria-labelledby="wci-team-title">
						<h2 id="wci-team-title" class="wci-section__title"><?php esc_html_e( 'Đội ngũ giảng dạy', 'wp-course-intro' ); ?></h2>
						<div class="wci-section__body wci-team">
							<?php
							WCI_Template::get_part(
								'people-list',
								array(
									'people'   => $instructors,
									'title'    => __( 'Giảng viên', 'wp-course-intro' ),
									'modifier' => 'instructors',
								)
							);

							WCI_Template::get_part(
								'people-list',
								array(
									'people'   => $assistants,
									'title'    => __( 'Trợ giảng', 'wp-course-intro' ),
									'modifier' => 'assistants',
								)
							);
							?>
						</div>
					</section>
				<?php endif; ?>

			</div>

			<aside class="wci-single__aside">
				<div class="wci-summary-card">
					<h2 class="wci-summary-card__title"><?php esc_html_e( 'Thông tin nhanh', 'wp-course-intro' ); ?></h2>
					<dl class="wci-summary-card__list">
						<?php if ( $format ) : ?>
							<div class="wci-summary-card__row">
								<dt><?php esc_html_e( 'Hình thức học', 'wp-course-intro' ); ?></dt>
								<dd><?php echo nl2br( esc_html( $format ) ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( $instructors ) : ?>
							<div class="wci-summary-card__row">
								<dt><?php esc_html_e( 'Giảng viên', 'wp-course-intro' ); ?></dt>
								<dd><?php echo esc_html( implode( ', ', wp_list_pluck( $instructors, 'name' ) ) ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( $assistants ) : ?>
							<div class="wci-summary-card__row">
								<dt><?php esc_html_e( 'Trợ giảng', 'wp-course-intro' ); ?></dt>
								<dd><?php echo esc_html( implode( ', ', wp_list_pluck( $assistants, 'name' ) ) ); ?></dd>
							</div>
						<?php endif; ?>
					</dl>

					<?php if ( $register_url ) : ?>
						<a class="wci-btn wci-btn--primary wci-btn--block" href="<?php echo esc_url( $register_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Đăng ký khóa học', 'wp-course-intro' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</aside>
		</div>

		<?php WCI_Template::render_related( $post_id, 12 ); ?>

	</article>
	<?php

endwhile;

get_footer();
