<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cheatin\' uh?' );
}

/*------------------------------------------------------------------------------------------------*/
/* !PLUGIN SETTINGS ============================================================================= */
/*------------------------------------------------------------------------------------------------*/

// !Add a "Settings" link in the plugins list

add_filter( 'plugin_action_links_' . SFABT_PLUGIN_BASENAME, 'sfabt_settings_action_link', 10, 2 );
add_filter( 'network_admin_plugin_action_links_' . SFABT_PLUGIN_BASENAME, 'sfabt_settings_action_link', 10, 2 );

function sfabt_settings_action_link( $links, $file ) {
	$links['settings'] = '<a href="' . self_admin_url( 'profile.php' ) . '#sf-adminbar-tools">' . __( 'Profile' ) . '</a>';
	return $links;
}


/*------------------------------------------------------------------------------------------------*/
/* !"ALL SETTINGS" MENU ITEM ==================================================================== */
/*------------------------------------------------------------------------------------------------*/

add_action( 'admin_menu', 'sfabt_add_all_settings_menu_item', PHP_INT_MAX );

function sfabt_add_all_settings_menu_item() {
	global $submenu;
	$add_all_options = true;

	if ( ! empty( $submenu['options-general.php'] ) ) {
		foreach ( $submenu['options-general.php'] as $option ) {
			if ( $option[2] === 'options.php' ) {
				$add_all_options = false;
				break;
			}
		}
	}

	if ( $add_all_options ) {
		add_options_page( __( 'All Settings' ), __( 'All Settings' ), 'manage_options', 'options.php' );
	}
}


/*------------------------------------------------------------------------------------------------*/
/* !DISABLE AUTOSAVE AND ITS FRIENDS ============================================================ */
/*------------------------------------------------------------------------------------------------*/

add_action( 'load-post.php', 'sfabt_maybe_disable_autosave' );
add_action( 'load-post-new.php', 'sfabt_maybe_disable_autosave' );

function sfabt_maybe_disable_autosave() {
	global $wp_scripts;

	if ( ! get_user_meta( get_current_user_id(), 'sf-abt-no-autosave', true ) ) {
		return;
	}

	// Starting with WP 3.6, Heartbeat comes on stage.
	$is_36 = version_compare( $GLOBALS['wp_version'], '3.6', '>=' );

	// Remove autosave and heartbeat from dependencies to not prevent other scripts from being enqueued.
	// Of course it can lead to troubles, but it's better than dequeueing all other scripts.
	if ( ! empty( $wp_scripts->registered ) ) {
		foreach ( $wp_scripts->registered as $s => $r ) {
			if ( ! empty( $r->deps ) ) {
				if ( $is_36 && false !== ( $pos = array_search( 'heartbeat', $r->deps ) ) ) {
					unset( $wp_scripts->registered[ $s ]->deps[ $pos ] );
				}
				if ( false !== ( $pos = array_search( 'autosave', $r->deps ) ) ) {
					unset( $wp_scripts->registered[ $s ]->deps[ $pos ] );
				}
			}
		}
	}

	// Remove autosave and heartbeat from the queue to not trigger notices
	$wp_scripts->queue = array_values( array_diff( $wp_scripts->queue, array( 'autosave', 'heartbeat' ) ) );

	// Finally, remove autosave and heartbeat
	wp_deregister_script( 'autosave' );

	if ( $is_36 ) {
		wp_deregister_script( 'heartbeat' );	// Dans ton cul Lulu ! \o/
	}

	// Remind the user.
	add_action( 'admin_notices', 'sfabt_disable_autosave_notice' );

	// Disable post lock dialog.
	add_filter( 'show_post_locked_dialog', '__return_false' );

	// Make sure I'm not set as the user who locks the post edition.
	add_filter( 'update_post_metadata', 'sfabt_dont_set_post_lock', 10, 5 );

}


// Notice displayed on post edit page.

function sfabt_disable_autosave_notice() {
	global $post;

	echo '<div class="error">';

		echo '<p>' . __( 'Autosave and post lock disabled. As long as you don\'t save your modifications, you can mess with this post!', 'sf-adminbar-tools' ) . '</p>';

		if ( function_exists( 'wp_check_post_lock' ) && $user_id = wp_check_post_lock( $post->ID ) ) {
			$user = get_userdata( $user_id );
			printf( '<p>' . __( '%s is currently editing this post.', 'sf-adminbar-tools' ), esc_html( $user->display_name ) ) . '</p>';
		}

	echo "</div>\n";
}


// Make sure I'm not set as the user who locks the post edition: we'll filter the '_edit_lock' meta value when it's updated.

function sfabt_dont_set_post_lock( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
	if ( $meta_key !== '_edit_lock' || ! $meta_value ) {
		return $check;
	}

	$meta_value = explode( ':', $meta_value );

	// No user ID or user ID is not mine.
	if ( empty( $meta_value[1] ) || (int) $meta_value[1] !== get_current_user_id() ) {
		return $check;
	}

	// We'll try to fallback to the previous value.
	$previous = get_post_meta( $object_id, '_edit_lock', true );

	// No previous value.
	if ( ! $previous ) {
		return $previous;
	}

	//Current time value
	$time = (int) $meta_value[0];

	$meta_value = explode( ':', $previous );

	// No user ID.
	if ( empty( $meta_value[1] ) ) {
		return $previous;
	}

	// If the previous user ID is mine, return false. Else, return the new value for the time + the previous value for the user ID.
	return (int) $meta_value[1] === get_current_user_id() ? false : $time . ':' . (int) $meta_value[1];
}


/*------------------------------------------------------------------------------------------------*/
/* !WP SEO GO TO HELL =========================================================================== */
/*------------------------------------------------------------------------------------------------*/

add_action( 'init', 'sfabt_maybe_remove_wpseo_admin_stuff' );

function sfabt_maybe_remove_wpseo_admin_stuff() {
	global $wpseo_metabox;

	if ( ! defined( 'WPSEO_VERSION' ) || ! get_user_meta( get_current_user_id(), 'sf-abt-no-wpseo', true ) ) {
		return;
	}

	// Columns
	add_filter( 'wpseo_use_page_analysis', '__return_false' );

	// Metaboxes
	remove_action( 'add_meta_boxes', array( $wpseo_metabox, 'add_meta_box' ) );
	remove_action( 'admin_enqueue_scripts', array( $wpseo_metabox, 'enqueue' ) );
	remove_action( 'wp_insert_post', array( $wpseo_metabox, 'save_postdata' ) );
	remove_action( 'edit_attachment', array( $wpseo_metabox, 'save_postdata' ) );
	remove_action( 'add_attachment', array( $wpseo_metabox, 'save_postdata' ) );
	remove_action( 'admin_init', array( $wpseo_metabox, 'setup_page_analysis' ) );
	remove_action( 'admin_init', array( $wpseo_metabox, 'translate_meta_boxes' ) );
}

/**/