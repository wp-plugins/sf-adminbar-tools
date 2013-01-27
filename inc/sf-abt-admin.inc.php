<?php

/*-----------------------------------------------------------------------------------*/
/* !Uninstall ====================================================================== */
/*-----------------------------------------------------------------------------------*/

register_uninstall_hook( SF_ABT_FILE, 'sf_abt_uninstall' );
function sf_abt_uninstall() {
	delete_option( 'sf-abt-open-files' );
	delete_option( '_sf_abt' );
	delete_metadata('user', 0, 'sf-abt-coworking', null, true);
}


/*-----------------------------------------------------------------------------------*/
/* !Settings ======================================================================= */
/*-----------------------------------------------------------------------------------*/

/* !Adds a "Settings" link in the plugins list */
add_filter( 'plugin_action_links_'.plugin_basename(SF_ABT_FILE), 'sf_abt_settings_action_links', 10, 2 );
function sf_abt_settings_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'options-general.php?page='.SF_ABT_PAGE_NAME ) . '">' . __("Settings") . '</a>';
	array_unshift( $links, $settings_link );

	return $links;
}


/* !Menu item */
add_action( 'admin_menu', 'sf_abt_menu' );
function sf_abt_menu() {
	add_submenu_page( 'options-general.php', SF_ABT_PLUGIN_NAME, SF_ABT_PLUGIN_NAME, 'manage_options', SF_ABT_PAGE_NAME, 'sf_abt_settings_page' );

	register_setting( 'sf-abt-settings', '_sf_abt', 'sf_abt_sanitize' );
}


/* !Settings for the plugin page */
add_action( 'load-settings_page_'.SF_ABT_PAGE_NAME, 'sf_abt_register_settings' );
function sf_abt_register_settings() {
	global $current_user;
	get_currentuserinfo();

	$abt		= '_sf_abt';
	$options	= get_option($abt);
	$coworkers	= is_array($options) && isset($options['coworkers']) && is_array($options['coworkers']) ? array_map('intval', $options['coworkers']) : array();
	$options['coworkers'] = $coworkers;
	$admins		= get_users( array('role' => 'administrator') );

	$args		= array( 'prefix' => $abt, 'options' => $options, 'current_user' => $current_user, 'admins' => $admins );

	add_settings_section( 'sf_abt_enabled_for', __('Players involved', 'sf-abt'), '__return_null', SF_ABT_PAGE_NAME);

	if ( empty( $coworkers ) || !sf_abt_coworkers_have_admin($coworkers) || in_array( $current_user->ID, $coworkers ) ) {								// Empty list, or no admin in coworkers, or user is in the list

		$args['can_manage'] = true;
		add_settings_field( 'coworkers', __("Who's gonna use this plugin?", 'sf-abt'), 'sf_abt_coworkers_field', SF_ABT_PAGE_NAME, 'sf_abt_enabled_for', $args);
		add_action('sf-abt-settings', 'submit_button', 1000);

	} else {

		$args['can_manage'] = false;
		add_settings_field( 'coworkers', __("Alone in the dark?", 'sf-abt'), 'sf_abt_coworkers_field', SF_ABT_PAGE_NAME, 'sf_abt_enabled_for', $args);

	}
	do_action('sf-abt-fields', $args);
}


// !Coworkers fields
function sf_abt_coworkers_field($o) {
	if ( $o['can_manage'] ) {
		foreach( $o['admins'] as $admin ) {
			echo "\t\t\t\t".'<label><input style="margin-top:-2px" type="checkbox" name="'.$o['prefix'].'[coworkers][]" value="'.$admin->ID.'"'.( in_array($admin->ID, $o['options']['coworkers']) ? ' checked="checked"' : '' ).'/> '.get_avatar($admin->ID, 16, '').' '.$admin->display_name.'</label><br/>'."\n";
		}
	} else {
		echo '<p>'.__("You want to use this plugin? You should ask one of those awesome people to join the dev team.", 'sf-abt').'</p>';
		$coworkers = get_users( array( 'include' => $o['options']['coworkers'] ) );
		foreach( $coworkers as $coworker ) {
			echo "\t\t\t\t".'<a href="'.admin_url('user-edit.php?user_id='.$coworker->ID).'">'.get_avatar($coworker->ID, 16, '').' '.$coworker->display_name.'</a><br/>'."\n";
		}
	}
}


/* !Settings page */
function sf_abt_settings_page() { ?>
<div class="wrap">
	<div id="icon-<?php echo SF_ABT_PAGE_NAME; ?>" class="icon32" style="background: url(<?php echo SF_ABT_PLUGIN_URL; ?>images/icons.png) 0 0 no-repeat;"><br/></div>
	<?php // Yeah I know, screen_icon(SF_ABT_PAGE_NAME) would do the same, but when the plugin is not "activated" in this settings page, the css file is not enqueued, so... ?>

	<h2><?php echo esc_html( SF_ABT_PLUGIN_NAME ); ?></h2>

	<form name="sf_abt" method="post" action="options.php" id="sf_abt">
		<?php
		settings_fields( 'sf-abt-settings' );
		do_settings_sections( SF_ABT_PAGE_NAME );
		do_action('sf-abt-settings');
		?>
	</form>
</div>
<?php
}


/* !Sanitize options */
function sf_abt_sanitize($options) {

	// Check if the current user can edit options
	global $current_user;
	get_currentuserinfo();
	$opts		= get_option('_sf_abt');
	$coworkers	= is_array($opts) && isset($opts['coworkers']) && is_array($opts['coworkers']) ? array_map('intval', $opts['coworkers']) : array();
	$opts['coworkers'] = $coworkers;
	if ( (sf_abt_coworkers_have_admin($coworkers) && !in_array( $current_user->ID, $coworkers) ) || !current_user_can( 'administrator' ) )
		return $opts;

	// If a user is not in the list, we have to remove his/her "open files" and "coworking" meta

	// Get IDs of users who have "open files"
	$open_files	= get_option('sf-abt-open-files');
	$open_files	= is_array($open_files) ? $open_files : array();

	// Get IDs of users with coworking enabled
	$users		= get_users( array('meta_key' => 'sf-abt-coworking', 'fields' => 'ID') );

	// Merge all these geeks
	$users		= array_unique(array_map('intval', array_merge($users, $open_files)));

	// Coworkers IDs.
	$coworkers	= is_array($options) && isset($options['coworkers']) && is_array($options['coworkers']) ? array_map('intval', $options['coworkers']) : array();
	$options['coworkers'] = $coworkers;

	if ( count($users) ) {

		foreach ( $users as $user ) {
			if ( !in_array($user, $coworkers) ) {
				// Remove user files from the open files list
				$user_files	= array_keys($open_files, $user);
				if ( count($user_files) ) {
					foreach ( $user_files as $user_file ) {
						unset($open_files[$user_file]);
					}
				}
				// Delete user coworking meta
				delete_user_meta( $user, 'sf-abt-coworking' );
			}
		}
		// Update the open files list
		update_option('sf-abt-open-files', $open_files);

	}

	return $options;
}


/*-----------------------------------------------------------------------------------*/
/* !Utilities ====================================================================== */
/*-----------------------------------------------------------------------------------*/

// !Returns if the coworkers list still contains an administrator
function sf_abt_coworkers_have_admin($list) {
	if ( empty($list) )
		return false;

	foreach ( $list as $user ) {
		if ( user_can((int)$user, 'administrator') )
			return true;
	}
	return false;
}