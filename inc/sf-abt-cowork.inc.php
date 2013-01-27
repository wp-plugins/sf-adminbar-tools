<?php

/*-----------------------------------------------------------------------------------*/
/* !Add cowork nodes =============================================================== */
/*-----------------------------------------------------------------------------------*/

add_action('sf_abt_add_cowork_nodes', 'sf_abt_add_cowork_nodes', 10, 6);
function sf_abt_add_cowork_nodes($wp_admin_bar, $current_user, $open_files, $my_files, $my_files_count, $other_files_count) {

	// !ITEM LEVEL 1: Theme files ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-files',
		'id'	=> 'sf-abt-theme',
		'title'	=> sprintf(__('Theme - %s', 'sf-abt'), (function_exists('wp_get_theme') ? wp_get_theme()->name : get_current_theme())),
		'meta'	=> array(
			'class'	=> 'sf-abt-root-directory',
		),
	) );

	// !ITEM LEVEL 1: Plugins files --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-files',
		'id'	=> 'sf-abt-plugins',
		'title'	=> __('Plugins'),
		'meta'	=> array(
			'class'	=> 'sf-abt-root-directory',
		),
	) );

	// !ITEM LEVEL 1: MU Plugins files -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( defined('WPMU_PLUGIN_DIR') && is_readable(WPMU_PLUGIN_DIR) ) {
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-files',
			'id'	=> 'sf-abt-muplugins',
			'title'	=> __('MU Plugins', 'sf-abt'),
			'meta'	=> array(
				'class'	=> 'sf-abt-root-directory',
			),
		) );
	}

	if ( isset($_GET['action']) && $_GET['action'] == 'sf_abt_refresh_files' ) {

		// !ITEMS LEVEL 2: Theme files ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$themepath = trailingslashit( TEMPLATEPATH );
		sf_abt_add_file_nodes( $wp_admin_bar, $themepath, 'sf-abt-theme', $open_files, $current_user );

		// !ITEMS LEVEL 2: Plugins files ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$pluginspath = trailingslashit( WP_PLUGIN_DIR );
		sf_abt_add_file_nodes( $wp_admin_bar, $pluginspath, 'sf-abt-plugins', $open_files, $current_user );

		// !ITEMS LEVEL 2: MU plugins files --------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		if ( defined('WPMU_PLUGIN_DIR') && is_readable(WPMU_PLUGIN_DIR) ) {
			$mupluginspath = trailingslashit( WPMU_PLUGIN_DIR );
			sf_abt_add_file_nodes( $wp_admin_bar, $mupluginspath, 'sf-abt-muplugins', $open_files, $current_user );
		}

	} else {

		// !ITEMS LEVEL 2: Loading theme files -----------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-theme',
			'id'	=> 'sf-abt-themeload',
			'title'	=> __('Loading...', 'sf-abt') . ' <span class="loader">&#160;</span>',
		) );

		// !ITEMS LEVEL 2: Loading plugins files ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$wp_admin_bar->add_node( array(
			'parent'=> 'sf-abt-plugins',
			'id'	=> 'sf-abt-pluginsload',
			'title'	=> __('Loading...', 'sf-abt') . ' <span class="loader">&#160;</span>',
		) );

		// !ITEMS LEVEL 2: Loading MU plugins files -------------------------------------------------------------------------------------------------------------------------------------------------------------------
		if ( defined('WPMU_PLUGIN_DIR') && is_readable(WPMU_PLUGIN_DIR) ) {
			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-muplugins',
				'id'	=> 'sf-abt-mupluginsload',
				'title'	=> __('Loading...', 'sf-abt') . ' <span class="loader">&#160;</span>',
			) );
		}
	}

	// !ITEMS LEVEL 1: List my files ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ( $my_files_count ) {
		$my_avatar		= get_abt_avatar($current_user->ID, $current_user->display_name);
		$image_exts		= array( 'jpg', 'jpeg', 'png', 'bmp', 'gif', 'tiff', 'ico' );

		foreach ( $my_files as $file ) {
			$relative_file	= w3p_ltrim_word($file, WP_CONTENT_DIR);
			$preview		= w3p_is_fileext($file, $image_exts) ? '<span class="sf-abt-imgpop"><img alt="" src="'.content_url($relative_file).'"/></span>' : '';

			$wp_admin_bar->add_node( array(
				'parent'=> 'sf-abt-files',
				'id'	=> 'sf-abt-th_or_pl-'.sanitize_html_class( $relative_file ),
				'title'	=> $preview . $my_avatar . basename( $file ),
				'href'	=> "#",
				'meta'	=> array(
							'class'	=> 'abt-file used-by-me',
							'title'	=> $relative_file,
						),
			) );
		}
	}

	// !ITEM LEVEL 1: Clear button ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$files_counts = ($my_files_count ? '<span id="used-by-me-small" class="hidden-when-not-small used-by-me-counter">'.$my_files_count.'</span>' : '')
				   .($other_files_count ? '<span id="used-by-other-small" class="hidden-when-not-small used-by-other-counter">'.$other_files_count.'</span>' : '');

	$wp_admin_bar->add_node( array(
		'parent'=> 'sf-abt-files',
		'id'	=> 'sf-abt-close-files',
		'title'	=> '<span class="sf-abt-stats">'.__("Clear my list", 'sf-abt').'</span>'.$files_counts,
		'href'	=> "#",
	) );
}


/*-----------------------------------------------------------------------------------*/
/* !Utilities ====================================================================== */
/*-----------------------------------------------------------------------------------*/

// !Creates files tree
function sf_abt_add_file_nodes( $wp_admin_bar, $path, $parentnode, $open_files = false, $current_user = 0 ) {
	$dirs = w3p_glob( $path, '*', 1 );
	if ( $dirs && count($dirs) ) {				// Only directories: make them appear on top
		foreach ( $dirs as $dir ) {
			$dname = basename( $dir );
			if ( $dname == '@eaDir' )	// Special directories on Synology NAS
				continue;
			$did   = $parentnode.'-'.sanitize_html_class( $dname );
			$wp_admin_bar->add_node( array(
				'parent'=> $parentnode,
				'id'	=> $did,
				'title'	=> $dname,
			) );

			$subpath = trailingslashit( $dir );
			sf_abt_add_file_nodes( $wp_admin_bar, $subpath, $did, $open_files, $current_user );
		}
	}

	$files			= w3p_glob( $path, '*', 2 );			// Only files now
	$image_exts		= array( 'jpg', 'jpeg', 'png', 'bmp', 'gif', 'tiff', 'ico' );

	if ( $files && count($files) ) {
		foreach ( $files as $a => $file ) {
			$fname			= basename( $file );
			$fid			= $parentnode.'-'.sanitize_html_class( $fname );
			$relative_file	= w3p_ltrim_word($file, WP_CONTENT_DIR);
			$files_args = array(
				'parent'=> $parentnode,
				'id'	=> $fid,
				'href'	=> "#",
				'title'	=> $fname,
				'meta'	=> array(
							'class'	=> 'abt-file',
							'title'	=> $relative_file,
						),
			);

			// The file is open by someone
			if ( isset($open_files[$file]) && $open_files[$file] ) {
				$uid = (int) $open_files[$file];

				if ( $uid == $current_user->ID ) {
					$username = esc_attr( $current_user->display_name );
					$files_args['meta']['class'] .= ' used-by-me';
				} else {
					$username	= esc_attr( get_userdata($uid)->display_name );
					$avatar		= get_abt_avatar($uid, $username);
					$files_args['title'] = $avatar . $files_args['title'];
					$files_args['meta']['class'] .= ' used-by-other';
				}
			}

			// If it's an image, add a preview
			if ( w3p_is_fileext($file, $image_exts) ) {
				$files_args['title'] = '<span class="sf-abt-imgpop"><img alt="" src="'.content_url($relative_file).'"/></span>' . $files_args['title'];
			}

			$wp_admin_bar->add_node( $files_args );
		}
	}
}


// !Return a defult avatar if they're not enabled
function get_abt_avatar($uid, $username) {
	$avatar = get_avatar($uid, 48, '');
	return $avatar ? str_replace('<img ', '<img title="'.$username.'" ', $avatar) : '<span class="avatar avatar-48" title="'.$username.'">?</span>';
}


// !Advanced glob (by Julio Potier: @BoiteAWeb) Thx dude!
if( !function_exists( 'w3p_glob' ) ) {
	function w3p_glob( $paths, $exts = '*', $filter = 0, $forbidden = NULL, $return_type = 1, $safe_return = false, $security_check = 1 )
	{
		if( !$paths ) return false;
		// Arrays used below by filter_var()
		$filters = array( '__return_true', 'is_dir', 'is_file' );
		$filters_options = array( 'options' => array( 'default' => 0, 'min_range' => 0, 'max_range' => count( $filters ) - 1 ) );
		$security_checks = array( '__return_true', 'file_exists', 'is_readable', 'is_writable' );
		$security_options = array( 'options' => array( 'default' => 1, 'min_range' => 0, 'max_range' => count( $security_checks ) - 1 ) );
		$return_type_options = array( 'options' => array( 'default' => 1, 'min_range' => 0, 'max_range' => 2 ) );
		// Validation and init
		$paths = (array)$paths;
		$forbidden = (array)$forbidden;
		$filter = filter_var( $filter, FILTER_VALIDATE_INT, $filters_options );
		$filter = $filters[$filter];
		$return_type = filter_var( $return_type, FILTER_VALIDATE_INT, $return_type_options );
		$security_check = filter_var( $security_check, FILTER_VALIDATE_INT, $security_options );
		$security_check = $security_checks[$security_check];
		// Play with extension
		if( $exts && $exts != '*' ) {
			$exts = is_array( $exts ) ? $exts : explode( ',', $exts );
			// Extra function to add shortcut for extensions
			if (!function_exists('shortcut_exts')) {
				function shortcut_exts( $ext )
				{
					$shortcut_exts = array	( // from http://www.fileinfo.com/
										'@pic' => 'jpg,jpeg,jpe,png,bmp,gif,tiff,tif',
										'@php' => 'php,php3,php4,php5,php6'
										);
					$shortcut_exts = apply_filters( 'w3p_shortcut_exts', $shortcut_exts ); // Filter hook
					if( isset( $shortcut_exts[$ext] ) ) $ext = $shortcut_exts[$ext];
					return trim( preg_replace( '/[^A-Za-z0-9\_\*\-\+\.\~\,]/', '', $ext ), '.' ); // We keep dots but we trim them.
				}
			}
			$exts = array_map( 'shortcut_exts', $exts );
			$exts = '*.{' . implode( ',', $exts ) . '}';
		}else{
			$exts = '*';
		}
		$GLOBALS['w3p_exts'] = $exts; // add extensions to globals to use it in the anonymous function below
		$paths = array_map( create_function( '$dir', 'return trailingslashit( ( is_file( $dir ) ? dirname( $dir ) : $dir ) ) . $GLOBALS["w3p_exts"];' ), $paths );
		$paths = implode( ',', $paths );
		$files = (array)glob( '{' . $paths . '}', GLOB_BRACE );
		if( count( $files ) )
			$files = array_filter( $files, $filter );
		// Check for forbidden words
		if( count( $files ) && $forbidden ) {
			$GLOBALS['w3p_fordidden'] = $forbidden; // add forbidden to globals to use it in the anonymous function below
			$files = array_map( create_function( '$file', 'return str_replace( $forbidden, "", basename( $file ) ) == basename( $file );' ), $files );
		}
		// Security checks
		if( count( $files ) && $security_check != '__return_true' ) {
			$files = array_filter( $files, $security_check );
			$files = array_filter( $files, create_function( '$file', 'return str_replace( array( "%00", "\00" ), "", basename( $file ) ) == basename( $file );' ) );
		}
		if( count( $files ) ) {
			switch( $return_type ) {
				case 0 : $files = array_map( 'basename', $files ); break;
				case 1 : break;
				case 2 : $files = array_map( create_function( '$file', 'return str_replace( $_SERVER["DOCUMENT_ROOT"], site_url(), $file );' ), $files ); break;
			}
			if( $safe_return ) $files = array_map( 'esc_attr', $files );
		}
		return count($files) ? array_values($files) : false;
	}
}

if( !function_exists( 'w3p_extract_fileext' ) ) {
	function w3p_extract_fileext( $file, $ignoreCase = true )
	{
		$ext = pathinfo( sanitize_file_name( basename( $file ) ), PATHINFO_EXTENSION );
		return $ignoreCase ? strtolower( $ext ) : $ext;
	}
}


// !Check a file extension (by Julio Potier: @BoiteAWeb)
if( !function_exists( 'w3p_is_fileext' ) ) {
	function w3p_is_fileext( $file, $ext, $ignoreCase = true )
	{
		if( $ignoreCase ) {
			$ext = array_map( 'strtolower', (array)$ext );
		}
		return in_array( w3p_extract_fileext( $file, $ignoreCase ), (array)$ext );
	}
}


// !Real "word" ltrim
if( !function_exists( 'w3p_ltrim_word' ) ) {
	function w3p_ltrim_word( $text, $remove = ' ' ) {
		$text_arr = explode($remove, $text);
		if ( count($text_arr) == 1 )
			return $text_arr[0];

		for ( $i=0; $i < count($text_arr); $i++ ) {
			if ( $text_arr[$i] == '' )
				unset($text_arr[$i]);
			else
				break;
		}

		return implode($remove, $text_arr);
	}
}