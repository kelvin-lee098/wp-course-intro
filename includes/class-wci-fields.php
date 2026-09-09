<?php
/**
 * Central definition of every custom course field.
 *
 * One source of truth used by the meta boxes (render + save) and the
 * front-end template so the two never drift apart.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCI_Fields
 */
class WCI_Fields {

	/**
	 * Meta key prefix for every stored value.
	 */
	const PREFIX = '_wci_';

	/**
	 * Simple scalar / rich-text fields.
	 *
	 * type: text | url | textarea | wysiwyg
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function simple_fields() {
		return array(
			'summary'      => array(
				'label'       => __( 'Mô tả ngắn', 'wp-course-intro' ),
				'type'        => 'textarea',
				'description' => __( 'Tóm tắt hiển thị ở đầu trang chi tiết và trên thẻ khóa học.', 'wp-course-intro' ),
			),
			'content'      => array(
				'label'       => __( 'Nội dung khóa học', 'wp-course-intro' ),
				'type'        => 'wysiwyg',
				'description' => __( 'Chương trình học chi tiết, các module, buổi học...', 'wp-course-intro' ),
			),
			'requirements' => array(
				'label'       => __( 'Yêu cầu đầu vào', 'wp-course-intro' ),
				'type'        => 'wysiwyg',
				'description' => __( 'Kiến thức / thiết bị / trình độ cần có trước khi tham gia.', 'wp-course-intro' ),
			),
			'format'       => array(
				'label'       => __( 'Hình thức học', 'wp-course-intro' ),
				'type'        => 'textarea',
				'description' => __( 'Ví dụ: Online qua Zoom, Offline tại cơ sở, Hybrid, số buổi, thời lượng...', 'wp-course-intro' ),
			),
			'outcome'      => array(
				'label'       => __( 'Cam kết đầu ra', 'wp-course-intro' ),
				'type'        => 'wysiwyg',
				'description' => __( 'Học viên đạt được gì sau khóa học, chứng chỉ, cam kết hỗ trợ...', 'wp-course-intro' ),
			),
			'register_url' => array(
				'label'       => __( 'Link đăng ký', 'wp-course-intro' ),
				'type'        => 'url',
				'description' => __( 'URL biểu mẫu / trang đăng ký khóa học.', 'wp-course-intro' ),
			),
		);
	}

	/**
	 * Repeatable people groups (image + name).
	 *
	 * @return array<string,string>
	 */
	public static function people_groups() {
		return array(
			'instructors' => __( 'Giảng viên', 'wp-course-intro' ),
			'assistants'  => __( 'Trợ giảng', 'wp-course-intro' ),
		);
	}

	/**
	 * Full meta key for a field name.
	 *
	 * @param string $name Field name.
	 * @return string
	 */
	public static function key( $name ) {
		return self::PREFIX . $name;
	}

	/**
	 * Read a simple field, already sanitised on save.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $name    Field name.
	 * @return string
	 */
	public static function get( $post_id, $name ) {
		return (string) get_post_meta( $post_id, self::key( $name ), true );
	}

	/**
	 * Read a people group as a clean list of [ 'name' => ..., 'image_id' => int ].
	 *
	 * @param int    $post_id Post ID.
	 * @param string $group   Group name (instructors|assistants).
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_people( $post_id, $group ) {
		$raw = get_post_meta( $post_id, self::key( $group ), true );

		if ( ! is_array( $raw ) ) {
			return array();
		}

		$people = array();

		foreach ( $raw as $person ) {
			$name     = isset( $person['name'] ) ? trim( (string) $person['name'] ) : '';
			$image_id = isset( $person['image_id'] ) ? absint( $person['image_id'] ) : 0;

			if ( '' === $name && ! $image_id ) {
				continue;
			}

			$people[] = array(
				'name'     => $name,
				'image_id' => $image_id,
			);
		}

		return $people;
	}
}
