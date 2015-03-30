<?php
/*
 * Plugin Name: SF Admin bar tools
 * Plugin URI: http://scri.in/sf-abt2/
 * Description: Adds some small interesting tools to the admin bar for developers
 * Version: 2.1.1
 * Author: GregLone
 * Author URI: http://www.screenfeed.fr/greg/
 * License: GPLv3
 * License URI: http://www.screenfeed.fr/gpl-v3.txt
 * Text Domain: sf-abt
 * Domain Path: /languages/
*/


global $wp_version;
if ( version_compare($wp_version, '3.1', '<') )
	return;

define( 'SF_ABT_PLUGIN_NAME',	'SF Admin Bar Tools' );
define( 'SF_ABT_PAGE_NAME',		'sf_abt_config' );
define( 'SF_ABT_FILE',			__FILE__ );
define( 'SF_ABT_DIRNAME',		basename( dirname( SF_ABT_FILE ) ) );
define( 'SF_ABT_PLUGIN_URL',	plugin_dir_url( SF_ABT_FILE ) );
define( 'SF_ABT_PLUGIN_DIR',	plugin_dir_path( SF_ABT_FILE ) );

/*-----------------------------------------------------------------------------------*/
/* !Language support =============================================================== */
/*-----------------------------------------------------------------------------------*/

add_action( 'init', 'sf_abt_lang_init' );
function sf_abt_lang_init() {
	load_plugin_textdomain( 'sf-abt', false, SF_ABT_DIRNAME . '/languages/' );
}


/*-----------------------------------------------------------------------------------*/
/* !Activation ===================================================================== */
/*-----------------------------------------------------------------------------------*/

register_activation_hook( SF_ABT_FILE, 'sf_abt_activation' );
function sf_abt_activation() {
	$opts		= sf_abt_get_options();
	$user_id	= get_current_user_id();

	if ( !sf_abt_coworkers_have_admin($opts['coworkers']) && !in_array( $user_id, $opts['coworkers'] ) && current_user_can( 'administrator' ) ) {
		$opts['coworkers'][] = $user_id;
		update_option('_sf_abt', $opts);
	}
}


/*-----------------------------------------------------------------------------------*/
/* !Init =========================================================================== */
/*-----------------------------------------------------------------------------------*/

add_action( 'plugins_loaded', 'sf_abt_include_cowork' );
function sf_abt_include_cowork() {

	if ( is_admin() && !defined('DOING_AJAX') )
		include(SF_ABT_PLUGIN_DIR.'/inc/sf-abt-admin.inc.php');						// !Plugin page

	$user_id = get_current_user_id();

	if ( !sf_abt_is_autorized_coworker( $user_id ) )
		return;

	if ( sf_abt_cowork_enabled( $user_id ) )
		include(SF_ABT_PLUGIN_DIR.'/inc/sf-abt-cowork.inc.php');					// !Coworking nodes

	if ( is_admin() ) {

		if ( defined('DOING_AJAX') && DOING_AJAX )
			include(SF_ABT_PLUGIN_DIR.'/inc/sf-abt-ajax.inc.php');					// !Coworking ajax requests
		else
			include(SF_ABT_PLUGIN_DIR.'/inc/sf-abt-admin-nodes.inc.php');			// !Admin nodes

	}

	include(SF_ABT_PLUGIN_DIR.'/inc/sf-abt.inc.php');
}


/*-----------------------------------------------------------------------------------*/
/* !Utilities ====================================================================== */
/*-----------------------------------------------------------------------------------*/

// !Return sanitized option(s)
function sf_abt_get_options( $name = false ) {
	$default_options	= sf_abt_get_default();
	$functions			= sf_abt_sanitization_functions();
	$abt_options		= get_option( '_sf_abt' );
	$abt_options		= is_array($abt_options) ? $abt_options : array();
	$options			= array();

	if ( $name )
		return isset($abt_options[$name]) ? sf_abt_sanitize_option( $name, $abt_options[$name], $functions ) : $default_options[$name];

	foreach( $default_options as $name => $def ) {
		$options[$name] = isset($abt_options[$name]) ? sf_abt_sanitize_option( $name, $abt_options[$name], $functions ) : $def;
	}
	return $options;
}


// !Compat - Return an array of all coworkers
function sf_abt_get_coworkers() {
	return sf_abt_get_options( 'coworkers' );
}


// !Return default options
function sf_abt_get_default( $option = false ) {
	$default_options = array(
		'coworkers' => array(),
	);

	$default_options = apply_filters( 'sf_abt_default_options', $default_options );

	if ( $option )		// We request only one default option
		return isset($default_theme_options[$option]) ? $default_theme_options[$option] : '';

	return $default_options;
}


// !Return an array of functions for sanitization purpose
function sf_abt_sanitization_functions( $option = false ) {
	$functions = array(
		'coworkers'	=> array( 'function' => 'wp_parse_id_list' ),		// key == 'function': will use call_user_func(). key == 'array_map': will use... array_map(). Use one OR the other for each option
	);

	$functions = apply_filters( 'sf_abt_sanitization_functions', $functions );

	if ( $option )
		return isset($functions[$option]) ? $functions[$option] : array( 'function' => 'esc_attr', 'array_map' => 'esc_attr' );

	return $functions;
}


// !Return the sanitized option
function sf_abt_sanitize_option( $name = '', $value = '', $functions = array() ) {
	if ( !is_array($functions) || empty($functions) )
		$functions = sf_abt_sanitization_functions();

	if ( !is_array($value) && isset($functions[$name]['function']) )
		return call_user_func($functions[$name]['function'], $value);
	if ( is_array($value) && isset($functions[$name]['array_map']) )
		return array_map($functions[$name]['array_map'], $value);
	if ( isset($functions[$name]['function']) )
		return call_user_func($functions[$name]['function'], $value);
	if ( isset($functions[$name]['array_map']) )
		return array_map($functions[$name]['array_map'], $value);
	return esc_attr($value);
}


// !Sanitize and update options
function sf_abt_update_options( $new_values = array() ) {
	$options	= sf_abt_get_options();
	$functions	= sf_abt_sanitization_functions();

	foreach( $options as $name => $value ) {
		$options[$name] = isset($new_values[$name]) ? sf_abt_sanitize_option( $name, $new_values[$name], $functions ) : $value;
	}
	update_option( '_sf_abt', $options );
	return $options;
}


// !Return if a user is in the coworkers list
function sf_abt_is_autorized_coworker( $id = 0 ) {
	$id = $id ? $id : get_current_user_id();
	$coworkers = sf_abt_get_options( 'coworkers' );
	return in_array($id, $coworkers) && user_can($id, 'administrator');
}


// !Return if the coworkers list still contains an administrator
function sf_abt_coworkers_have_admin( $list = array() ) {
	if ( empty($list) ) {
		$opts	= sf_abt_get_options();
		if ( empty($opts['coworkers']) )
			return false;
		$list	= $opts['coworkers'];
	}

	foreach ( $list as $user ) {
		if ( user_can($user, 'administrator') )
			return true;
	}
	return false;
}


// !Return cowork open files
function sf_abt_get_open_files() {
	$open_files	= get_option('sf-abt-open-files');
	$open_files	= is_array($open_files) ? $open_files : array();
	$files		= array();

	if ( count($open_files) ) {
		foreach( $open_files as $file => $user ) {
			$files[esc_attr($file)] = absint($user);
		}
	}
	return $files;
}


// !Return if cowork is enabled for the user
function sf_abt_cowork_enabled( $id = 0 ) {
	$id = $id ? $id : get_current_user_id();
	return (int) get_user_meta($id, 'sf-abt-coworking', true);
}


/*-----------------------------------------------------------------------------------*/
/* !Tools ========================================================================== */
/*-----------------------------------------------------------------------------------*/
/*
 * $var (mixed) variable to print out.
 * $display (bool) When false (default), print a "display: none" in the <pre> style. This way, you'll have to manually remove it (with firebug or any other tool), so you don't bother other users.
 * $display_for_non_logged_in (bool) When false (default), nothing will be printed for logged out users.
 * ex:
 * pre_print_r($var)      : printed only for logged in users, but hidden for everybody (remove the display:hidden by yourself in the page).
 * pre_print_r($var, 1)   : printed only for logged in users, all logged in users can see the code.
 * pre_print_r($var, 0, 1): printed for everybody (logged out users too), but hidden for everybody (remove the display:hidden by yourself in the page).
 * pre_print_r($var, 1, 1): printed for everybody (logged out users too), all users can see the code.
 */
if ( !function_exists('pre_print_r') ) :
function pre_print_r($var, $display = false, $display_for_non_logged_in = false) {
	if ( !$display_for_non_logged_in && !( function_exists('is_user_logged_in') && is_user_logged_in() ) )
		return;

	echo '<pre style="background:rgb(34,34,34);line-height:19px;font-size:14px;color:#fff;text-shadow:none;font-family:monospace;padding:6px 10px;margin:2px;position:relative;z-index:10000;overflow:auto;'.((bool) $display ? '' : 'display:none;').'">';
	if ( (is_string($var) && trim($var) === '') || $var === false || $var === null )
		var_dump($var);
	else
		print_r($var);
	echo '<div style="clear:both"></div></pre>';
}
endif;
/**/