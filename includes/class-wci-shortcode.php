<?php
/**
 * Shortcodes for listing courses on any page.
 *
 * [wci_courses limit="12" columns="3" orderby="date" order="DESC"]
 * [wci_course_carousel limit="12"]
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCI_Shortcode
 */
class WCI_Shortcode {

	/**
	 * Hook registration.
	 */
	public function hooks() {
		add_shortcode( 'wci_courses', array( $this, 'grid' ) );
		add_shortcode( 'wci_course_carousel', array( $this, 'carousel' ) );
	}

	/**
	 * Shared query builder.
	 *
	 * @param array $atts Normalised attributes.
	 * @return WP_Query
	 */
	protected function query( $atts ) {
		$allowed_orderby = array( 'date', 'title', 'menu_order', 'rand', 'modified' );
		$orderby         = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'date';

		return new WP_Query(
			array(
				'post_type'           => WCI_POST_TYPE,
				'posts_per_page'      => absint( $atts['limit'] ),
				'orderby'             => $orderby,
				'order'               => 'ASC' === strtoupper( $atts['order'] ) ? 'ASC' : 'DESC',
				'post__not_in'        => array_filter( array_map( 'absint', explode( ',', (string) $atts['exclude'] ) ) ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
	}

	/**
	 * [wci_courses] grid.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function grid( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit'   => 12,
				'columns' => 3,
				'orderby' => 'date',
				'order'   => 'DESC',
				'exclude' => '',
			),
			$atts,
			'wci_courses'
		);

		$query = $this->query( $atts );

		if ( ! $query->have_posts() ) {
			return '';
		}

		wp_enqueue_style( 'wci-frontend' );

		$columns = max( 1, min( 4, absint( $atts['columns'] ) ) );

		ob_start();
		printf( '<div class="wci-grid wci-grid--cols-%d">', $columns );

		while ( $query->have_posts() ) {
			$query->the_post();
			WCI_Template::get_part( 'course-card', array( 'post_id' => get_the_ID() ) );
		}

		echo '</div>';
		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * [wci_course_carousel] running list.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function carousel( $atts ) {
		$defaults = WCI_Template::carousel_defaults();

		$atts = shortcode_atts(
			array(
				'limit'    => 12,
				'orderby'  => 'rand',
				'order'    => 'DESC',
				'exclude'  => '',
				'per_view' => $defaults['per_view'],
				'interval' => $defaults['interval'],
				'arrows'   => 'yes',
			),
			$atts,
			'wci_course_carousel'
		);

		$query = $this->query( $atts );

		if ( ! $query->have_posts() ) {
			return '';
		}

		wp_enqueue_style( 'wci-frontend' );
		wp_enqueue_script( 'wci-carousel' );

		$settings = WCI_Template::carousel_args(
			array(
				'per_view' => $atts['per_view'],
				'interval' => $atts['interval'],
			)
		);

		$show_arrows = in_array( strtolower( (string) $atts['arrows'] ), array( 'yes', 'true', '1' ), true );

		ob_start();
		?>
		<div class="wci-carousel-wrap">
			<?php if ( $show_arrows ) : ?>
				<div class="wci-carousel__controls">
					<button type="button" class="wci-carousel__nav" data-dir="prev" aria-label="<?php esc_attr_e( 'Trước', 'wp-course-intro' ); ?>">&#8249;</button>
					<button type="button" class="wci-carousel__nav" data-dir="next" aria-label="<?php esc_attr_e( 'Sau', 'wp-course-intro' ); ?>">&#8250;</button>
				</div>
			<?php endif; ?>

			<?php WCI_Template::carousel_markup( $query, $settings ); ?>
		</div>
		<?php
		wp_reset_postdata();

		return (string) ob_get_clean();
	}
}
