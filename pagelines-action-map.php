<?php
/*
Plugin Name: Action Map
Plugin URI: http://www.pagelines.com/
Description: Shows where WordPress and PageLines actions are included in the templates live on the page.
Version: 1.5.4
Author: PageLines
Author URI: http://www.pagelines.com
pagelines: true
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

	include( dirname( __FILE__ ) . '/hooks.php' );

		$wp_hooks = array(	
			'wp_head',
			'wp_footer',
			'get_search_form',
			'wp_meta',
			'get_sidebar',
			'dynamic_sidebar',
			'the_post',
			'loop_start',
			'loop_end'
    	);

	return apply_filters('pagelines_hooks', array_merge( $hooks, $wp_hooks ) );
}