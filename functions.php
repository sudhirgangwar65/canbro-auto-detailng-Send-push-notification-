<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Direct access forbidden.' ); }

/**
 * Theme Includes
 */
require_once get_template_directory() . '/inc/init.php';
require_once get_template_directory() . '/inc/theme-config.php';
require_once get_template_directory() . '/inc/tgmpa.php';
require_once get_template_directory() . '/inc/template-parts.php';
require_once get_template_directory() . '/inc/theme-welcome.php';

/**
 * Includes template part, allowing to pass variables
 */
if ( !function_exists( 'aqualine_get_template_part' ) ) {

	function aqualine_get_template_part( $slug, $name = null, array $aqualine_params = array() ) {

		/* list of allowable includes */
		$allow = array('tmpl/content-ltx-gallery');

		$slug = $slug;
		if ( ! is_null( $name ) ) {

			$slug .= '-' . $name;
		}

		if (in_array($slug, $allow) AND file_exists(get_template_directory() . '/' . $slug . '.php')) {

			include( get_template_directory() . '/' . $slug . '.php' );
		}
	}
}


/**
 * Generate H1 header
 */
if ( !function_exists( 'aqualine_get_the_h1' ) ) {

	function aqualine_get_the_h1() {

		global $wp_post;
		
		if ( is_home() ) {

			$title = esc_html__( 'All Blog Posts', 'aqualine' );
		} 
			else
		if ( is_front_page() ) {

			$title = esc_html__( 'Home', 'aqualine' );
		}
			else
		if ( is_year() ) {

			$title = sprintf( esc_html__( 'Year Archives: %s', 'aqualine' ), get_the_date( 'Y' ) );
		}
			else				
		if ( is_month() ) {

			$title = sprintf( esc_html__( 'Month Archives: %s', 'aqualine' ), get_the_date( 'F Y' ) );
		}
			else
		if ( is_day() ) {

			$title = sprintf( esc_html__( 'Day Archives: %s', 'aqualine' ), get_the_date() );
		}
			else
		if ( is_category() ) {

			$title = single_cat_title( '', false );
		}
			else
		if ( is_tag() ) {

			$title = sprintf( esc_html__( 'Tag: %s', 'aqualine' ), single_tag_title( '', false ) );
		}
			else
		if ( is_tax() ) {

			$title = single_term_title( '', false );
		}
			else
		if ( is_search() ) {

			$title = sprintf( esc_html__( 'Search Results: %s', 'aqualine' ), get_search_query() );
		} 
			else				
		if ( is_author() ) {

			if ( !empty( get_query_var( 'author_name' ) ) ) {

				$q = get_user_by( 'slug', get_query_var( 'author_name' ) );
			}
				else {

				$q = get_userdata( get_query_var( 'author' ) );
			}

			$title = sprintf( esc_html__( 'Author: %s', 'aqualine' ), $q->display_name );
		} 
			else
		if ( is_post_type_archive() ) {

			$q   = get_queried_object();
			$title = '';
			if ( !empty( $q->labels->all_items ) ) {

				$title = $q->labels->all_items;
			}
		}
			else
		if ( is_attachment() ) {

			$title = sprintf( esc_html__( 'Attachment: %s', 'aqualine' ), get_the_title() );
		}
			else
		if ( is_404() ) {

			$title = esc_html__( '404 Not Found', 'aqualine' );
		}
			else {

			$title = get_the_title();
		}

		return $title;
	}
}

/**
 * Adds custom post type active item in menu
 */
if ( !function_exists( 'aqualine_add_current_nav_class' ) ) {

	function aqualine_add_current_nav_class( $classes, $item ) {

		// Getting the current post details
		global $post, $wp;

		$id = ( isset( $post->ID ) ? get_the_ID() : null );

		if ( isset( $id ) ) {

			// Getting the post type of the current post
			$current_post_type = get_post_type_object( get_post_type( $post->ID ) );
			if (!empty($current_post_type->rewrite['slug'])) {

				$current_post_type_slug = $current_post_type->rewrite['slug'];
			}
				else {

				$current_post_type_slug = '';
			}

			$home_url = parse_url( esc_url( home_url( add_query_arg( array(), $wp->request ) ) ) );
			if (isset($home_url['path'])) {

				$current_url = esc_url( str_replace( '/', '', $home_url['path'] ) );
			}
				else {


				$current_url = esc_url( home_url( '/' ) );
			}

			$menu_slug = strtolower( trim( $item->url ) );

			if ( !empty($current_post_type_slug) && strpos( $menu_slug,$current_post_type_slug ) !== false && $current_url != '#' && $current_url != '' && $current_url === str_replace( '/', '', parse_url( $item->url, PHP_URL_PATH ) ) ) {

				$classes[] = 'current-menu-item';

			}
				else {

				$classes = array_diff( $classes, array( 'current_page_parent' ) );
			}		}

		if ( get_post_type() != 'post' && $item->object_id == get_site_option( 'page_for_posts' ) ) {

			$classes = array_diff( $classes, array( 'current_page_parent' ) );
		}

		return $classes;
	}
}

add_action( 'nav_menu_css_class', 'aqualine_add_current_nav_class', 10, 2 );


/**
 * Manual excerpt generation
 */
if ( !function_exists( 'aqualine_excerpt_set' ) ) {

	function aqualine_excerpt_set() {

		if ( function_exists( 'fw' ) ) {

			$excerpt_set = (int) fw_get_db_settings_option( 'excerpt_masonry_auto' );
		}	
			else {

			$excerpt_set = 150;
		}

		return $excerpt_set; 
	}

	add_filter( 'excerpt_length', 'aqualine_excerpt_set', 999 );
}


if ( !function_exists( 'aqualine_excerpt' ) ) {
	
	function aqualine_excerpt( $content, $excerpt = 0 ) {
		
		global $post;

		$active = get_query_var( 'aqualine_excerpt_activity' );
		if ( $active == 'disable' ) {

			return $content;
		}

		$format = get_post_format($post->ID);

		if ( ! empty( $post->post_content ) &&
			 ! preg_match( '#<!--more-->#', $post->post_content ) &&
			 ! preg_match( '#<!--nextpage-->#', $post->post_content ) &&
			 ! preg_match( '#twitter.com#', $post->post_content ) &&
			 ! preg_match( '#wp-caption#', $post->post_content )
			) {
			$content = aqualine_cut_excerpt( $post->post_content , $excerpt );
		}

		return $content;
	}
}

if ( !function_exists( 'aqualine_cut_excerpt' ) ) {
	
	function aqualine_cut_excerpt( $content = '', $excerpt = 0 ) {

		$cut = false;
		$excerpt_more = apply_filters( 'excerpt_more', '' );
		$content = aqualine_get_content( $content );
		$texts = preg_grep( '#(<[^>]+>)|(<\/[^>]+>)#s', $content, PREG_GREP_INVERT );
		$total_length = count( preg_split( '//u', implode( '', $texts ), - 1, PREG_SPLIT_NO_EMPTY ) );

		if ( function_exists( 'fw' ) ) {

			$excerpt_set = (int) fw_get_db_settings_option( 'excerpt_auto' );

		}
			else {

			$excerpt_set = 0;
		}

		if ( $excerpt_set == 0 ) {

			$excerpt_set = 255;
		}

		$excerpt_sc = get_query_var( 'ltx_sc_excerpt_size' );
		if ( !empty( $excerpt_sc ) ) {

			$excerpt_length = $excerpt_sc;
		}
			else {

			$excerpt_length = (int) apply_filters( 'excerpt_length', $excerpt_set );
		}

		foreach ( $texts as $key => $text ) {

			$text = preg_split( '//u', $text, - 1, PREG_SPLIT_NO_EMPTY );
			$text = array_slice( $text, 0, $excerpt_length );
			$excerpt_length = $excerpt_length - count( $text );
			$cut = $key;

			if ( 0 >= $excerpt_length ) {
				$content[ $key ] = $texts[ $key ] = implode( '', $text );
				break;
			}
		}

		if ( false !== $cut ) {

			array_splice( $content, $cut + 1 );
		}

		$content = aqualine_strip_tags( $texts, $cut );

		$content = implode( ' ', $content );

		$content = preg_replace( '/<\/p>/', '', $content );

		if ( $total_length > $excerpt_length ) {

			$content .= $excerpt_more;
		}

		return wp_kses_post( $content, true );
	}
}

/**
 * Cuts text by the number of characters
 */
if ( !function_exists( 'aqualine_cut_text' ) ) {

	function aqualine_cut_text( $text, $cut = 300, $aft = ' ...' ) {

		if ( empty( $text ) ) {
			return null;
		}

		if ( empty($cut) AND function_exists( 'FW' ) ) {

			$cut = (int) fw_get_db_settings_option( 'excerpt_wc_auto' );
		}

		$text = wp_strip_all_tags( $text, true );
		$text = strip_tags( $text );
		$text = preg_replace( "/<p>|<\/p>|<br>|(( *&nbsp; *)|(\s{2,}))|\\r|\\n/", ' ', $text );
		if ( function_exists('mb_strripos') AND mb_strlen( $text ) > $cut ) {

			$text = mb_substr( $text, 0, $cut, 'UTF-8' );
			return mb_substr( $text, 0, mb_strripos( $text, ' ', 0, 'UTF-8' ), 'UTF-8' ) . $aft;
		}
			else {

			return $text;
		}
	}
}

/**
 * Pregenerates content for excerpt function
 */
if ( !function_exists( 'aqualine_get_content' ) ) {
	
	function aqualine_get_content( $content = '' ) {

		$result = array();

		$content = wptexturize( $content );
		$content = convert_smilies( $content );
		$content = wpautop( $content );
		$content = prepend_attachment( $content );
		$content = strip_shortcodes( $content );
		$content = str_replace( ']]>', ']]&gt;', $content );
		$content = str_replace( array( "\r\n", "\r" ), "\n", $content );
		$content = preg_split( '#(<[^>]+>)|(<\/[^>]+>)#s', trim( $content ), - 1, PREG_SPLIT_DELIM_CAPTURE );
		$content = array_diff( $content, array( "\n", '' ) );
		$content = array_values( $content );

		foreach ( $content as $key => $value ) {

			$result[] = str_replace( array( "\r\n", "\r", "\n" ), '', $value );
		}

		return $result;
	}
}

if ( !function_exists( 'aqualine_strip_tags' ) ) {
	
	function aqualine_strip_tags( $texts = array(), $cut = 0 ) {

		if ( ! is_array( $texts ) ) {
			return $texts;
		}

		$clean = array( '<p>' );

		foreach ( $texts as $key => $value ) {
			if ( $key <= $cut ) {
				$clean[] = $value;
			}
		}

		return $clean;
	}
}

/**
 * Return true|false is woocommerce conditions.
 *
 * @param string $tag
 * @param string|array $attr
 *
 * @return bool
 */
if ( !function_exists( 'aqualine_is_wc' ) ) {

	function aqualine_is_wc($tag, $attr='') {

		if( !class_exists( 'woocommerce' ) ) {
			return false;
		}
		switch ($tag) {

			case 'wc_active':
		        return true;
			
		    case 'woocommerce':
		        if( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		        	return true;
		        }
				break;
		    case 'shop':
		        if( function_exists( 'is_shop' ) && is_shop() ) {
		        	return true;
		       	}
				break;
			case 'product_category':
		        if( function_exists( 'is_product_category' ) && is_product_category($attr) ) {
		        	return true;
		        }
				break;
		    case 'product_tag':
		        if( function_exists( 'is_product_tag' ) && is_product_tag($attr) ) {
		        	return true;
		        }
				break;
		    case 'product':
		    	if( function_exists( 'is_product' ) && is_product() ) {
		    		return true;
		    	}
				break;
		    case 'cart':
		        if( function_exists( 'is_cart' ) && is_cart() ) {
		        	return true;
		        }
				break;
		    case 'checkout':
		        if( function_exists( 'is_checkout' ) && is_checkout() ) {
		        	return true;
		        }
				break;
		    case 'account_page':
		        if( function_exists( 'is_account_page' ) && is_account_page() ) {
		        	return true;
		        }
				break;
		    case 'wc_endpoint_url':
		        if( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url($attr) ) {
		        	return true;
		        }
				break;
		    case 'ajax':
		        if( function_exists( 'is_ajax' ) && is_ajax() ) {
		        	return true;
		        }
				break;
		}

		return false;
	}
}

/**
 *  Return true if Visual Composer installed
 */
if ( !function_exists('aqualine_is_vc') ) {

    function aqualine_is_vc() {

        if ( class_exists('WPBakeryVisualComposerAbstract') ) {

            return true;
        }
        	else {

	        return false;
       	}
    }
}


/**
 * Checking active status of plugin
 */
if ( !function_exists( 'aqualine_plugin_is_active' ) ) {
	
	function aqualine_plugin_is_active( $plugin_var, $plugin_dir = null ) {

		if ( empty( $plugin_dir ) ) {

			$plugin_dir = $plugin_var;
		}

		return in_array( $plugin_dir . '/' . $plugin_var . '.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}
}

/**
 * Adding custom stylesheet to admin
 */
if ( !function_exists( 'aqualine_admin_css' ) ) {
	
	function aqualine_admin_css() {

		wp_enqueue_style( 'aqualine-admin', get_template_directory_uri() . '/assets/css/admin.css', false, '1.0.0' );
	}
}
add_action( 'admin_enqueue_scripts', 'aqualine_admin_css' );

/**
 * Return inverted contrast value of color
 */
if ( !function_exists( 'aqualine_rgb_contrast' ) ) {
	
	function aqualine_rgb_contrast($r, $g, $b) {

		if ($r < 128) {

			return array(255,255,255,0.1);
		}
			else {

			return array(255,255,255,1);
		}
	}
}

/**
 * Lightens/darkens a given colour (hex format), returning the altered colour in hex format.7
 * @param str $hex Colour as hexadecimal (with or without hash);
 * @percent float $percent Decimal ( 0.2 = lighten by 20%(), -0.4 = darken by 40%() )
 * @return str Lightened/Darkend colour as hexadecimal (with hash);
 */
if ( !function_exists( 'aqualine_color_change' ) ) {
	
	function aqualine_color_change( $hex, $percent ) {
		
		$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
		$new_hex = '#';
		
		if ( strlen( $hex ) < 6 ) {

			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}
		
		for ($i = 0; $i < 3; $i++) {

			$dec = hexdec( substr( $hex, $i*2, 2 ) );
			$dec = min( max( 0, $dec + $dec * $percent ), 255 ); 
			$new_hex .= str_pad( dechex( $dec ) , 2, 0, STR_PAD_LEFT );
		}		
		
		return $new_hex;
	}
}

function aqualine_adjust_brightness($hex, $steps) {

    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color   = hexdec($color); // Convert to decimal
        $color   = max(0,min(255,$color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}


/**
 * Return footer widget columns number and hidden widgets array
 * @return array();
 */
if ( !function_exists( 'aqualine_get_footer_cols_num' ) ) {

	function aqualine_get_footer_cols_num() {

		global $wp_query;	

		// Footer columns classes, depends on total columns number
	    $footer_tmpl = array(
	    	4	=>	array(
	    			'col-lg-3 col-md-4 col-sm-6 col-ms-12',
	    			'col-lg-3 col-md-4 col-sm-6 col-ms-12',
	    			'col-lg-3 col-md-4 col-sm-6 col-ms-12',
	    			'col-lg-3 col-md-4 col-sm-6 col-ms-12',
	    		),
	    	3	=>	array(
	    			'col-lg-4 col-md-6 col-sm-12 col-ms-12',
	    			'col-lg-4 col-md-6 col-sm-12 col-ms-12',
	    			'col-lg-4 col-md-6 col-sm-12 col-ms-12',
	    			'col-lg-4 col-md-6 col-sm-12 col-ms-12',
	    		),
	    	2	=>	array(
	    			'col-lg-6 col-md-6 col-sm-12',
	    			'col-lg-6 col-md-6 col-sm-12',
	    			'col-lg-6 col-md-6 col-sm-12',
	    			'col-lg-6 col-md-6 col-sm-12',
	    		),
	    	1	=>	array(
	    			'col-xl-8 col-lg-10 col-md-12 text-align-center ',
	    			'col-xl-8 col-lg-10 col-md-12 text-align-center ',
	    			'col-xl-8 col-lg-10 col-md-12 text-align-center ',
	    			'col-xl-8 col-lg-10 col-md-12 text-align-center ',
	    		),
	    );	

		if ( function_exists( 'FW' ) ) {

			$col_hidden_md = $col_hidden_mobile = $classes = $footer_hide = array();

		    $footer_layout = fw_get_db_post_option( $wp_query->get_queried_object_id(), 'footer-layout' );
		    if ( $footer_layout != 'disabled') {

		    	$footer_cols_num = 0;
		    	for ($x = 1; $x <= 4; $x++) {

		    		if ( !is_active_sidebar( 'footer-' . $x ) ) {

		    			continue;
		    		}

		    		$col_hidden = fw_get_db_settings_option( 'footer_' . $x . '_hide' );
		    		if ( $col_hidden == 'show' ) {

		    			$footer_cols_num++;
		    		}
		    			else {

						$footer_hide[$x] = true;
	    			}

	              	$hide_md = fw_get_db_settings_option( 'footer_' . $x . '_hide_md');
	            	if ( $hide_md == 'hide' ) {

	            		$col_hidden_md[$x] = 'hidden-md';
	            	}    	
	            		else {

						$col_hidden_md[$x] = '';
	           		}

	              	$hide_mobile = fw_get_db_settings_option( 'footer_' . $x . '_hide_mobile');
	            	if ( $hide_mobile == 'hide' ) {

	            		$col_hidden_mobile[$x] = 'hidden-xs hidden-ms hidden-sm';
	            	}    	
	            		else {

						$col_hidden_mobile[$x] = '';
	           		}
	            			
		    	}

		    	for ($x = 1; $x <= 4; $x++) {

		    		if ( !is_active_sidebar( 'footer-' . $x ) ) {

		    			continue;
		    		}		    		

					if ( isset($footer_tmpl[$footer_cols_num][( $x - 1 )]) ) {

		        		$classes[$x] = $footer_tmpl[$footer_cols_num][( $x - 1 )];
		        	}
		        }	
		    }                
		    	else {

		        $footer_cols_num = 0;
		   	}    		

			return array(
				'num'			=>	$footer_cols_num,
				'hidden'		=>	$footer_hide,
				'hidden_md'		=>	$col_hidden_md,
				'hidden_mobile'	=>	$col_hidden_mobile,
				'classes'		=>	$classes,
			);
		}
			else {

			$col_hidden_md = $col_hidden_mobile = $classes = $footer_hide = array();
			$footer_cols_num = 0;

	    	for ($x = 1; $x <= 4; $x++) {

	    		if ( is_active_sidebar( 'footer-' . $x ) ) {

		    		$col_hidden_md[$x] = '';
		    		$col_hidden_mobile[$x] = '';
		    		$footer_cols_num++;
	    		}
	    			else {

	    			$footer_hide[$x] = true;
    			}
	        }	

	        for ($x = 1; $x <= 4; $x++) {

				if ( isset($footer_tmpl[$footer_cols_num][( $x - 1 )]) ) {

	        		$classes[$x] = $footer_tmpl[$footer_cols_num][( $x - 1 )];
	        	}
	        }

			return array(
				'num'			=>	$footer_cols_num,
				'hidden'		=>	$footer_hide,
				'hidden_md'		=>	$col_hidden_md,
				'hidden_mobile'	=>	$col_hidden_mobile,
				'classes'		=>	$classes
			);
		}
	}
}


/**
 * Get current page navbar and reset it to default if non-theme setting
 */
if ( !function_exists( 'aqualine_get_navbar_layout' ) ) {

	function aqualine_get_navbar_layout( $default = null ) {

		global $wp_query;

		$aqualine_theme_config = aqualine_theme_config();

		if ( function_exists('FW')) {

			$navbar_layout_default = fw_get_db_settings_option( 'navbar-default' );
			$navbar_layout_default_force = fw_get_db_settings_option( 'navbar-default-force' );
		}
		if ( empty( $navbar_layout_default ) ) {

			$navbar_layout_default = $default;
		}

		if ( function_exists('FW')) {
		
			$navbar_layout = fw_get_db_post_option( $wp_query->get_queried_object_id(), 'navbar-layout' );
		}

		if ( !empty($navbar_layout) AND $navbar_layout == 'disabled') {

			return 'disabled';
		}
			else
		if ( ( !empty( $navbar_layout) AND empty( $aqualine_theme_config['navbar'][$navbar_layout] ) )
			 OR empty( $navbar_layout )
			 OR $navbar_layout_default_force == 'force' ) {

			$navbar_layout = $navbar_layout_default;
		}
		
		return $navbar_layout;
	}
}

/**
 * Return navbar menu
*/
if ( !function_exists( 'aqualine_get_wp_nav_menu' ) ) {

	function aqualine_get_wp_nav_menu() {

		global $wp_query;

		$location = 'primary';
		$menu_id = null;

		wp_nav_menu(array(

			'theme_location'	=>  $location,
			'menu_class' 		=> 'nav navbar-nav',
			'container'			=> 'ul',
			'link_before' 		=> '<span><span>',     
			'link_after'  		=> '</span></span>'							
		));		
	}
}


/**
 * Returns all Sections
 */
if ( !function_exists( 'aqualine_get_sections' ) ) {

	function aqualine_get_sections() {

		static $list;
		$default = array('top_bar', 'before_footer', 'subscribe');

		if ( empty($list) ) {

			$wp_query = new WP_Query( array(
				'post_type' => 'sections',
			) );

			if ( $wp_query->have_posts() ) {

				while ( $wp_query->have_posts() ) {

					$wp_query->the_post();
				
					$tid = fw_get_db_post_option(get_The_ID(), 'theme_block');

					$list[$tid][get_the_ID()] = get_the_title();

				}
			}
		}

		foreach ( $default as $item ) {

			if ( empty($list[$item]) ) {

				$list[$item] = array();
			}
		}

		return $list;
	}
}

/**
 * Get page header layout
 */
if ( !function_exists( 'aqualine_get_pageheader_layout' ) ) {

	function aqualine_get_pageheader_layout() {

		global $wp_query;

		$pageheader_layout = 'default';
		if ( function_exists( 'FW' ) ) {

			$pageheader_display = fw_get_db_settings_option( 'pageheader-display' );
			if ( $pageheader_display != 'disabled' ) {

				$pageheader_layout = fw_get_db_post_option( $wp_query->get_queried_object_id(), 'header-layout' );
			}
				else {

				$pageheader_layout = $pageheader_display;
			}
		}

		$post_type = get_post_type(get_The_ID());

		if ( isset($page_narrow) AND is_single() AND !aqualine_is_wc('woocommerce') AND ($pageheader_layout == 'default' OR empty($pageheader_layout)) AND $post_type == 'post' ) {

			$pageheader_layout = 'narrow';
		}

		return $pageheader_layout;	
	}
}

/**
 * Get page header class
 */
if ( !function_exists( 'aqualine_get_pageheader_class' ) ) {

	function aqualine_get_pageheader_class() {
		
		$aqualine_header_class = array();
		$aqualine_h1 = aqualine_get_the_h1();

		if ( !empty($aqualine_h1) ) {

			$aqualine_header_class[] = 'header-h1 ';
		}

		if ( function_exists('FW') ) {

			$header_fixed = fw_get_db_settings_option( 'header_fixed' );
			if ( $header_fixed == 'fixed' ) {

				$aqualine_header_class[] = 'header-parallax ';
			}
		}

		if ( function_exists( 'bcn_display' ) && !is_front_page() ) {

			$aqualine_header_class[] = 'hasBreadcrumbs';
		}

		$navbar_layout = 'transparent';
		if ( function_exists( 'FW' ) ) {

			$navbar_layout = aqualine_get_navbar_layout('transparent');
		}

		$aqualine_header_class[] = 'wrapper-navbar-layout-' . $navbar_layout;



		return implode( ' ', $aqualine_header_class );
	}

	function aqualine_get_pageheader_parallax_class() {

		$classes = array();
		$classes[] = 'page-header';

		if ( function_exists('FW') ) {

			$header_fixed = fw_get_db_settings_option( 'header_fixed' );
			if ( $header_fixed == 'fixed' ) {

				$classes[] = 'ltx-bg-parallax-enabled';
			}
		}	

		return implode( ' ', $classes );
	}
}

/**
 * Get page header wrapper class
 */
if ( !function_exists( 'aqualine_get_pageheader_wrapper' ) ) {

	function aqualine_get_pageheader_wrapper() {

		global $wp_query;

		if ( function_exists('FW')) {

			$parallax = fw_get_db_settings_option( 'footer-parallax' );
			$parallax_layout = fw_get_db_post_option( $wp_query->get_queried_object_id(), 'footer-parallax' );

			if ( $parallax == 'enabled' AND $parallax_layout != 'disabled') {

				return 'ltx-footer-parallax';
			}
		}

		return '';
	}
}

/**
 * Bcn first crumb title
 * Used for external plugin Breadcrumb NavXT
 */
if ( function_exists( 'bcn_display' ) ) {

	add_filter('bcn_breadcrumb_title', function($title, $type, $id) {

		if ($type[0] === 'home') {

			$title = esc_html__('Home', 'aqualine');
		}
		return $title;
	}, 42, 3);
}


/**
 * Checks is any sidebar active
 */
if ( !function_exists( 'aqualine_check_active_sidebar' ) ) {

	function aqualine_check_active_sidebar() {

		if ( aqualine_is_wc('woocommerce') || aqualine_is_wc('shop') || aqualine_is_wc('product') ) {

			if ( is_active_sidebar( 'sidebar-wc' ) ) {

				return true;
			}
		}
			else {

			if ( is_active_sidebar( 'sidebar-1' ) ) {

				if ( function_exists('FW') AND is_single() ) {

					$aqualine_sidebar = fw_get_db_settings_option( 'blog_post_sidebar' );
					if ( $aqualine_sidebar != 'hidden' ) {

						return true;
					}
				}
					else
				if ( is_single() ) {

					return false;
				}
					else {

					return true;
				}
			}
		}

		return false;
	}
}


/**
 * Checks WC sidebar position
 */
if ( !function_exists( 'aqualine_get_wc_sidebar_pos' ) ) {

	function aqualine_get_wc_sidebar_pos() {

		if ( aqualine_is_wc('product') ) {

			$aqualine_sidebar = false;
		}
			else {

			$aqualine_sidebar = 'left';
		}

		if ( function_exists( 'FW' ) ) {

			if ( aqualine_is_wc('product') ) {

				$aqualine_sidebar = fw_get_db_settings_option( 'shop_post_sidebar' );
			}	
				else {

				$aqualine_sidebar = fw_get_db_settings_option( 'shop_list_sidebar' );
			}

			if ( $aqualine_sidebar == 'hidden' ) {

				$aqualine_sidebar = false;
			}
		}	

		return $aqualine_sidebar;
	}
}

/**
 * Collecting additional Custom CSS
 */
if ( !function_exists( 'aqualine_custom_css' ) ) {

	function aqualine_custom_css( $css = null ) {

		$custom_css = get_query_var('ltx_custom_css');
		if ( empty($custom_css ) ) {

			$custom_css = '';
		}

		if ( !empty($css) ) {

			$custom_css .= $css;
			set_query_var('ltx_custom_css', $custom_css);
		}

		return $custom_css;
	}	
}

/**
 * Find first http/s in string
 */
if ( !function_exists( 'aqualine_find_http' ) ) {

	function aqualine_find_http( $string ) {

		$reg_exUrl = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";

		if (preg_match($reg_exUrl, $string, $url)) {

			return $url[0];
	    }
	}	
}

/**
 * Adds inline style for futher use
 */
if ( ! function_exists( 'aqualine_add_inline_style' ) ) {

	function aqualine_add_inline_style( $style ) {

		global $aqualine_variables;

		if ( empty( $aqualine_variables ) ) {

			$aqualine_variables = array();
			$aqualine_variables['inline_style'] = '';
		}

		$aqualine_variables['inline_style'] .= $style;

		return true;
	}
}

/**
 * Return stored inline styles
 */
if ( ! function_exists( 'aqualine_get_inline_style' ) ) {

	function aqualine_get_inline_style() {

		global $aqualine_variables;

		if ( !empty($aqualine_variables['inline_style']) ) {

			return $aqualine_variables['inline_style'];
		}
			else {

			return false;
		}
	}
}

/**
 * Display image with srcset and sizes attr
 * 
 */
function aqualine_the_img_srcset( $attachment_id, $sizes_hooks, $sizes_media ) {

	if ( !empty($attachment_id) AND !empty($sizes_hooks) AND !empty($sizes_media) ) {

		$attachment_id = get_post_thumbnail_id();

		foreach ( $sizes_hooks as $hook ) {

			$size = wp_get_attachment_image_src( $attachment_id, $hook );
			$img = wp_get_attachment_image_url( $attachment_id, $hook );

			$srcset[] = $img .' '. $size[1].'w';
		}

		$sizes = array();
		foreach ( $sizes_media as $width => $hook ) {

			$size = wp_get_attachment_image_src( $attachment_id, $hook );
			$sizes[] = '(max-width: '.$width.') '.$size[1].'px';
		}

		$size = wp_get_attachment_image_src( $attachment_id, $sizes_hooks[0] );
		$sizes[] = $size[1].'px';

		$image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true);
		$image = wp_get_attachment_image_url( $attachment_id, $sizes_hooks[0] );

		echo '<img src="'.esc_url($image).'" width="'.esc_attr($size[1]).'" height="'.esc_attr($size[2]).'" alt="'.esc_attr($image_alt).'" 
		srcset="'. esc_attr( implode(',', $srcset)) .'"
		sizes="'. esc_attr( implode(',', $sizes)) .'">';
	}
}

require_once get_template_directory() . '/inc/visualcomposer/visualcomposer.php';

$aqualine_current_scheme =  apply_filters ('aqualine_current_scheme', array());

require "vendor/autoload.php"; // For FCM OAuth token generetor Library
add_action(
	'rest_api_init',
	 function () {
	register_rest_route(
	'api',
	'add_booking',
	array(
	'methods'  => 'POST',
	'callback' => 'add_booking',
	)
	);
	}
	);

function add_booking(WP_REST_Request $request){

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);
	// Create post object
	 $arr_request = json_decode( $request->get_body() );
	 $arr=$arr_request->details;
	$values=array();
	 foreach($arr as $key=>$value){
		$values[$key]=$value;
	 }
	
$my_post = array(

	'post_title'    => wp_strip_all_tags( $arr_request->details->name ),
	'post_type'=>wp_strip_all_tags( 'lt-booking' ),
 );
	//echo $values['booking-date'];
  if(!empty($values['booking-date'])){
    $my_post['post_date']=date('Y-m-d H:i:s', strtotime($values['booking-date']));  
    $my_post['post_date_gmt']=gmdate('Y-m-d H:i:s', strtotime($values['booking-date']));  
    $my_post['post_status']='publish';
    
  }
  	
  if(isset($arr_request->ID)){
	$id=$arr_request->ID;
	$my_post['ID']=$id;
	$post_id = $id;

	global $wpdb;
	// return wp_json_encode( (object) array( 1, 'two' ) );
		$results = $wpdb->get_results("Delete
									   FROM {$wpdb->prefix}postmeta 
									    WHERE meta_key='fw_options' AND post_id=$id "
									);
  }else{
  //return $my_post;
  // Insert the post into the database
  $post_id=wp_insert_post( $my_post );
  updateStatus($post_id);
  $the_post = wp_is_post_revision( $post_id );
	if ( $the_post ) {
		$post_id = $the_post;
	}

	
}
 $meta_key='fw_options';
// $meta_value=array(
// 	"point"=>$arr_request->details->point,
// 	"name"=>$arr_request->details->name,
// 	"phone"=>$arr_request->details->phone,
// 	"email"=>$arr_request->details->email,
// 	"car-type"=>$arr_request->details->car-type,
// 	"tariff"=>$arr_request->details->tariff,
// 	"services"=>$arr_request->details->services,
// 	"price"=>$arr_request->details->price,
// 	"duration"=>$arr_request->details->duration,
// 	"payment"=>$arr_request->details->payment,
// 	"confirmed"=>$arr_request->details->confirmed,
// 	"booking-date"=>$arr_request->details->booking-date,
// 	"custom"=>$arr_request->details->custom

// );



add_post_meta( $post_id, $meta_key, $values, true );
// 	$test = new FCMDPPLGPNTestSendPushNotification;

// 	$test->post_type = wp_strip_all_tags( 'lt-booking' );
// 	$test->ID = $post_id;
// 	$test->post_title =  wp_strip_all_tags( $arr_request->details->name );
// 	$test->post_content = "";
// 	$test->post_excerpt = "";
// 	$test->post_url = "";
	
	$booking_title=wp_strip_all_tags( $arr_request->details->name );
	$device_token=$arr_request->details->device_token;
	$body="A new booking ($booking_title) has been completed";
    sendMessage($device_token,$body);


// $FCMDPPLGPN_Push_Notification_OBJ = new FCMDPPLGPN_Push_Notification();
// $resp=$FCMDPPLGPN_Push_Notification_OBJ->fcmdpplgpn_notification($test, false, false, '');
   //  wp_send_notification($post_id);
	 return [
		'status' => true,
		'message' =>'Done'
		];
}





// Generate Oauth token for Push notification

function getGoogleAccessToken()
{

    $credentialsFilePath = __DIR__.'/canbro-5c12f-firebase-adminsdk-cwxyk-99b74f48ce.json'; //replace this with your actual path and file name
    $client = new \Google_Client();
    $client->setAuthConfig($credentialsFilePath);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    $client->refreshTokenWithAssertion();
    $token = $client->getAccessToken();
    return $token['access_token'];
}


// Push Notification Code

function sendMessage($device_token,$body)
{
    $apiurl = 'https://fcm.googleapis.com/v1/projects/canbro-5c12f/messages:send';

    $headers = [
        'Authorization: Bearer ' . getGoogleAccessToken(),
        'Content-Type: application/json'
    ];

    $notification_tray = [
        'title' => "New Booking",
        'body' => $body,
    ];

    $in_app_module = [
        "title" => "New Booking",
        "body" => $body,
    ];

    // The device token of the recipient
    $device_token = 'cM4BZH2Dxkl6pL-5dRK7X2:APA91bFxeXbwA5qpamRVoqNcEVyiwfwQcHkQUQsQlh6WbdaiNzR6dDZh6llbVNrUqQnlpANLsC5BzPyh3skyAz3Sc2qiCTuEfuJE9IF0O7HPZliQYwjWvhfEFgYObA53-0_wtSWm154H';

    $message = [
        'message' => [
            'token' => $device_token, // Specify the recipient device token here
            'notification' => $notification_tray,
            'data' => $in_app_module,
        ],
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiurl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

    $result = curl_exec($ch);

//    print_r($result);

    if ($result === FALSE) {
        //Failed
        die('Curl failed: ' . curl_error($ch));
    }

    curl_close($ch);
}




























//update the post status
function updateStatus($post_id){
	    global $wpdb;
$new_status = 'publish'; // The new status you want to set

// Update the post status using a raw SQL query
$update_query = $wpdb->prepare("
    UPDATE $wpdb->posts
    SET post_status = %s
    WHERE ID = %d
", $new_status, $post_id);

$wpdb->query($update_query);

	}


add_action(
	'rest_api_init',
	 function () {
	register_rest_route(
	'api',
	'booking_detail',
	array(
	'methods'  => 'POST',
	'callback' => 'booking_detail',
	)
	);
	}
	);
	
	
	
	
	
	

	
	function booking_detail( WP_REST_Request $request ) {
	$arr_request = json_decode( $request->get_body() );
	if(isset($arr_request->ID)){
		$id=$arr_request->ID;
		
	  }else{

		return [
			'status' => true,
			'message' =>'Booking ID is required',
			];
	  }
	global $wpdb;
	// return wp_json_encode( (object) array( 1, 'two' ) );
	
	$wpdb->get_results("UPDATE {$wpdb->prefix}notifications SET status=1 WHERE post_id=$id"
									);
									
		$results = $wpdb->get_results("SELECT *
									   FROM {$wpdb->prefix}posts as p
									   JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id=p.ID
									    WHERE p.post_type='lt-booking' AND meta_key='fw_options' AND p.post_status='publish' AND p.ID=$id "
									);
									if(!$results){
									    	return [
										'status' => false,
										'message' => 'no data found',
										];
									}
									$data=[];
									foreach($results as $rs){
										$datalist=json_decode($rs->meta_value);
										
										$rs->details=get_post_meta( $rs->ID, 'fw_options', true); //($rs->meta_value);
											

										
									}
									return [
										'status' => true,
										'data' => $results[0],
										];
								}
add_action(
	'rest_api_init',
	 function () {
	register_rest_route(
	'api',
	'bookings',
	array(
	'methods'  => 'GET',
	'callback' => 'bookings',
	)
	);
	}
	);
	
	function bookings( WP_REST_Request $request ) {
	$arr_request = json_decode( $request->get_body() );
	global $wpdb;
	// return wp_json_encode( (object) array( 1, 'two' ) );
		$results = $wpdb->get_results("SELECT *
									   FROM {$wpdb->prefix}posts as p
									   JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id=p.ID
									    WHERE p.post_type='lt-booking' AND meta_key='fw_options' AND p.post_status='publish' "
									);
									
									
									$data=[];
									foreach($results as $rs){
										$datalist=json_decode($rs->meta_value);
										
										$rs->details=get_post_meta( $rs->ID, 'fw_options', true); //($rs->meta_value);
									       $rs->details['car-type']=strval($rs->details['car-type']);
                                        $data[]=$rs;
										
									}
									return [
										'status' => true,
										'data' => $data,
										];
								}

add_action(
	'rest_api_init',
	 function () {
	register_rest_route(
	'api',
	'services',
	array(
	'methods'  => 'GET',
	'callback' => 'services',
	)
	);
	}
	);
	
	function services( WP_REST_Request $request ) {
	$arr_request = json_decode( $request->get_body() );
	global $wpdb;
		$results = $wpdb->get_results("SELECT *
									   FROM {$wpdb->prefix}posts WHERE post_type='lt-booking-service' AND post_status='publish'"
									);
		
		return [
			'status' => true,
			'data' => $results,
			];
								}
								add_action(
									'rest_api_init',
									 function () {
									register_rest_route(
									'api',
									'plans',
									array(
									'methods'  => 'GET',
									'callback' => 'plans',
									)
									);
									}
									);
									
									function plans( WP_REST_Request $request ) {
									$arr_request = json_decode( $request->get_body() );
									global $wpdb;
								// 		$results = $wpdb->get_results("SELECT *
								// 									   FROM {$wpdb->prefix}posts WHERE post_type='lt-booking-tariff' AND post_status='publish'"
								// 									);
								// 								
																
																
																
																///strat
										$results = $wpdb->get_results("SELECT *
									   FROM {$wpdb->prefix}posts as p
									   JOIN {$wpdb->prefix}postmeta as pm ON pm.post_id=p.ID
									    WHERE p.post_type='lt-booking-tariff' AND meta_key='fw_options' AND p.post_status='publish' "
									);
																	if(!$results){
									    	return [
										'status' => false,
										'message' => 'no data found',
										];
									}
									$data=[];
									foreach($results as $rs){
										$datalist=json_decode($rs->meta_value);
										
										$details=get_post_meta( $rs->ID, 'fw_options', true); //($rs->meta_value);
										if($details){
											    $rs->post_title=$details['admin-header'];
										}
											    $rs->price=$details['price'];
											    $rs->time=$details['time'];

										
									}
										return [
																	'status' => true,
																'data' => $results,
																	];
									
																///end
									}

								add_action(
									'rest_api_init',
									 function () {
									register_rest_route(
									'api',
									'points',
									array(
									'methods'  => 'GET',
									'callback' => 'points',
									)
									);
									}
									);
									
									function points( WP_REST_Request $request ) {
									$arr_request = json_decode( $request->get_body() );
									global $wpdb;
										$results = $wpdb->get_results("SELECT *
																	   FROM {$wpdb->prefix}posts WHERE post_type='lt-booking-point' AND post_status='publish'"
																	);
																	return [
																		'status' => true,
																		'data' => $results,
																		];
																}


add_action(
	'rest_api_init',
	 function () {
	register_rest_route(
	'api',
	'cars',
	array(
	'methods'  => 'GET',
	'callback' => 'cars',
	)
	);
	}
	);
	
	function cars( WP_REST_Request $request ) {
	$arr_request = json_decode( $request->get_body() );
	global $wpdb;
		$results = $wpdb->get_results("SELECT *
									   FROM {$wpdb->prefix}posts WHERE post_type='lt-booking-car' AND post_status='publish'"
									);
									return [
										'status' => true,
										'data' => $results,
										];
	if ( ! empty( $arr_request->email ) && ! empty( $arr_request->password ) ) {
	// this returns the user ID and other info from the user name.
	$user = get_user_by( 'email', $arr_request->email );
	 
	if ( ! $user ) {
	// if the user name doesn't exist.
	return [
	'status' => 'error',
	'message' => 'Wrong email address.',
	];
	}
	
	// check the user's login with their password.
	if ( ! wp_check_password( $arr_request->password, $user->user_pass, $user->ID ) ) {
	// if the password is incorrect for the specified user.
	return [
	'status' => 'error',
	'message' => 'Wrong password.',
	];
	}
	
	return [
	'status' => 'success',
	'message' => 'User credentials are correct.',
	];
	} else {
	return [
	'status' => 'error',
	'message' => 'Email and password are required.',
	];
	}
	}
	
	//okokokokokokokok
	add_action(
	'rest_api_init',
	 function () {
	register_rest_route(
	'api',
	'delete_booking',
	array(
	'methods'  => 'POST',
	'callback' => 'delete_post',
	)
	);
	}
	);
	
	function delete_post( WP_REST_Request $request) {
	    
	    	$arr_request = json_decode( $request->get_body() );
	    $force_delete = false;
	global $wpdb;
		if(isset($arr_request->ID)){
		$postid=$arr_request->ID;
		
	  }else{

		return [
			'status' => true,
			'message' =>'Booking ID is required',
			];
	  }

	$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts  WHERE post_type='lt-booking' AND ID = %d", $postid ) );

	if ( ! $post ) {
		 	return [
			'status' => true,
			'message' =>'No data found!',
			];
	}

	$post = get_post( $post );




	/**
	 * Filters whether a post deletion should take place.
	 *
	 * @since 4.4.0
	 *
	 * @param WP_Post|false|null $delete       Whether to go forward with deletion.
	 * @param WP_Post            $post         Post object.
	 * @param bool               $force_delete Whether to bypass the Trash.
	 */
	$check = apply_filters( 'pre_delete_post', null, $post, $force_delete );
	if ( null !== $check ) {
		return $check;
	}

	/**
	 * Fires before a post is deleted, at the start of wp_delete_post().
	 *
	 * @since 3.2.0
	 * @since 5.5.0 Added the `$post` parameter.
	 *
	 * @see wp_delete_post()
	 *
	 * @param int     $postid Post ID.
	 * @param WP_Post $post   Post object.
	 */
	do_action( 'before_delete_post', $postid, $post );

	delete_post_meta( $postid, '_wp_trash_meta_status' );
	delete_post_meta( $postid, '_wp_trash_meta_time' );

	wp_delete_object_term_relationships( $postid, get_object_taxonomies( $post->post_type ) );

	$parent_data  = array( 'post_parent' => $post->post_parent );
	$parent_where = array( 'post_parent' => $postid );

	if ( is_post_type_hierarchical( $post->post_type ) ) {
		// Point children of this page to its parent, also clean the cache of affected children.
		$children_query = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_parent = %d AND post_type = %s", $postid, $post->post_type );
		$children       = $wpdb->get_results( $children_query );
		if ( $children ) {
			$wpdb->update( $wpdb->posts, $parent_data, $parent_where + array( 'post_type' => $post->post_type ) );
		}
	}

	// Do raw query. wp_get_post_revisions() is filtered.
	$revision_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'revision'", $postid ) );
	// Use wp_delete_post (via wp_delete_post_revision) again. Ensures any meta/misplaced data gets cleaned up.
	foreach ( $revision_ids as $revision_id ) {
		wp_delete_post_revision( $revision_id );
	}

	// Point all attachments to this post up one level.
	$wpdb->update( $wpdb->posts, $parent_data, $parent_where + array( 'post_type' => 'attachment' ) );

	wp_defer_comment_counting( true );

	$comment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d ORDER BY comment_ID DESC", $postid ) );
	foreach ( $comment_ids as $comment_id ) {
		wp_delete_comment( $comment_id, true );
	}

	wp_defer_comment_counting( false );

	$post_meta_ids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d ", $postid ) );
	foreach ( $post_meta_ids as $mid ) {
		delete_metadata_by_mid( 'post', $mid );
	}

	/**
	 * Fires immediately before a post is deleted from the database.
	 *
	 * @since 1.2.0
	 * @since 5.5.0 Added the `$post` parameter.
	 *
	 * @param int     $postid Post ID.
	 * @param WP_Post $post   Post object.
	 */
	do_action( 'delete_post', $postid, $post );

	$result = $wpdb->delete( $wpdb->posts, array( 'ID' => $postid ) );
	if ( ! $result ) {
		return [
			'status' => false,
			'message' =>'Error while deleting',
			];
	}

	/**
	 * Fires immediately after a post is deleted from the database.
	 *
	 * @since 2.2.0
	 * @since 5.5.0 Added the `$post` parameter.
	 *
	 * @param int     $postid Post ID.
	 * @param WP_Post $post   Post object.
	 */
	do_action( 'deleted_post', $postid, $post );

	clean_post_cache( $post );

	if ( is_post_type_hierarchical( $post->post_type ) && $children ) {
		foreach ( $children as $child ) {
			clean_post_cache( $child );
		}
	}

	wp_clear_scheduled_hook( 'publish_future_post', array( $postid ) );

	/**
	 * Fires after a post is deleted, at the conclusion of wp_delete_post().
	 *
	 * @since 3.2.0
	 * @since 5.5.0 Added the `$post` parameter.
	 *
	 * @see wp_delete_post()
	 *
	 * @param int     $postid Post ID.
	 * @param WP_Post $post   Post object.
	 */
	do_action( 'after_delete_post', $postid, $post );

	return [
			'status' => true,
			'message' =>'Booking deleted successfully',
			];
}

//notification apis
	function notifications( WP_REST_Request $request ) {
	$arr_request = json_decode( $request->get_body() );
	global $wpdb;
	// return wp_json_encode( (object) array( 1, 'two' ) );
		$results = $wpdb->get_results("SELECT *
									   FROM {$wpdb->prefix}notifications"
									);
									
								
								
									return [
										'status' => true,
										'data' => $results,
										
										];
								}

add_action(
	'rest_api_init',
	 function () {
	register_rest_route(
	'api',
	'notifications',
	array(
	'methods'  => 'GET',
	'callback' => 'notifications',
	)
	);
	}
	);

function custom_mime_types($mimes) {
    // Add .pem to the list of mime types
    $mimes['pem'] = 'application/x-x509-ca-cert';
    return $mimes;
}
add_filter('upload_mimes', 'custom_mime_types');


