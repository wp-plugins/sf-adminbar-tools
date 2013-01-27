<?php

/*-----------------------------------------------------------------------------------*/
/* !Add admin nodes ================================================================ */
/*-----------------------------------------------------------------------------------*/

add_action('sf_abt_add_nodes', 'sf_abt_add_admin_nodes');
function sf_abt_add_admin_nodes($wp_admin_bar) {
	global $hook_suffix, $pagenow, $typenow, $taxnow, $plugin_page, $page_hook, $current_screen;

	// !ITEM LEVEL 1: Current screen -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-main',
		'id'	=> 'sf-abt-screen',
		'title'	=> __('Current screen', 'sf-abt'),
	) );

	// !ITEM LEVEL 2: Admin init hooks -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-screen',
		'id'	=> 'sf-abt-load-hook',
		'title'	=> __('Admin init hooks', 'sf-abt'),
	) );

	// !ITEMS LEVEL 3: Admin init hooks ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$act_hook_class_bef	= 'has-intel sf-abt-before-headers';
	$act_hook_title_bef	= __('Before headers', 'sf-abt');
	$act_hook_class_aft	= 'has-intel sf-abt-after-headers';
	$act_hook_title_aft	= __('After headers', 'sf-abt');
	$act_hook_class		= 'has-intel sf-abt-after-footer';
	$act_hook_title		= __('After footer', 'sf-abt');
	$has_intel_class	= 'has-intel';

	if ( is_network_admin() )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load_menu',
			'title'	=> sf_abt_get_action_num('_network_admin_menu'),
			'meta'	=> array(
				'class' => $act_hook_class_bef,
				'title' => $act_hook_title_bef
			),
		) );

	elseif ( is_user_admin() )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load_menu',
			'title'	=> sf_abt_get_action_num('_user_admin_menu'),
			'meta'	=> array(
				'class' => $act_hook_class_bef,
				'title' => $act_hook_title_bef
			),
		) );

	else
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load_menu',
			'title'	=> sf_abt_get_action_num('_admin_menu'),
			'meta'	=> array(
				'class' => $act_hook_class_bef,
				'title' => $act_hook_title_bef
			),
		) );

	if ( is_network_admin() )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load-menu',
			'title'	=> sf_abt_get_action_num('network_admin_menu'),
			'meta'	=> array(
				'class' => $act_hook_class_bef,
				'title' => $act_hook_title_bef
			),
		) );

	elseif ( is_user_admin() )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load-menu',
			'title'	=> sf_abt_get_action_num('user_admin_menu'),
			'meta'	=> array(
				'class' => $act_hook_class_bef,
				'title' => $act_hook_title_bef
			),
		) );

	else
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load-menu',
			'title'	=> sf_abt_get_action_num('admin_menu'),
			'meta'	=> array(
				'class' => $act_hook_class_bef,
				'title' => $act_hook_title_bef
			),
		) );

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-load-hook',
		'id'	=> 'sf-abt-load-init',
		'title'	=> sf_abt_get_action_num('admin_init'),
		'meta'	=> array(
			'class' => $act_hook_class_bef,
			'title' => $act_hook_title_bef
		),
	) );

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-load-hook',
		'id'	=> 'sf-abt-load-curScreen',
		'title'	=> sf_abt_get_action_num('current_screen'),
		'meta'	=> array(
			'class' => $act_hook_class_bef,
			'title' => $act_hook_title_bef
		),
	) );

	if ( isset($plugin_page) && $plugin_page ) {

		if ( $page_hook ) {

			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-load-hook',
				'id'	=> 'sf-abt-load-hook-plugin-beforeh',
				'title'	=> sf_abt_get_action_num('load-'.$page_hook),
				'meta'	=> array(
					'class' => $act_hook_class_bef,
					'title' => $act_hook_title_bef
				),
			) );

			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-load-hook',
				'id'	=> 'sf-abt-load-hook-plugin-afterh',
				'title'	=> sf_abt_get_action_num($page_hook),
				'meta'	=> array(
					'class' => $act_hook_class_aft,
					'title' => $act_hook_title_aft
				),
			) );

		} else {

			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-load-hook',
				'id'	=> 'sf-abt-load-hook-plugin-page',
				'title'	=> sf_abt_get_action_num('load-'.$plugin_page),
				'meta'	=> array(
					'class' => $act_hook_class_bef,
					'title' => $act_hook_title_bef
				),
			) );

		}

	} else if ( !isset($_GET['import']) ) {

		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load-hook-pagenow',
			'title'	=> sf_abt_get_action_num('load-'.$pagenow),
			'meta'	=> array(
				'class' => $act_hook_class_bef,
				'title' => $act_hook_title_bef
			),
		) );

		if ( $typenow == 'page' ) {

			if ( $pagenow == 'post-new.php' )
				$wp_admin_bar->add_node( array(
					'parent'=> 'sf-abt-load-hook',
					'id'	=> 'sf-abt-load-hook-pagenow-old',
					'title'	=> sf_abt_get_action_num('load-page-new.php'),
					'meta'	=> array(
						'class' => $act_hook_class_bef,
						'title' => $act_hook_title_bef
					),
				) );
			elseif ( $pagenow == 'post.php' )
				$wp_admin_bar->add_node( array(
					'parent'=> 'sf-abt-load-hook',
					'id'	=> 'sf-abt-load-hook-pagenow-old',
					'title'	=> sf_abt_get_action_num('load-page.php'),
					'meta'	=> array(
						'class' => $act_hook_class_bef,
						'title' => $act_hook_title_bef
					),
				) );

		} elseif ( $pagenow == 'edit-tags.php' ) {

			if ( $taxnow == 'category' )
				$wp_admin_bar->add_node( array(
					'parent'=> 'sf-abt-load-hook',
					'id'	=> 'sf-abt-load-hook-pagenow-old',
					'title'	=> sf_abt_get_action_num('load-categories.php'),
					'meta'	=> array(
						'class' => $act_hook_class_bef,
						'title' => $act_hook_title_bef
					),
				) );
			elseif ( $taxnow == 'link_category' )
				$wp_admin_bar->add_node( array(
					'parent'=> 'sf-abt-load-hook',
					'id'	=> 'sf-abt-load-hook-pagenow-old',
					'title'	=> sf_abt_get_action_num('load-edit-link-categories.php'),
					'meta'	=> array(
						'class' => $act_hook_class_bef,
						'title' => $act_hook_title_bef
					),
				) );

		}

		$act_hook_class = $act_hook_class_bef;
		$act_hook_title = $act_hook_title_bef;

	}

	// !ITEM LEVEL 3: Admin init hook for $_REQUEST['action'] ------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( !empty($_REQUEST['action']) ) {

		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-load-hook',
			'id'	=> 'sf-abt-load-hook-action',
			'title'	=> sf_abt_get_action_num('admin_action_'.esc_attr($_REQUEST['action'])),
			'meta'	=> array(
				'class' => $act_hook_class,
				'title' => $act_hook_title
			),
		) );

	}

	// !ITEM LEVEL 2: Admin head hooks -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-screen',
		'id'	=> 'sf-abt-head-hook',
		'title'	=> __('Admin head hooks', 'sf-abt'),
	) );

	// !ITEMS LEVEL 3: Admin head hooks ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-head-hook',
		'id'	=> 'sf-abt-hook-enq-styles',
		'title'	=> sf_abt_get_action_num('admin_enqueue_scripts', array('$hook_suffix' => $hook_suffix)),
		'meta'	=> array(
			'class' => $has_intel_class
		),
	) );

	if ( $hook_suffix )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-head-hook',
			'id'	=> 'sf-abt-hook-styles-suffix',
			'title'	=> sf_abt_get_action_num('admin_print_styles-'.$hook_suffix),
			'meta'	=> array(
				'class' => $has_intel_class
			),
		) );

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-head-hook',
		'id'	=> 'sf-abt-hook-styles',
		'title'	=> sf_abt_get_action_num('admin_print_styles'),
		'meta'	=> array(
			'class' => $has_intel_class
		),
	) );

	if ( $hook_suffix )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-head-hook',
			'id'	=> 'sf-abt-hook-scripts-suffix',
			'title'	=> sf_abt_get_action_num('admin_print_scripts-'.$hook_suffix),
			'meta'	=> array(
				'class' => $has_intel_class
			),
		) );

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-head-hook',
		'id'	=> 'sf-abt-hook-scripts',
		'title'	=> sf_abt_get_action_num('admin_print_scripts'),
		'meta'	=> array(
			'class' => $has_intel_class
		),
	) );

	if ( $hook_suffix )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-head-hook',
			'id'	=> 'sf-abt-hook-head-suffix',
			'title'	=> sf_abt_get_action_num('admin_head-'.$hook_suffix),
			'meta'	=> array(
				'class' => $has_intel_class
			),
		) );

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-head-hook',
		'id'	=> 'sf-abt-hook-head',
		'title'	=> sf_abt_get_action_num('admin_head'),
		'meta'	=> array(
			'class' => $has_intel_class
		),
	) );

	// !ITEM LEVEL 2: Admin footer hooks -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-screen',
		'id'	=> 'sf-abt-foot-hook',
		'title'	=> __('Admin footer hooks', 'sf-abt'),
	) );

	// !ITEMS LEVEL 3: Admin footer hooks ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-foot-hook',
		'id'	=> 'sf-abt-foot-hook-simple',
		'title'	=> sf_abt_get_action_num('admin_footer'),
		'meta'	=> array(
			'class' => $has_intel_class
		),
	) );

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-foot-hook',
		'id'	=> 'sf-abt-foot-hook-scripts',
		'title'	=> sf_abt_get_action_num('admin_print_footer_scripts'),
		'meta'	=> array(
			'class' => $has_intel_class
		),
	) );

	if ( $hook_suffix )
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-foot-hook',
			'id'	=> 'sf-abt-foot-hook-suffix',
			'title'	=> sf_abt_get_action_num('admin_footer-'.$hook_suffix),
			'meta'	=> array(
				'class' => $has_intel_class
			),
		) );

	// !ITEM LEVEL 2: $...now ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( $pagenow || $typenow || $taxnow ) {

		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-screen',
			'id'	=> 'sf-abt-now',
			'title'	=> '$...now',
		) );

		// !ITEMS LEVEL 3: $...now ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		if ( $pagenow )
			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-now',
				'id'	=> 'sf-abt-now-page',
				'title'	=> sprintf(__('%1$s: %2$s', 'sf-abt'), '$pagenow', $pagenow),
			) );

		if ( $typenow )
			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-now',
				'id'	=> 'sf-abt-now-type',
				'title'	=> sprintf(__('%1$s: %2$s', 'sf-abt'), '$typenow', $typenow),
			) );

		if ( $taxnow )
			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-now',
				'id'	=> 'sf-abt-now-tax',
				'title'	=> sprintf(__('%1$s: %2$s', 'sf-abt'), '$taxnow', $taxnow),
			) );

	}

	// !ITEMS LEVEL 2: Current screen id, base, etc -----------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( is_object($current_screen) && !empty($current_screen) ) {

		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-screen',
			'id'	=> 'sf-abt-screenid',
			'title'	=> sprintf(__('%1$s: %2$s', 'sf-abt'), 'id', $current_screen->id),
		) );

		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-screen',
			'id'	=> 'sf-abt-screenbase',
			'title'	=> sprintf(__('%1$s: %2$s', 'sf-abt'), 'base', $current_screen->base),
		) );

		if ( $current_screen->parent_base )
			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-screen',
				'id'	=> 'sf-abt-screenpbase',
				'title'	=> sprintf(__('%1$s: %2$s', 'sf-abt'), 'parent_base', $current_screen->parent_base),
			) );

		if ( $current_screen->parent_file )
			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-screen',
				'id'	=> 'sf-abt-screenpfile',
				'title'	=> sprintf(__('%1$s: %2$s', 'sf-abt'), 'parent_file', $current_screen->parent_file),
			) );

	}
}


/*-----------------------------------------------------------------------------------*/
/* !Utilities ====================================================================== */
/*-----------------------------------------------------------------------------------*/

// !Get the number of times an action has been ran (ok, I'm not really sure this sentence is in english)
function sf_abt_get_action_num( $hook, $params = false ) {
	global $wp_actions;
	$num = isset($wp_actions[$hook]) ? (int) $wp_actions[$hook] : '&times;';
	$len = ceil( min( 50, strlen($hook) ) * (strlen($hook) < 14 ? 7.6 : 7) );

	$params_span = $data_attr = '';
	if ( is_array($params) && $nbr_params = count($params) ) {
		$params_txt = $values_txt = '';
		foreach ( $params as $param => $val ) {
			$params_txt .= $param . ', ';
			$values_txt .= $val . ', ';
		}
		$data_attr = $nbr_params > 1 ? ' data-nbrparams="'.$nbr_params.'"' : '';
		//$params_span = ' <span class="no-adminbar-style" title="'.sprintf(_n('Parameter: %s', 'Parameters: %s', $nbr_params, 'sf-abt'), trim($params_txt, ', ')).'">('.trim($values_txt, ', ').')</span>';
		$params_span = ' <span class="action-indic" title="'.sprintf(_n('Parameter: %1$s (%2$s)', 'Parameters: %1$s (%2$s)', $nbr_params, 'sf-abt'), trim($params_txt, ', '), trim($values_txt, ', ')).'"><span class="action-count">P</span></span>';
	}

	return '<span class="action-indic"><span class="action-count">' . $num . '</span></span>'.$params_span.' <input class="no-adminbar-style" type="text" style="width:'.$len.'px" readonly="readonly" autocomplete="off" value="' . $hook . '"'.$data_attr.'/>';
}
