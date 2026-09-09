<?php
/**
 * Uninstall cleanup.
 *
 * Removes course posts and their meta. Runs only on "Delete" from the
 * Plugins screen — not on deactivate.
 *
 * @package WP_Course_Intro
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'WCI_POST_TYPE' ) ) {
	define( 'WCI_POST_TYPE', 'wci_course' );
}

/**
 * Set to true only if you want course data wiped when the plugin is deleted.
 * Kept false by default so an accidental delete does not destroy content.
 */
$wci_purge = apply_filters( 'wci_purge_on_uninstall', false );

if ( $wci_purge ) {
	$courses = get_posts(
		array(
			'post_type'      => WCI_POST_TYPE,
			'post_status'    => 'any',
			'numberposts'    => -1,
			'fields'         => 'ids',
		)
	);

	foreach ( $courses as $course_id ) {
		wp_delete_post( $course_id, true );
	}
}
