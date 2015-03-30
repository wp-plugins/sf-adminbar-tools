<?php

/*-----------------------------------------------------------------------------------*/
/* !Coworking ajax requests ======================================================== */
/*-----------------------------------------------------------------------------------*/

// !Enable coworking
add_action( 'wp_ajax_sf_abt_enable_coworking',	'sf_abt_enable_coworking');
function sf_abt_enable_coworking() {
	check_ajax_referer('sf-abt_enable-coworking', 'nonce', 'error');

	if ( !current_user_can('administrator') || !($uid = get_current_user_id()) )
		exit();

	// Update the user meta
	$cowork	= sf_abt_cowork_enabled( $uid );
	update_user_meta($uid, 'sf-abt-coworking', ($cowork ? 0 : 1));

	// Clear the open files
	$open_files	= sf_abt_get_open_files();
	if ( count($open_files) ) {
		foreach ($open_files as $file => $user) {
			if ( (int) $user == $uid )
				unset($open_files[$file]);
		}
		update_option('sf-abt-open-files', $open_files);
	}

	echo $cowork ? 'stop' : 'start';
	exit();
}


// !Flag a file as "mine" or "free"
add_action( 'wp_ajax_sf_abt_update_open_files',	'sf_abt_update_open_files');
function sf_abt_update_open_files() {
	check_ajax_referer('sf-abt-update-open-files', 'nonce', 'error');

	if ( !current_user_can('administrator') || !($uid = get_current_user_id()) )
		exit();

	if ( isset($_POST['file']) && $_POST['file'] ) {

		$open_files	= sf_abt_get_open_files();
		$file = WP_CONTENT_DIR.esc_attr($_POST['file']);

		if ( !isset($open_files[$file]) || !$open_files[$file] ) {		// File is not open yet, the user can assign it to himself. We return the avatar
			$open_files[$file] = $uid;
			update_option('sf-abt-open-files', $open_files);
			$msg = array( 'msg' => 'you-got-it-my-young-padawan', 'avatar' => get_abt_avatar($uid, get_userdata($uid)->display_name), 'files' => $open_files );
		} elseif ( $open_files[$file] == $uid ) {						// File is already open by the user, this means the user want to free it, someone else can use it
			unset($open_files[$file]);
			update_option('sf-abt-open-files', $open_files);
			$msg = array( 'msg' => 'i-m-not-a-number', 'files' => $open_files );
		} else {														// File is open by someone else, forbid the switch
			if ( !($otheruserdata = get_userdata($open_files[$file])) ) {
				echo '{"msg":"error"}';
				exit();
			}
			$msg = array( 'msg' => 'you-shall-not-pass', 'avatar' => get_abt_avatar($open_files[$file], $otheruserdata->display_name), 'files' => $open_files );
		}

	} else
		$msg = array('msg' => 'error');

	echo json_encode($msg);
	exit();
}


// !Flag all my files as "free"
add_action( 'wp_ajax_sf_abt_close_all_my_files',	'sf_abt_close_my_open_files');
function sf_abt_close_my_open_files() {
	check_ajax_referer('sf-abt-close-my-open-files', 'nonce', 'error');

	if ( !current_user_can('administrator') || !($uid = get_current_user_id()) )
		exit();

	$open_files	= sf_abt_get_open_files();

	if ( count($open_files) ) {
		foreach ($open_files as $file => $user) {
			if ( $user == $uid )
				unset($open_files[$file]);
		}
		update_option('sf-abt-open-files', $open_files);
	}
	echo 'removed';

	exit();
}


// !Refresh counters (every 5 minutes)
add_action( 'wp_ajax_sf_abt_refresh_counters',	'sf_abt_refresh_counters');
function sf_abt_refresh_counters() {
	check_ajax_referer('sf-abt-refresh-counters', 'nonce', 'error');

	if ( !current_user_can('administrator') || !($uid = get_current_user_id()) )
		exit();

	// If user disabled coworking, remove counters
	$cowork		= sf_abt_cowork_enabled( $uid );
	if ( !$cowork ) {
		echo json_encode( array( 'me' => 0, 'other' => 0, 'cowork' => 0 ) );
		exit();
	}

	$open_files			= sf_abt_get_open_files();
	$my_files			= array_keys($open_files, $uid);
	$my_files_count		= count($my_files);
	$other_files_count	= count($open_files) - $my_files_count;

	echo json_encode( array( 'me' => $my_files_count, 'other' => $other_files_count, 'cowork' => 1 ) );
	exit();
}


// !Refresh files tree: launched on top item hover
add_action( 'add_admin_bar_menus_ajax',		'sf_abt_admin_bar_menus' );
add_action( 'wp_ajax_sf_abt_refresh_files',	'sf_abt_refresh_files');
function sf_abt_refresh_files() {
	check_ajax_referer('sf-abt-refresh-files', 'nonce', 'error');

	if ( !current_user_can('administrator') )
		exit();

	global $current_user;
	get_currentuserinfo();
	$open_files		= sf_abt_get_open_files();
	$cowork			= sf_abt_cowork_enabled( $current_user->ID );

	$page_files		= isset($_POST['files']) && is_array($_POST['files']) ? $_POST['files'] : array();
	$page_cowork	= isset($_GET['cowork']) && $_GET['cowork'] == 'start' ? 1 : 0;										//echo $page_cowork;
	$force			= isset($_GET['force']) && $_GET['force'] ? 1 : 0;													//echo $force;
	$force			= $cowork  && !$page_cowork ? 1 : $force;															//echo $force;
	$force			= !$cowork && $page_cowork ? 'stop' : $force;														//echo $force;

	if ( !$cowork  && !$page_cowork ) {
		echo json_encode( array( 'files' => 'not modified' ) );
		exit();
	} elseif ( $force === 'stop' ) {
		echo json_encode( array( 'files' => 'stop' ) );
		exit();
	} elseif ( $page_files == $open_files && !$force ) {
		echo json_encode( array( 'files' => 'not modified' ) );
		exit();
	} elseif ( $page_files != $open_files && !$force ) {			// Partial tree refresh
		$avatars = array();
		$avatars[$current_user->ID] = get_abt_avatar($current_user->ID, $current_user->display_name);
		foreach ( $open_files as $uid ) {
			if ( !isset($avatars[$uid]) ) {
				$userdata = get_userdata($uid);
				$avatars[$uid] = get_abt_avatar($uid, $userdata->display_name);
			}
		}
		echo json_encode( array( 'files' => $open_files, 'avatars' => $avatars ) );
		exit();
	}

	global $wp_admin_bar, $wp_query;

	$wp_query->is_admin = 0;

	if ( !class_exists( 'wp_admin_bar_class' ) )
		require( ABSPATH . WPINC . '/class-wp-admin-bar.php' );

	if ( !class_exists('SF_ABT_Admin_Bar') ) :
		class SF_ABT_Admin_Bar extends WP_Admin_Bar {

			public function render() {
				$root = $this->_bind();
				if ( $root )
					$this->_sfabt_render( $root );
			}

			final protected function _sfabt_render( $root ) {

				foreach ( $root->children as $group ) {
					$this->_render_group( $group );
				}
			}

		}
	endif;

	$admin_bar_class = 'SF_ABT_Admin_Bar';

	if ( class_exists( $admin_bar_class ) )
		$wp_admin_bar = new $admin_bar_class;
	else
		exit();

	ob_start();
	$wp_admin_bar->initialize();

	do_action( 'add_admin_bar_menus_ajax' );	// Trick: change the action name to trigger only my actions and speed up the process. All others are useless here.

	do_action_ref_array( 'admin_bar_menu', array( &$wp_admin_bar ) );

	do_action( 'wp_before_admin_bar_render' );

	$wp_admin_bar->render();

	do_action( 'wp_after_admin_bar_render' );
	$out = ob_get_clean();

	echo json_encode( array( 'files' => $open_files, 'html' => $out ) );
	exit();
}