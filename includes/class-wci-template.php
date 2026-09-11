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
		// Safety net: a shortcode rendered by a page builder / widget calls
		// wp_enqueue_style() while the content prints — after wp_head()
		// already flushed <head>, too late for a normal style to go out.
		// Catch that here and print it in the footer instead.
		add_action( 'wp_footer', array( $this, 'print_late_styles' ), 1 );
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

		if ( $this->page_needs_assets() ) {
			wp_enqueue_style( 'wci-frontend' );
			wp_enqueue_script( 'wci-carousel' );
		}
	}

	/**
	 * Whether the current request is expected to show a course card/carousel,
	 * so the CSS/JS can be enqueued early (in <head>) instead of relying on
	 * the shortcode's own, too-late wp_enqueue_style() call.
	 *
	 * @return bool
	 */
	protected function page_needs_assets() {
		if ( is_singular( WCI_POST_TYPE ) || is_post_type_archive( WCI_POST_TYPE ) ) {
			return true;
		}

		if ( is_singular() ) {
			$post = get_post();

			if ( $post && ( has_shortcode( $post->post_content, 'wci_courses' )
				|| has_shortcode( $post->post_content, 'wci_course_carousel' ) ) ) {
				return true;
			}
		}

		return (bool) apply_filters( 'wci_page_needs_assets', false );
	}

	/**
	 * Print wci-frontend in the footer if something (a widget, a page
	 * builder shortcode render, ACF, etc.) enqueued it after <head> already
	 * printed its styles. No-op if it was already output normally.
	 */
	public function print_late_styles() {
		if ( wp_style_is( 'wci-frontend', 'enqueued' ) && ! wp_style_is( 'wci-frontend', 'done' ) ) {
			wp_print_styles( array( 'wci-frontend' ) );
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
	 * Carousel display defaults (filterable site-wide).
	 *
	 * per_view: cards visible per slide on desktop (1-6, stepped down on smaller screens).
	 * interval: ms between automatic slides (min 1500, 0 disables autoplay).
	 *
	 * @return array<string,int>
	 */
	public static function carousel_defaults() {
		return apply_filters(
			'wci_carousel_defaults',
			array(
				'per_view' => 4,
				'interval' => 5000,
			)
		);
	}

	/**
	 * Normalise carousel args.
	 *
	 * @param array $args Raw args.
	 * @return array{per_view:int,interval:int,autoplay:bool}
	 */
	public static function carousel_args( array $args = array() ) {
		$args = wp_parse_args( $args, self::carousel_defaults() );
		$args = wp_parse_args(
			$args,
			array(
				'per_view' => 4,
				'interval' => 5000,
			)
		);

		$per_view = max( 1, min( 6, absint( $args['per_view'] ) ) );
		$interval = absint( $args['interval'] );

		if ( $interval > 0 && $interval < 1500 ) {
			$interval = 1500;
		}

		return array(
			'per_view' => $per_view,
			'interval' => $interval,
			'autoplay' => $interval > 0,
		);
	}

	/**
	 * Open the carousel wrapper markup and return the query used.
	 *
	 * @param WP_Query $query Posts to show.
	 * @param array    $args  Normalised carousel args.
	 */
	public static function carousel_markup( WP_Query $query, array $args ) {
		?>
		<div
			class="wci-carousel"
			data-wci-carousel
			data-autoplay="<?php echo $args['autoplay'] ? 'true' : 'false'; ?>"
			data-per-view="<?php echo esc_attr( $args['per_view'] ); ?>"
			data-interval="<?php echo esc_attr( $args['interval'] ); ?>"
			style="--wci-per-view-config: <?php echo esc_attr( $args['per_view'] ); ?>;"
		>
			<div class="wci-carousel__track">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					self::get_part( 'course-card', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the "other courses" carousel at the end of a single course page.
	 *
	 * @param int   $current_id Course to exclude.
	 * @param int   $limit      Max courses to pull.
	 * @param array $args       Optional { per_view, interval }.
	 */
	public static function render_related( $current_id = 0, $limit = 12, array $args = array() ) {
		$current_id = $current_id ? absint( $current_id ) : get_the_ID();
		$settings   = self::carousel_args( $args );

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

			<?php self::carousel_markup( $query, $settings ); ?>
		</section>
		<?php
		wp_reset_postdata();
	}
}
