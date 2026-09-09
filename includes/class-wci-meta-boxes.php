<?php
/**
 * Meta boxes for course fields.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCI_Meta_Boxes
 */
class WCI_Meta_Boxes {

	const NONCE_ACTION = 'wci_save_course';
	const NONCE_NAME   = 'wci_course_nonce';

	/**
	 * Hook registration.
	 */
	public function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post_' . WCI_POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
	}

	/**
	 * Register meta boxes.
	 */
	public function add() {
		add_meta_box(
			'wci_details',
			__( 'Thông tin khóa học', 'wp-course-intro' ),
			array( $this, 'render_details' ),
			WCI_POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'wci_people',
			__( 'Giảng viên & Trợ giảng', 'wp-course-intro' ),
			array( $this, 'render_people' ),
			WCI_POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Enqueue admin assets only on the course editor.
	 *
	 * @param string $hook Current admin page.
	 */
	public function assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || WCI_POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'wci-admin',
			WCI_PLUGIN_URL . 'assets/css/wci-admin.css',
			array(),
			WCI_VERSION
		);

		wp_enqueue_script(
			'wci-admin',
			WCI_PLUGIN_URL . 'assets/js/wci-admin.js',
			array( 'jquery' ),
			WCI_VERSION,
			true
		);

		wp_localize_script(
			'wci-admin',
			'wciAdmin',
			array(
				'chooseImage' => __( 'Chọn ảnh', 'wp-course-intro' ),
				'useImage'    => __( 'Dùng ảnh này', 'wp-course-intro' ),
				'remove'      => __( 'Xóa', 'wp-course-intro' ),
				'namePlaceholder' => __( 'Họ và tên', 'wp-course-intro' ),
			)
		);
	}

	/**
	 * Render the main details meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_details( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<div class="wci-fields">';

		foreach ( WCI_Fields::simple_fields() as $name => $field ) {
			$value    = WCI_Fields::get( $post->ID, $name );
			$field_id = 'wci_' . $name; // underscores only — safe as a TinyMCE editor id.

			echo '<div class="wci-field wci-field--' . esc_attr( $field['type'] ) . '">';
			printf(
				'<label class="wci-field__label" for="%1$s">%2$s</label>',
				esc_attr( $field_id ),
				esc_html( $field['label'] )
			);

			switch ( $field['type'] ) {
				case 'wysiwyg':
					wp_editor(
						$value,
						$field_id,
						array(
							'textarea_name' => WCI_Fields::key( $name ),
							'textarea_rows' => 8,
							'media_buttons' => true,
						)
					);
					break;

				case 'textarea':
					printf(
						'<textarea class="widefat" id="%1$s" name="%2$s" rows="3">%3$s</textarea>',
						esc_attr( $field_id ),
						esc_attr( WCI_Fields::key( $name ) ),
						esc_textarea( $value )
					);
					break;

				case 'url':
					printf(
						'<input type="url" class="widefat code" id="%1$s" name="%2$s" value="%3$s" placeholder="https://" />',
						esc_attr( $field_id ),
						esc_attr( WCI_Fields::key( $name ) ),
						esc_attr( $value )
					);
					break;

				default:
					printf(
						'<input type="text" class="widefat" id="%1$s" name="%2$s" value="%3$s" />',
						esc_attr( $field_id ),
						esc_attr( WCI_Fields::key( $name ) ),
						esc_attr( $value )
					);
			}

			if ( ! empty( $field['description'] ) ) {
				printf( '<p class="description">%s</p>', esc_html( $field['description'] ) );
			}

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render the people (instructors / assistants) meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_people( $post ) {
		foreach ( WCI_Fields::people_groups() as $group => $label ) {
			$people = WCI_Fields::get_people( $post->ID, $group );
			$key    = WCI_Fields::key( $group );

			echo '<div class="wci-people" data-group="' . esc_attr( $group ) . '">';
			printf( '<h3 class="wci-people__title">%s</h3>', esc_html( $label ) );

			echo '<div class="wci-people__list">';

			if ( empty( $people ) ) {
				$this->render_person_row( $key, 0, '', 0 );
			} else {
				foreach ( $people as $index => $person ) {
					$this->render_person_row( $key, $index, $person['name'], $person['image_id'] );
				}
			}

			echo '</div>';

			printf(
				'<p><button type="button" class="button wci-people__add" data-key="%1$s">%2$s</button></p>',
				esc_attr( $key ),
				esc_html__( '+ Thêm người', 'wp-course-intro' )
			);

			echo '</div>';
		}

		// Template row for JS cloning.
		echo '<script type="text/html" id="tmpl-wci-person-row">';
		$this->render_person_row( '__KEY__', '__INDEX__', '', 0 );
		echo '</script>';
	}

	/**
	 * Render a single person row (image + name).
	 *
	 * @param string     $key      Base meta key.
	 * @param int|string $index    Row index.
	 * @param string     $name     Person name.
	 * @param int        $image_id Attachment ID.
	 */
	protected function render_person_row( $key, $index, $name, $image_id ) {
		$field_base = $key . '[' . $index . ']';
		$img_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
		?>
		<div class="wci-person">
			<div class="wci-person__media">
				<div class="wci-person__preview<?php echo $img_url ? '' : ' is-empty'; ?>">
					<?php if ( $img_url ) : ?>
						<img src="<?php echo esc_url( $img_url ); ?>" alt="" />
					<?php endif; ?>
				</div>
				<input
					type="hidden"
					class="wci-person__image-id"
					name="<?php echo esc_attr( $field_base . '[image_id]' ); ?>"
					value="<?php echo esc_attr( $image_id ); ?>"
				/>
				<button type="button" class="button-link wci-person__pick"><?php esc_html_e( 'Chọn ảnh', 'wp-course-intro' ); ?></button>
				<button type="button" class="button-link wci-person__clear"<?php echo $img_url ? '' : ' hidden'; ?>><?php esc_html_e( 'Bỏ ảnh', 'wp-course-intro' ); ?></button>
			</div>
			<div class="wci-person__body">
				<input
					type="text"
					class="widefat wci-person__name"
					name="<?php echo esc_attr( $field_base . '[name]' ); ?>"
					value="<?php echo esc_attr( $name ); ?>"
					placeholder="<?php esc_attr_e( 'Họ và tên', 'wp-course-intro' ); ?>"
				/>
				<button type="button" class="button-link-delete wci-person__remove"><?php esc_html_e( 'Xóa người này', 'wp-course-intro' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Persist submitted values.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Simple fields.
		foreach ( WCI_Fields::simple_fields() as $name => $field ) {
			$key = WCI_Fields::key( $name );
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

			switch ( $field['type'] ) {
				case 'wysiwyg':
					$clean = wp_kses_post( $raw );
					break;

				case 'url':
					$clean = esc_url_raw( trim( $raw ) );
					break;

				case 'textarea':
					$clean = sanitize_textarea_field( $raw );
					break;

				default:
					$clean = sanitize_text_field( $raw );
			}

			if ( '' === $clean ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $clean );
			}
		}

		// People groups.
		foreach ( array_keys( WCI_Fields::people_groups() ) as $group ) {
			$key   = WCI_Fields::key( $group );
			$raw   = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : array();
			$clean = array();

			foreach ( $raw as $person ) {
				$name     = isset( $person['name'] ) ? sanitize_text_field( $person['name'] ) : '';
				$image_id = isset( $person['image_id'] ) ? absint( $person['image_id'] ) : 0;

				if ( '' === $name && ! $image_id ) {
					continue;
				}

				$clean[] = array(
					'name'     => $name,
					'image_id' => $image_id,
				);
			}

			if ( empty( $clean ) ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $clean );
			}
		}
	}
}
