<?php
/*
 * Plugin Name: SF Admin bar tools
 * Plugin URI: http://scri.in/sf-abt2/
 * Description: Adds some small interesting tools to the admin bar for developers
 * Version: 2.0
 * Author: GregLone
 * Author URI: http://www.screenfeed.fr/greg/
 * License: GPLv2+
 * Text Domain: sf-abt
 * Domain Path: /languages/
*/

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
/* !Init =========================================================================== */
/*-----------------------------------------------------------------------------------*/

add_action( 'plugins_loaded', 'sf_abt_include_cowork' );
function sf_abt_include_cowork() {

	if ( is_admin() && !defined('DOING_AJAX') )
		include(SF_ABT_PLUGIN_DIR.'/inc/sf-abt-admin.inc.php');						// !Plugin page

	global $current_user;
	get_currentuserinfo();

	if ( !sf_abt_is_autorized_coworker($current_user->ID) )
		return;

	$cowork  = (int) get_user_meta($current_user->ID, 'sf-abt-coworking', true);
	if ( $cowork )
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

// !Returns an array of all coworkers
function sf_abt_get_coworkers() {
	$options = get_option( '_sf_abt' );
	return is_array($options) && isset($options['coworkers']) && is_array($options['coworkers']) ? wp_parse_id_list($options['coworkers']) : array();
}


// !Returns if a user is in the coworkers list
function sf_abt_is_autorized_coworker($id) {
	$coworkers = sf_abt_get_coworkers();
	return in_array($id, $coworkers) && user_can($id, 'administrator');
}
