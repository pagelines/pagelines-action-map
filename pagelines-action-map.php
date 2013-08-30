<?php
/*
Plugin Name: Action Map
Plugin URI: http://www.pagelines.com/
Description: Shows where WordPress and PageLines actions are included in the templates live on the page.
Version: 1.8.2
Author: PageLines
Author URI: http://www.pagelines.com
pagelines: true
Tags: hooks
*/

new Action_Map;

class Action_Map {

	function __construct() {		
		add_action('template_redirect', array( $this, 'pl_actionmap' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'css' ) );		
	}


	function css() {

		wp_enqueue_style( 'action-map', plugins_url( 'style.css', __FILE__ ) );	

	}

	function pl_actionmap() {

		global $wp_admin_bar;
		global $pagelines_template;
		if ( !current_user_can('edit_theme_options') )
    		return;

		$dir = basename( get_template_directory() );
		if ( 'dms' != $dir && 'pagelines' != $dir )
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
		$wp_hooks = $this->wp_hooks();
		$hooks = $this->get_pl_hooks();
		$hooks = ( is_array( $hooks ) ) ? $hooks : array();
		$sections = $this->get_section_hooks();

		$hooks = array_merge( $wp_hooks, $hooks, $sections );
		if ( $status === 'On' )
			foreach ( $hooks as $hook )
				add_action( $hook , create_function( '', 'echo "<div style=\"display:block;\"><span class=\"actionmap\">' . $hook . '</span></div>";') );
    
	}

	function get_pl_hooks() {

		// see if we have hooks already....

		$url = 'api.pagelines.com/dms-updates/hooks.php?api=1';
		if( $hooks = get_transient( 'pagelines_hooks' ) )
			return $hooks;
		$response = pagelines_try_api( $url, false );

		if ( $response !== false ) {
			if( ! is_array( $response ) || ( is_array( $response ) && $response['response']['code'] != 200 ) ) {
				$out = '';
			} else {
				$hooks = wp_remote_retrieve_body( $response );
				set_transient( 'pagelines_hooks', $hooks, 86400 );
				$out = $hooks;
			}
		}
		
	return json_encode( $out );
	}

	function get_section_hooks() {

		global $load_sections;
		$available = $load_sections->pagelines_register_sections( false, true );

		$sections = array();	
		foreach( $available as $type ) {	
			foreach( $type as $key => $data ) {
		
				$sections[] = sprintf( 'pagelines_before_%s', basename( $data['base_dir'] ) );
				$sections[] = sprintf( 'pagelines_inside_bottom_%s', basename( $data['base_dir'] ) );
				$sections[] = sprintf( 'pagelines_after_%s', basename( $data['base_dir'] ) );
				$sections[] = sprintf( 'pagelines_outer_%s', basename( $data['base_dir'] ) );
			}
		}
	return $sections;
	}

	function wp_hooks() {

		return array(
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
	}
}