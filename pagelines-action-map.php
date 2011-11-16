<?php
/*
Plugin Name: Action Map
Plugin URI: http://www.pagelines.com/
Description: Shows where WordPress and PageLines actions are included in the templates live on the page.
Version: 1.5.2
Author: PageLines
Author URI: http://www.pagelines.com
pagelines: pagelines-actionmap
Tags: hooks
*/

add_action('template_redirect', 'pl_actionmap' );

function pl_actionmap() {
  
  global $wp_admin_bar;
  global $pagelines_template;
  if ( !current_user_can('edit_theme_options') )
    return;

  if ( basename( get_template_directory() ) != 'pagelines' )
    return;
    
	if ( !isset( $wp_admin_bar ) )
		return;
	
  if ( isset( $_GET['actionmap'] ) )
    if ( get_transient( 'action_status' ) )
      delete_transient( 'action_status' );
    else
      set_transient( 'action_status', true, 60 );
  
  
  $status = ( get_transient( 'action_status' ) ) ? 'On' : 'Off';

  global $post;

  $url = sprintf( '%1$s%2$s', trailingslashit( site_url() ) . '?', ( $_GET ) ? str_replace( '&actionmap', '', $_SERVER['QUERY_STRING'] ) . '&actionmap' : 'actionmap' );

  $wp_admin_bar->add_menu( array( 'id' => 'actionmap', 'title' => __("ActionMap " . $status, 'pagelines'), 'href' => $url ) );  
  if ( $status === 'On' )
    foreach ( get_hooks_array() as $hook )
      add_action( $hook , create_function( '', 'echo "<div style=\"display:block;\"><span style=\"border: 1px solid red;padding:2px;margin:1px;display:inline-block\">' . $hook . '</span></div>";') );
    
}

function get_hooks_array(){

  $hooks = array(
	'pagelines_after_footer',
	'pagelines_register_sections',
	'pagelines_hook_init',
	'pagelines_code_before_head',
	'pagelines_loop_before_post_content',
	'pagelines_loop_after_post_content',
	'pagelines_before_sidebar_wrap',
	'pagelines_after_sidebar_wrap',
	'pagelines_admin_head',
	'pagelines_before_optionUI',
	'pagelines_section_before_postnav',
	'pagelines_section_after_postnav',
	'pagelines_carousel_list',
	'pagelines_before_twitterbar_text',
	'pagelines_content_before_columns',
	'pagelines_content_before_maincolumn',
	'pagelines_content_before_sidebar1',
	'pagelines_content_after_sidebar1',
	'brandnav_after_brand',
	'brandnav_after_nav',
	'pagelines_feature_before',
	'pagelines_fcontent_before',
	'pagelines_feature_text_top',
	'pagelines_feature_text_bottom',
	'pagelines_fcontent_after',
	'pagelines_feature_media_top',
	'pagelines_feature_after',
	'pagelines_feature_nav_before',
	'pagelines_before_branding_icons',
	'pagelines_branding_icons_start',
	'pagelines_branding_icons_end',
	'pagelines_after_branding_wrap',
	'pagelines_box_inside_bottom',
	'pagelines_before_html',
	'pagelines_head',
	'pagelines_before_site',
	'pagelines_before_page',
	'pagelines_before_header',
	'pagelines_before_main',
	'wp_head',
	'wp_footer',
	'get_search_form',
	'wp_meta',
	'loop_start',
	'loop_end'
    );

  return apply_filters('pagelines_hooks', $hooks);
}