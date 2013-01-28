<?php

/*-----------------------------------------------------------------------------------*/
/* !Uninstall ====================================================================== */
/*-----------------------------------------------------------------------------------*/

register_uninstall_hook( SF_ABT_FILE, 'sf_abt_uninstall' );
function sf_abt_uninstall() {
	delete_option( 'sf-abt-open-files' );
	delete_option( '_sf_abt' );
	delete_metadata('user', 0, 'sf-abt-coworking', null, true);
	delete_metadata('user', 0, 'sf-abt-no-autosave', null, true);
	delete_metadata('user', 0, 'sf-abt-no-cowork-refresh', null, true);
}


/*-----------------------------------------------------------------------------------*/
/* !Disable autosave on demand ===================================================== */
/*-----------------------------------------------------------------------------------*/

add_action( 'load-post.php', 'sf_abt_disable_autosave' );
add_action( 'load-post-new.php', 'sf_abt_disable_autosave' );
function sf_abt_disable_autosave() {
	if ( (int) get_user_meta( get_current_user_id(), 'sf-abt-no-autosave', true ) ) {
		wp_deregister_script('autosave');
		add_action('admin_notices', 'sf_abt_disable_autosave_notice');
	}
}


function sf_abt_disable_autosave_notice() {
	echo '<div class="error"><p>'.__('Autosave disabled, you can (gently) mess with this post! ;)', 'sf-abt').'</p></div>';
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
add_action( 'admin_menu', 'sf_abt_menu', 100 );
function sf_abt_menu() {
	register_setting( 'sf-abt-settings', '_sf_abt', 'sf_abt_sanitize_settings' );

	add_submenu_page( 'options-general.php', SF_ABT_PLUGIN_NAME, SF_ABT_PLUGIN_NAME, 'manage_options', SF_ABT_PAGE_NAME, 'sf_abt_settings_page' );

	if ( sf_abt_is_autorized_coworker() ) {
		global $submenu;
		$add_all_options = true;
		if ( !empty($submenu['options-general.php']) ) {
			foreach ( $submenu['options-general.php'] as $option ) {
				if ( $option[2] == 'options.php' ) {
					$add_all_options = false;
					break;
				}
			}
		}
		if ( $add_all_options )
			add_options_page( __('All Settings'), __('All Settings'), 'manage_options', 'options.php');		// Add the "All settings" options page
	}
}


/* !Settings for the plugin page */
add_action( 'load-settings_page_'.SF_ABT_PAGE_NAME, 'sf_abt_register_settings' );
function sf_abt_register_settings() {
	global $current_user;
	get_currentuserinfo();

	$options			= sf_abt_get_options();
	$admins				= get_users( array('role' => 'administrator') );
	$args				= array( 'prefix' => '_sf_abt', 'options' => $options, 'current_user' => $current_user, 'admins' => $admins );
	$args['can_manage']	= ( empty( $options['coworkers'] ) || !sf_abt_coworkers_have_admin($options['coworkers']) || in_array( $current_user->ID, $options['coworkers'] ) );

	add_settings_section( 'general', __('General Settings'), '__return_null', SF_ABT_PAGE_NAME);

	if ( $args['can_manage'] ) {						// The user can manage the page: Empty list, or no admin in coworkers, or user is in the list

		add_settings_field( 'coworkers', __("Who's gonna use this plugin?", 'sf-abt'), 'sf_abt_coworkers_field', SF_ABT_PAGE_NAME, 'general', $args);

		do_action('sf-abt-settings', $args);			// Add more fields and sections

		add_settings_section( 'user_preferences', __('Personal Options'), '__return_null', SF_ABT_PAGE_NAME);

		add_settings_field( 'disable_cowork_refresh', '', 'sf_abt_no_auto_field', SF_ABT_PAGE_NAME, 'user_preferences', $args);

		do_action('sf-abt-preferences', $args);			// Add more fields and sections

		add_settings_section( 'sf_abt_submit', '', '__return_null', SF_ABT_PAGE_NAME);

		add_settings_field( 'submit', '', 'submit_button', SF_ABT_PAGE_NAME, 'sf_abt_submit');

	} else {											// The user can't manage the page

		add_settings_field( 'coworkers', __("Alone in the dark?", 'sf-abt'), 'sf_abt_coworkers_field', SF_ABT_PAGE_NAME, 'general', $args);

	}
}


// !Coworkers fields
function sf_abt_coworkers_field($o) {
	if ( $o['can_manage'] ) {

		foreach( $o['admins'] as $admin ) {
			echo "\t\t\t\t".'<label><input style="margin-top:-2px" type="checkbox" name="'.$o['prefix'].'[coworkers][]" value="'.$admin->ID.'"'.( in_array($admin->ID, $o['options']['coworkers']) ? ' checked="checked"' : '' ).'/> '.get_avatar($admin->ID, 16, '').' '.$admin->display_name."</label><br/>\n";
		}

	} else {

		echo '<p>'.__("You want to use this plugin? You should ask one of those awesome people to join the dev team.", 'sf-abt').'</p>';
		$coworkers = get_users( array( 'include' => $o['options']['coworkers'] ) );
		foreach( $coworkers as $coworker ) {
			echo "\t\t\t\t".'<a href="'.admin_url('user-edit.php?user_id='.$coworker->ID).'">'.get_avatar($coworker->ID, 16, '').' '.$coworker->display_name."</a><br/>\n";
		}

	}
}


// !"Coworking auto-refresh" and post autosave fields
function sf_abt_no_auto_field($o) {
	$no_refresh  = (int) get_user_meta( get_current_user_id(), 'sf-abt-no-cowork-refresh', true );
	$no_autosave = (int) get_user_meta( get_current_user_id(), 'sf-abt-no-autosave', true );

	echo "\t\t\t\t<h4>".__("These preferences apply to you only.", 'sf-abt')."</h4>\n";

	echo "\t\t\t\t<label>".
			'<input style="margin-top:-2px" type="checkbox" name="'.$o['prefix'].'[no-cowork-refresh]" value="1"'.( $no_refresh ? ' checked="checked"' : '' ).'/> '.
			__("Stop the coworking auto-refresh (please).", 'sf-abt')
		."</label>\n";

	echo "\t\t\t\t<p class='description'>".sprintf(__("Coworking enabled or not, the plugin launch an ajax call on window focus and every %s minutes to keep the statuses updated. This can be boring if you're working in your console, so you can disable that here.", 'sf-abt'), '<strong>5</strong>')."</p>\n";

	echo "\t\t\t\t<label>".
			'<input style="margin-top:-2px" type="checkbox" name="'.$o['prefix'].'[no-autosave]" value="1"'.( $no_autosave ? ' checked="checked"' : '' ).'/> '.
			__("Stop posts autosave.", 'sf-abt')
		."</label>\n";

	echo "\t\t\t\t<p class='description'>".sprintf(__("When you're on a post edit screen, WordPress keeps a track of your current editing every %s seconds with ajax calls. This can be boring if you're working in your console, so you can disable that here.", 'sf-abt'), '<strong>'.AUTOSAVE_INTERVAL.'</strong>')."</p>\n";
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
		?>
	</form>
</div>
<?php
}


/* !Sanitize options */
function sf_abt_sanitize_settings( $options ) {

	$default_options	= sf_abt_get_default();
	$opts				= sf_abt_get_options();
	$functions			= sf_abt_sanitization_functions();

	// Check if the current user can edit options
	if ( (sf_abt_coworkers_have_admin($opts['coworkers']) && !in_array( get_current_user_id(), $opts['coworkers']) ) || !current_user_can( 'administrator' ) )
		return $opts;

	// Sanitize options
	foreach( $default_options as $name => $def ) {
		$opts[$name] = isset($options[$name]) ? sf_abt_sanitize_option( $name, $options[$name], $functions ) : $def;
	}
	$opts = apply_filters('sf_abt_sanitize_settings', $opts, $options);

	// Update user preferences
	if ( isset($options['no-cowork-refresh']) && $options['no-cowork-refresh'] )
		update_user_meta(get_current_user_id(),'sf-abt-no-cowork-refresh', 1);
	else
		delete_user_meta(get_current_user_id(),'sf-abt-no-cowork-refresh');

	if ( isset($options['no-autosave']) && $options['no-autosave'] )
		update_user_meta(get_current_user_id(),'sf-abt-no-autosave', 1);
	else
		delete_user_meta(get_current_user_id(),'sf-abt-no-autosave');

	// If a user is not in the list, we have to remove his/her "open files" and metas

	// Get IDs of users who have "open files"
	$open_files	= sf_abt_get_open_files();

	// Get IDs of users with coworking enabled
	$users		= get_users(
					array(
						'meta_query' => array(
							'relation'	=> 'OR',
							array( 'key' => 'sf-abt-coworking' ),
							array( 'key' => 'sf-abt-no-cowork-refresh' ),
							array( 'key' => 'sf-abt-no-autosave' ),
						),
						'fields' => 'ID',
					)
				);

	// Merge all these geeks
	$users		= array_unique(array_merge($users, $open_files));

	if ( count($users) ) {

		foreach ( $users as $user ) {
			if ( !in_array($user, $opts['coworkers']) ) {
				// Remove user files from the open files list
				$user_files	= array_keys($open_files, $user);
				if ( count($user_files) ) {
					foreach ( $user_files as $user_file ) {
						unset($open_files[$user_file]);
					}
				}
				// Delete user metas
				delete_user_meta( $user, 'sf-abt-coworking' );
				delete_user_meta( $user, 'sf-abt-no-cowork-refresh' );
				delete_user_meta( $user, 'sf-abt-no-autosave' );
			}
		}
		// Update the open files list
		update_option('sf-abt-open-files', $open_files);

	}

	return $opts;
}


/*-----------------------------------------------------------------------------------*/
/* !Utilities ====================================================================== */
/*-----------------------------------------------------------------------------------*/

if ( !function_exists('__return_null') ) :
function __return_null(){
	return null;
}
endif;