<?php
/*
Plugin Name: SF Admin bar tools
Plugin URI: http://www.screenfeed.fr/sf-abt/
Description: Adds some small interesting tools to the admin bar for Developers
Version: 1.0.1
Author: GregLone
Author URI: http://www.screenfeed.fr/greg/
License: GPLv3
Require: WordPress 3.3
*/

define( 'SF_ABT_FILE',			__FILE__ );
define( 'SF_ABT_DIRNAME',		basename( dirname( SF_ABT_FILE ) ) );
define( 'SF_ABT_PLUGIN_URL',	trailingslashit( WP_PLUGIN_URL ) . SF_ABT_DIRNAME );
define( 'SF_ABT_PLUGIN_DIR',	trailingslashit( WP_PLUGIN_DIR ) . SF_ABT_DIRNAME );

/*-----------------------------------------------------------------------------------*/
/* Lang support */
/*-----------------------------------------------------------------------------------*/

add_action( 'init', 'sf_abt_lang_init' );
function sf_abt_lang_init() {
	load_plugin_textdomain( 'sf-abt', false, SF_ABT_DIRNAME . '/languages/' );
}


/*-----------------------------------------------------------------------------------*/
/* Custom Post Type : Archive intro */
/*-----------------------------------------------------------------------------------*/

add_action( 'add_admin_bar_menus', 'sf_abt_admin_bar_menus' );
function sf_abt_admin_bar_menus() {
	if ( !current_user_can('administrator') )
		return;
	add_action( 'admin_bar_menu', 'sf_abt_tools', 200 );
}

function sf_abt_tools( $wp_admin_bar ) {
	$wp_admin_bar->add_group( array(
		'id'	=> 'sf-abt-tools',
		'meta'	=> array(
			'class' => 'ab-top-secondary',
			),
	) );

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-tools',
		'id'	=> 'sf-abt-perfs',
		'title'	=> '<span class="hidden-when-small">'.sprintf(__('%1$s queries, %2$s s.', 'sf-abt'), get_num_queries(), timer_stop()).'</span>',
		'href'	=> "#",
		'meta'	=> array(
			'tabindex'	=> '-1',
			'title'		=> __("Hide/Unhide the admin bar", 'sf-abt'),
			),
	) );

	$scrid		= is_admin() ? __("Fix/unfix admin menu", 'sf-abt') : '$wp_query';
	$scridTitle	= is_admin() ? '' : __('Show $wp_query content', 'sf-abt');
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-perfs',
		'id'	=> 'sf-abt-menuWpQuery',
		'title'	=> $scrid,
		'href'	=> "#",
		'meta'	=> array(
			'tabindex'	=> '-1',
			'title'		=> $scridTitle,
			),
	) );

	if ( is_admin() ) {
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-perfs',
			'id'	=> 'sf-abt-scrid',
			'title'	=> get_current_screen()->id,
		) );
	}
}

add_action('admin_head',	'sf_abt_css', 999 );
add_action('wp_head',		'sf_abt_css', 999 );
function sf_abt_css() {
	if ( !current_user_can('administrator') )
		return; ?>
	<style type="text/css" id="sf-adt-css">
		.admin-bar-small #wpadminbar { min-width: 0; width: auto; left: auto; right: 0; opacity: .7; }
		.admin-bar-small .ab-top-menu:not(#wp-admin-bar-sf-abt-tools), .admin-bar-small .hidden-when-small { display: none; }
		.admin-bar-small #wpadminbar .quicklinks, .admin-bar-small #wpadminbar .quicklinks .ab-top-secondary>li#wp-admin-bar-sf-abt-perfs, .admin-bar-small #wpadminbar .quicklinks .ab-top-secondary>li#wp-admin-bar-sf-abt-perfs>a.ab-item { border-left: none; }
		#wp-admin-bar-sf-abt-perfs.hover>.ab-item, #wp-admin-bar-sf-abt-perfs.hover>.ab-item span { color: #333; text-shadow: none; background: #fff!important; }
		.admin-bar-small #wp-admin-bar-sf-abt-perfs>.ab-item:before { content: ''; width: 0; height: 0; border: solid 8px transparent; border-right-color: #ccc; border-left-width: 0; margin-top: 5px; position: absolute; }
		.admin-bar-small #wp-admin-bar-sf-abt-perfs.hover>.ab-item:before { border-right-color: #373737; }
		.admin-menu-fixed #adminmenuwrap { position: fixed; }
		body.admin-bar-small #adminmenu { padding-top: 0; }
		.admin-menu-fixed #collapse-menu:before, .admin-menu-fixed #collapse-menu:after { content: ""; display: table; }
		.admin-menu-fixed #collapse-menu:after { clear: both; }
		#sf-abt-pre-wrap { position: fixed; z-index: 1000; top: 0; right: 0; bottom: 0; left: 0; background: rgba(255,255,255,.5); cursor: pointer; }
		pre#sf-abt-pre { position: absolute; top: 40px; right: 100px; bottom: 12px; left: 100px; background-color: #191919; border: solid 5px #ccc; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; padding: 6px 0; margin: 0; overflow: auto; cursor: text; -webkit-box-shadow: 0 0 6px 0 rgba(0,0,0,.6); -moz-box-shadow: 0 0 6px 0 rgba(0,0,0,.6); box-shadow: 0 0 6px 0 rgba(0,0,0,.6); }
		h2#sf-abt-h2 { position: fixed; right: 120px; top: 50px; padding: 6px 20px 0 0; margin: 0; color: #00B5D5; cursor: pointer; }
		code#sf-abt-code { display: block; background-image: -webkit-linear-gradient(rgba(255,255,255,.05) 50%, transparent 50%, transparent); background-image: -moz-linear-gradient(rgba(255,255,255,.05) 50%, transparent 50%, transparent); background-image: -ms-linear-gradient(rgba(255,255,255,.1) 50%, transparent 50%, transparent); background-image: -o-linear-gradient(rgba(255,255,255,.1) 50%, transparent 50%, transparent); background-image: linear-gradient(rgba(255,255,255,.05) 50%, transparent 50%, transparent); -webkit-background-size: 100% 36px ; -moz-background-size: 100% 36px ; background-size: 100% 36px; background-position: 0 1px; -webkit-tab-size: 4; -moz-tab-size: 4; -ms-tab-size: 4; -o-tab-size: 4; tab-size: 4; padding: 0 12px; margin: 0; font-size: 13px; line-height: 18px; font-family: Monaco, "Andale Mono", "Courier New", Courier, monospace; color: #ccc; }
		.sf-abt-post-content { background: #373737; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; padding: 6px 12px; margin: 0 40px 0 90px; }
		.sf-abt-post-content .sf-abt-post-content { padding: 0; margin: 0; }
	</style>
	<?php
}

add_action('admin_footer',	'sf_abt_js', 999);
add_action('wp_footer',		'sf_abt_js', 999);
function sf_abt_js() {
	if ( !current_user_can('administrator') )
		return; ?>
	<script type="text/javascript" id="sf-adt-js">
		jQuery(document).ready(function($){
			var $body = $("body");
			// Initial states
			if(window.localStorage) {
				if (localStorage.getItem('smallAdminBar'))		$body.addClass("admin-bar-small");
				if (localStorage.getItem('fixedAdminMenu'))		$body.addClass("admin-menu-fixed");
			}
			// Retract admin bar
			$body.on('click', '#wp-admin-bar-sf-abt-perfs>.ab-item', function(e){
				if($body.hasClass('admin-bar-small')) {
					$body.removeClass("admin-bar-small");
					if(window.localStorage)		localStorage.removeItem('smallAdminBar');
				} else {
					$body.addClass("admin-bar-small");
					if(window.localStorage)		localStorage.setItem('smallAdminBar', 1);
				}
				e.preventDefault();
			});
			// Fix the admin menu
			if($body.hasClass('wp-admin')) {
				$body.on('click', '#wp-admin-bar-sf-abt-menuWpQuery>.ab-item', function(e){
					if($body.hasClass('admin-menu-fixed')) {
						$body.removeClass("admin-menu-fixed");
						if(window.localStorage)		localStorage.removeItem('fixedAdminMenu');
					} else {
						$body.addClass("admin-menu-fixed");
						if(window.localStorage)		localStorage.setItem('fixedAdminMenu', 1);
					}
					e.preventDefault();
				});
			} else {
				// Call $wp_query with ajax
				var $preCont = $('#sf-abt-pre-cont');
				$body.on('click', '#wp-admin-bar-sf-abt-menuWpQuery>.ab-item, #sf-abt-h2', function(e){
					var sep = '?';
					var pageUrl = window.location.href;
					if(pageUrl.indexOf('#')!=-1)	pageUrl = pageUrl.split('#')[0];
					if(pageUrl.indexOf('?')!=-1)	sep = '&';
					$preCont.html('').load(pageUrl+sep+'_wpnonce=<?php echo wp_create_nonce('sf-abt_get-wp-query'); ?>&wp_query=<?php echo rand(0, 1000); ?> #sf-abt-pre-wrap');	console.log(pageUrl+sep+'_wpnonce=<?php echo wp_create_nonce('sf-abt_get-wp-query'); ?>&wp_query=<?php echo rand(0, 1000); ?> #sf-abt-pre-wrap');
					e.preventDefault();
				}).on('click', '#sf-abt-pre-wrap', function(e){		// Close the $wp_query lightbox
					if (e.target===this) {
						$(this).remove();
						e.preventDefault();
					}
				});
			}
		});
	</script>
	<?php
	if (is_admin())
		return;
	if (isset($_GET['wp_query']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'sf-abt_get-wp-query')) {
		global $wp_query;
		$sf_abt_q = '';
		$sf_abt_q = $wp_query;
		if ( isset($sf_abt_q->queried_object->post_content) )
			$sf_abt_q->queried_object->post_content = '<div class="sf-abt-post-content">'.$sf_abt_q->queried_object->post_content.'</div>';

		if ( isset($sf_abt_q->post->post_content) )
			$sf_abt_q->post->post_content = '<div class="sf-abt-post-content">'.$sf_abt_q->post->post_content.'</div>';

		if ( isset($sf_abt_q->posts) && count($sf_abt_q->posts) ) {
			foreach ( $sf_abt_q->posts as $i => $sp_abt_p ) {
				if ( isset($sp_abt_p->post_content) ) {
					$sf_abt_q->posts[$i]->post_content = '<div class="sf-abt-post-content">'.$sf_abt_q->posts[$i]->post_content.'</div>';
				}
			}
		}

		if ( $sf_abt_q )
			echo '<div id="sf-abt-pre-wrap"><pre id="sf-abt-pre"><h2 id="sf-abt-h2">$wp_query</h2><code id="sf-abt-code">'.print_r($sf_abt_q, true).'</code></pre></div>';
	} else
		echo '<div id="sf-abt-pre-cont"></div>';
}













