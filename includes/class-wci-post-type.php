<?php
/**
 * Course custom post type.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCI_Post_Type
 */
class WCI_Post_Type {

	/**
	 * Hook registration.
	 */
	public function hooks() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_' . WCI_POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . WCI_POST_TYPE . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
	}

	/**
	 * Register the post type.
	 */
	public static function register() {
		$labels = array(
			'name'                  => __( 'Khóa học', 'wp-course-intro' ),
			'singular_name'         => __( 'Khóa học', 'wp-course-intro' ),
			'menu_name'             => __( 'Khóa học', 'wp-course-intro' ),
			'add_new'               => __( 'Thêm khóa học', 'wp-course-intro' ),
			'add_new_item'          => __( 'Thêm khóa học mới', 'wp-course-intro' ),
			'edit_item'             => __( 'Sửa khóa học', 'wp-course-intro' ),
			'new_item'              => __( 'Khóa học mới', 'wp-course-intro' ),
			'view_item'             => __( 'Xem khóa học', 'wp-course-intro' ),
			'view_items'            => __( 'Xem các khóa học', 'wp-course-intro' ),
			'search_items'          => __( 'Tìm khóa học', 'wp-course-intro' ),
			'not_found'             => __( 'Không tìm thấy khóa học nào.', 'wp-course-intro' ),
			'not_found_in_trash'    => __( 'Không có khóa học trong thùng rác.', 'wp-course-intro' ),
			'all_items'             => __( 'Tất cả khóa học', 'wp-course-intro' ),
			'featured_image'        => __( 'Ảnh đại diện khóa học', 'wp-course-intro' ),
			'set_featured_image'    => __( 'Đặt ảnh đại diện', 'wp-course-intro' ),
			'remove_featured_image' => __( 'Xóa ảnh đại diện', 'wp-course-intro' ),
			'use_featured_image'    => __( 'Dùng làm ảnh đại diện', 'wp-course-intro' ),
			'archives'              => __( 'Danh sách khóa học', 'wp-course-intro' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'has_archive'        => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-welcome-learn-more',
			'menu_position'      => 20,
			'hierarchical'       => false,
			'rewrite'            => array(
				'slug'       => 'khoa-hoc',
				'with_front' => false,
			),
			'supports'           => array( 'title', 'thumbnail', 'excerpt', 'revisions', 'page-attributes' ),
			'capability_type'    => 'post',
		);

		register_post_type( WCI_POST_TYPE, apply_filters( 'wci_post_type_args', $args ) );
	}

	/**
	 * Admin list columns.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		$new = array();

		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['wci_thumb'] = __( 'Ảnh', 'wp-course-intro' );
			}

			$new[ $key ] = $label;

			if ( 'title' === $key ) {
				$new['wci_register'] = __( 'Link đăng ký', 'wp-course-intro' );
			}
		}

		return $new;
	}

	/**
	 * Render custom column values.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function column_content( $column, $post_id ) {
		if ( 'wci_thumb' === $column ) {
			echo has_post_thumbnail( $post_id )
				? get_the_post_thumbnail( $post_id, array( 60, 60 ) )
				: '<span aria-hidden="true">—</span>';
		}

		if ( 'wci_register' === $column ) {
			$url = WCI_Fields::get( $post_id, 'register_url' );

			if ( $url ) {
				printf(
					'<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
					esc_url( $url ),
					esc_html__( 'Mở', 'wp-course-intro' )
				);
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
		}
	}
}
