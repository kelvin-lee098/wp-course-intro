<?php
/**
 * Plugin bootstrapper.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WCI_Plugin
 */
class WCI_Plugin {

	/**
	 * Wire up all modules.
	 */
	public function init() {
		( new WCI_Post_Type() )->hooks();
		( new WCI_Meta_Boxes() )->hooks();
		( new WCI_Template() )->hooks();
		( new WCI_Shortcode() )->hooks();

		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'wp-course-intro',
			false,
			dirname( plugin_basename( WCI_PLUGIN_FILE ) ) . '/languages'
		);
	}
}
