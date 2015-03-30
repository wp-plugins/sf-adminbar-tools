<?php

/*-----------------------------------------------------------------------------------*/
/* !The adminbar items ============================================================= */
/*-----------------------------------------------------------------------------------*/

add_action( 'add_admin_bar_menus', 'sf_abt_admin_bar_menus' );
function sf_abt_admin_bar_menus() {
	if ( !current_user_can('administrator') )
		return;
	add_action( 'admin_bar_menu', 'sf_abt_tools', 0 );
}

function sf_abt_tools( $wp_admin_bar ) {
	global $current_user, $template;
	get_currentuserinfo();
	$open_files	= sf_abt_get_open_files();
	$my_files	= array_keys($open_files, $current_user->ID);
	$cowork		= sf_abt_cowork_enabled( $current_user->ID );

	$my_files_count		= count($my_files);
	$open_files_count	= count($open_files);
	$other_files_count	= $open_files_count - $my_files_count;
	$files_counts		= ($cowork && $my_files_count ? '<span id="used-by-me" class="used-by-me-counter">'.$my_files_count.'</span>' : '')
						 .($cowork && $other_files_count ? '<span id="used-by-other" class="used-by-other-counter">'.$other_files_count.'</span>' : '');

	if ( !defined('DOING_AJAX') || !DOING_AJAX ) {

		do_action('sf_abt_before_add_nodes', $wp_admin_bar);

		// !GROUP LEVEL 0: The main group -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$wp_admin_bar->add_group( array(
			'id'	=> 'sf-abt-tools',
			'meta'	=> array(
				'class' => 'ab-top-secondary',
				),
		) );

		// !ITEM LEVEL 0: The main item (requests and page load time) -------------------------------------------------------------------------------------------------------------------------------------------------
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-tools',
			'id'	=> 'sf-abt-main',
			'title'	=> '<span class="hidden-when-small"><span class="sf-abt-stats">'.sprintf(__('%1$s queries, %2$s s.', 'sf-abt'), get_num_queries(), timer_stop()).'</span>'.$files_counts.'</span>',
			'href'	=> "#",
			'meta'	=> array(
				'title'		=> __("Hide/Unhide the admin bar", 'sf-abt'),
				),
		) );

		// !ITEM LEVEL 1: WP_Query or fix admin menu ------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$scrid		= is_admin() ? __("Fix/unfix admin menu", 'sf-abt') : '$wp_query';
		$scridTitle	= is_admin() ? '' : __('Show $wp_query content', 'sf-abt');
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-main',
			'id'	=> 'sf-abt-menuWpQuery',
			'title'	=> $scrid,
			'href'	=> "#",
			'meta'	=> array(
				'title'		=> $scridTitle,
				),
		) );

		do_action('sf_abt_add_nodes', $wp_admin_bar);

		// !ITEM LEVEL 1: php mem + version, DEBUG, etc ---------------------------------------------------------------------------------------------------------------------------------------------------------------
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-main',
			'id'	=> 'sf-abt-phpmem',
			'title'	=> sprintf(__('php mem.: %1$s/%2$sB (%3$s)', 'sf-abt'), round(memory_get_usage()/1024/1024, 2), WP_MEMORY_LIMIT, PHP_VERSION),
			'meta'	=> array(
				'title'		=> sprintf(__('WP_DEBUG is %1$s, error reporting level is %2$s', 'sf-abt'), (WP_DEBUG ? __('enabled', 'sf-abt') : __('disabled', 'sf-abt')), error2string(error_reporting())),
				),
		) );

		// !ITEM LEVEL 2: Template ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		if ( $template ) {
			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-phpmem',
				'id'	=> 'sf-abt-template',
				'title'	=> str_replace(trailingslashit(TEMPLATEPATH), '', $template ),
				'meta'	=> array(
					'title'		=> __('Template'),
				),
			) );
		}

	}

	// !GROUP LEVEL 1: Coworking with theme and plugins files --------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( is_readable(TEMPLATEPATH) && is_readable(WP_PLUGIN_DIR) ) {

		$files_group = array(
			'id'	=> 'sf-abt-files',
			'meta'	=> array(
				'class' => 'ab-sub-secondary',
				),
		);
		if ( !defined('DOING_AJAX') || !DOING_AJAX )
			$files_group['parent'] = 'sf-abt-main';

		$wp_admin_bar->add_group( $files_group );

		do_action('sf_abt_add_cowork_nodes', $wp_admin_bar, $current_user, $open_files, $my_files, $my_files_count, $other_files_count);

		// !ITEM LEVEL 1: Switch coworking -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-files',
			'id'	=> 'sf-abt-coworking',
			'title'	=> ($cowork ? __("Stop coworking", 'sf-abt') : __("Coworking", 'sf-abt')),
			'href'	=> "#",
		) );

	}

}


if ( !defined( 'XMLRPC_REQUEST' ) && !defined( 'DOING_CRON' ) ) {

	if ( !is_admin() ) {

/*-----------------------------------------------------------------------------------*/
/* !Lightbox in frontend =========================================================== */
/*-----------------------------------------------------------------------------------*/

		add_action('wp_footer', 'sf_abt_lightbox', 999);
		function sf_abt_lightbox() {
			if ( !current_user_can('administrator') || is_admin() )
				return;

			$sf_abt_q = '';
			if ( (isset($_GET['wp_query'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'sf-abt_get-wp-query')) || is_404() ) {
				global $wp_query;
				$sf_abt_q = $wp_query;
				if ( isset($sf_abt_q->queried_object->post_content) && strpos($sf_abt_q->queried_object->post_content, '<div class="sf-abt-post-content">') !== 0 )
					$sf_abt_q->queried_object->post_content = '<div class="sf-abt-post-content">'.$sf_abt_q->queried_object->post_content.'</div>';

				if ( isset($sf_abt_q->post->post_content) && strpos($sf_abt_q->post->post_content, '<div class="sf-abt-post-content">') !== 0 )
					$sf_abt_q->post->post_content = '<div class="sf-abt-post-content">'.$sf_abt_q->post->post_content.'</div>';

				if ( isset($sf_abt_q->posts) && count($sf_abt_q->posts) ) {
					foreach ( $sf_abt_q->posts as $i => $sp_abt_p ) {
						if ( isset($sp_abt_p->post_content) && strpos($sp_abt_p->post_content, '<div class="sf-abt-post-content">') !== 0 ) {
							$sf_abt_q->posts[$i]->post_content = '<div class="sf-abt-post-content">'.$sf_abt_q->posts[$i]->post_content.'</div>';
						}
					}
				}

				if ( $sf_abt_q )
					echo '<div id="sf-abt-pre-wrap"'.(is_404() ? ' class="is-404" style="display:none;"' : '').'><pre id="sf-abt-pre"><h2 id="sf-abt-h2">$wp_query</h2><code id="sf-abt-code">'.print_r($sf_abt_q, true).'</code></pre></div>';
			}
		}

	}

	if ( !defined('DOING_AJAX') ) {

/*-----------------------------------------------------------------------------------*/
/* !CSS and JS ===================================================================== */
/*-----------------------------------------------------------------------------------*/

		add_action('init', 'sf_abt_css_and_js', 999 );
		function sf_abt_css_and_js() {
			if ( !current_user_can('administrator') )
				return;

			$uid = get_current_user_id();
			$cowork	= sf_abt_cowork_enabled( $uid );

			wp_enqueue_style( 'sf-abt', SF_ABT_PLUGIN_URL.'css/style-1.0.min.css', false, null, 'screen');

			$loc = array(
				'ajax_url'				=> admin_url('admin-ajax.php'),
				'imgs_url'				=> SF_ABT_PLUGIN_URL.'images',
				'content_dir'			=> untrailingslashit( WP_CONTENT_DIR ),
				'mid'					=> $uid,
				'cowork'				=> ($cowork ? 'start' : 'stop'),
				'cowork_on'				=> __("Coworking", 'sf-abt'),
				'refresh_cowork'		=> ! (int) get_user_meta(get_current_user_id(),'sf-abt-no-cowork-refresh', true),
				'query_nonce'			=> wp_create_nonce('sf-abt_get-wp-query'),
				'coworking_nonce'		=> wp_create_nonce('sf-abt_enable-coworking'),
				'update_files_none'		=> wp_create_nonce('sf-abt-update-open-files'),
				'close_files_nonce'		=> wp_create_nonce('sf-abt-close-my-open-files'),
				'refresh_files_nonce'	=> wp_create_nonce('sf-abt-refresh-files'),
				'refresh_counters_nonce'=> wp_create_nonce('sf-abt-refresh-counters'),
			);
			wp_enqueue_script( 'sf-abt-script', SF_ABT_PLUGIN_URL.'js/sf-admintools-1.0.min.js', array('jquery'), null, true);
			wp_localize_script('sf-abt-script', 'sfabt', $loc);
		}

	}

}


/*-----------------------------------------------------------------------------------*/
/* !Utilities ====================================================================== */
/*-----------------------------------------------------------------------------------*/

// !Returns the error level as a string
function error2string($value) {
	$level_names = array(
		E_ERROR				=> 'E_ERROR',
		E_WARNING			=> 'E_WARNING',
		E_PARSE				=> 'E_PARSE',
		E_NOTICE			=> 'E_NOTICE',
		E_CORE_ERROR		=> 'E_CORE_ERROR',
		E_CORE_WARNING		=> 'E_CORE_WARNING',
		E_COMPILE_ERROR		=> 'E_COMPILE_ERROR',
		E_COMPILE_WARNING	=> 'E_COMPILE_WARNING',
		E_USER_ERROR		=> 'E_USER_ERROR',
		E_USER_WARNING		=> 'E_USER_WARNING',
		E_USER_NOTICE		=> 'E_USER_NOTICE'
	);
	if ( defined('E_STRICT') )
		$level_names[E_STRICT] = 'E_STRICT';

	$levels = array();
	if ( ($value&E_ALL) == E_ALL ) {
		$levels[] = 'E_ALL';
		$value &=~ E_ALL;
	}
	foreach ( $level_names as $level => $name ) {
		if ( ($value&$level) == $level)
			$levels[] = $name;
	}
	return implode(' | ',$levels);
}



