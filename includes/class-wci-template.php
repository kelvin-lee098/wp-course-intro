<?php
/**
 * Front-end template handling + shared render helpers.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCI_Template
 */
class WCI_Template {

	/**
	 * Hook registration.
	 */
	public function hooks() {
		add_filter( 'single_template', array( $this, 'single_template' ) );
		add_filter( 'archive_template', array( $this, 'archive_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Use the plugin single template unless the theme overrides it.
	 *
	 * Theme override: copy templates/single-wci_course.php into your theme root.
	 *
	 * @param string $template Resolved template path.
	 * @return string
	 */
	public function single_template( $template ) {
		if ( ! is_singular( WCI_POST_TYPE ) ) {
			return $template;
		}

		$theme = locate_template( array( 'single-' . WCI_POST_TYPE . '.php' ) );

		if ( $theme ) {
			return $theme;
		}

		return WCI_PLUGIN_DIR . 'templates/single-course.php';
	}

	/**
	 * Use the plugin archive template unless the theme overrides it.
	 *
	 * @param string $template Resolved template path.
	 * @return string
	 */
	public function archive_template( $template ) {
		if ( ! is_post_type_archive( WCI_POST_TYPE ) ) {
			return $template;
		}

		$theme = locate_template( array( 'archive-' . WCI_POST_TYPE . '.php' ) );

		if ( $theme ) {
			return $theme;
		}

		return WCI_PLUGIN_DIR . 'templates/archive-course.php';
	}

	/**
	 * Register / enqueue front-end assets where a course is shown.
	 */
	public function assets() {
		wp_register_style(
			'wci-frontend',
			WCI_PLUGIN_URL . 'assets/css/wci-frontend.css',
			array(),
			WCI_VERSION
		);

		wp_register_script(
			'wci-carousel',
			WCI_PLUGIN_URL . 'assets/js/wci-carousel.js',
			array(),
			WCI_VERSION,
			true
		);

		if ( is_singular( WCI_POST_TYPE ) || is_post_type_archive( WCI_POST_TYPE ) ) {
			wp_enqueue_style( 'wci-frontend' );
			wp_enqueue_script( 'wci-carousel' );
		}
	}

	/**
	 * Locate a template part, allowing theme override in /wp-course-intro/.
	 *
	 * @param string $slug File name inside templates/parts (without .php).
	 * @param array  $args Variables exposed to the part.
	 */
	public static function get_part( $slug, array $args = array() ) {
		$file = 'wp-course-intro/' . $slug . '.php';
		$path = locate_template( $file );

		if ( ! $path ) {
			$path = WCI_PLUGIN_DIR . 'templates/parts/' . $slug . '.php';
		}

		if ( ! file_exists( $path ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $args, EXTR_SKIP );
		include $path;
	}

	/**
	 * Render the "other courses" carousel.
	 *
	 * @param int $current_id Course to exclude.
	 * @param int $limit      Max courses.
	 */
	public static function render_related( $current_id = 0, $limit = 12 ) {
		$current_id = $current_id ? absint( $current_id ) : get_the_ID();

		$query = new WP_Query(
			array(
				'post_type'           => WCI_POST_TYPE,
				'posts_per_page'      => absint( $limit ),
				'post__not_in'        => array( $current_id ),
				'orderby'             => 'rand',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		if ( ! $query->have_posts() ) {
			return;
		}
		?>
		<section class="wci-related" aria-labelledby="wci-related-title">
			<div class="wci-related__head">
				<h2 id="wci-related-title" class="wci-related__title"><?php esc_html_e( 'Các khóa học khác', 'wp-course-intro' ); ?></h2>
				<div class="wci-related__controls">
					<button type="button" class="wci-related__nav" data-dir="prev" aria-label="<?php esc_attr_e( 'Trước', 'wp-course-intro' ); ?>">&#8249;</button>
					<button type="button" class="wci-related__nav" data-dir="next" aria-label="<?php esc_attr_e( 'Sau', 'wp-course-intro' ); ?>">&#8250;</button>
				</div>
			</div>

			<div class="wci-carousel" data-wci-carousel data-autoplay="true">
				<div class="wci-carousel__track">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();
						self::get_part( 'course-card', array( 'post_id' => get_the_ID() ) );
					}
					?>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	}
}
