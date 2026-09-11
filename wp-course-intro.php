<?php
/**
 * Plugin Name:       WP Course Intro
 * Plugin URI:        https://example.com/wp-course-intro
 * Description:        Quản lý và giới thiệu các khóa học: ảnh đại diện, mô tả, nội dung, yêu cầu đầu vào, hình thức học, cam kết đầu ra, giảng viên, trợ giảng và link đăng ký. Kèm trang chi tiết và carousel "khóa học khác".
 * Version:           1.0.4
 * Requires at least: 5.6
 * Requires PHP:      7.2
 * Author:            ClaudeCode
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-course-intro
 * Domain Path:       /languages
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCI_VERSION', '1.0.4' );
define( 'WCI_PLUGIN_FILE', __FILE__ );
define( 'WCI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCI_POST_TYPE', 'wci_course' );

/**
 * Show an admin notice and stop, instead of white-screening the whole site.
 *
 * @param string $message Human readable reason.
 */
function wci_bail( $message ) {
	add_action(
		'admin_notices',
		static function () use ( $message ) {
			echo '<div class="notice notice-error"><p><strong>WP Course Intro:</strong> '
				. esc_html( $message ) . '</p></div>';
		}
	);
}

/**
 * Load a required class file, bailing cleanly if it is missing.
 *
 * @param string $relative Path relative to the plugin includes/ folder.
 * @return bool
 */
function wci_require( $relative ) {
	$path = WCI_PLUGIN_DIR . 'includes/' . $relative;

	if ( ! file_exists( $path ) ) {
		wci_bail(
			sprintf(
				/* translators: %s: missing file path */
				__( 'Thiếu file "%s". Hãy tải lại toàn bộ thư mục plugin.', 'wp-course-intro' ),
				'includes/' . $relative
			)
		);
		return false;
	}

	require_once $path;
	return true;
}

/**
 * Boot the plugin after all plugins are loaded.
 */
function wci_boot() {
	if ( version_compare( PHP_VERSION, '7.2', '<' ) ) {
		wci_bail(
			sprintf(
				/* translators: %s: current PHP version */
				__( 'Cần PHP 7.2 trở lên. Máy chủ đang chạy PHP %s.', 'wp-course-intro' ),
				PHP_VERSION
			)
		);
		return;
	}

	$files = array(
		'class-wci-fields.php',
		'class-wci-post-type.php',
		'class-wci-meta-boxes.php',
		'class-wci-template.php',
		'class-wci-shortcode.php',
		'class-wci-plugin.php',
	);

	foreach ( $files as $file ) {
		if ( ! wci_require( $file ) ) {
			return;
		}
	}

	if ( ! class_exists( 'WCI_Plugin' ) ) {
		wci_bail( __( 'Không nạp được lớp WCI_Plugin.', 'wp-course-intro' ) );
		return;
	}

	( new WCI_Plugin() )->init();
}
add_action( 'plugins_loaded', 'wci_boot' );

/**
 * Register the post type on activation and flush rewrite rules.
 */
function wci_activate() {
	$pt_file = WCI_PLUGIN_DIR . 'includes/class-wci-post-type.php';

	if ( file_exists( $pt_file ) ) {
		require_once $pt_file;

		if ( class_exists( 'WCI_Post_Type' ) ) {
			WCI_Post_Type::register();
		}
	}

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wci_activate' );

/**
 * Clean rewrite rules on deactivation.
 */
function wci_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wci_deactivate' );
