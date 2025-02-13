<?php
/*
Plugin Name: WP Adminbar Menus
Plugin URI: http://afzalmultani.com/
Description: This is a simple plugin for adding custom nav menus to your WordPress Adminbar/Toolbar..
Version: 1.0
* Author: Afzal Multani
* Author URI: http://www.afzalmultani.com/
* Author Email: multaniafzal30@gmail.com
* adminbarmenus_License: GPLv2 or later
*/
$version='1.0';
global $wp_version;

// Add meta links  -------------->
function adminbarmenus_meta_links( $links, $file )
{
	$plugin = plugin_basename(plugin_dir_path(__FILE__).'index.php');
	if ($file == $plugin) {
		$links[] = '<a href="http://afzalmultani.com/" target="_blank"><span class="dashicons dashicons-sos"></span>' . __('Support', 'am'  ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'adminbarmenus_meta_links', 10, 2 );



function adminbarmenus_main_site(){
	if(is_multisite())
	{
		if(is_main_site()){
			return true;
		}else{
			return false;
		}
	}else{
		return true;
	}
}
function adminbarmenus_is_s_admin(){
	global $userdata;
	if(is_user_logged_in()){
		if(function_exists('get_super_admins') && is_multisite() ){
			if(@in_array($userdata->user_login,get_super_admins()) ){
				return true;
			}else{
				return false;
			}
		}else if(@in_array('administrator',$userdata->roles)  && ($userdata->caps['administrator']==1) ){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

function adminbarmenus_get_main_blog_id(){
	if(adminbarmenus_main_site()){
		$ps=get_current_blog_id();
	}else if(defined('BLOG_ID_CURRENT_SITE')){
		$ps=BLOG_ID_CURRENT_SITE;
	}else{
		$ps=1;
	}
	return $ps;
}
function adminbarmenus_get_option($option){
	adminbarmenus_switch_to_blog(adminbarmenus_get_main_blog_id());
	$out = get_option($option);
	adminbarmenus_restore_current_blog();
	return $out;
}


// Adding settings option for additional adminbar links  -------------->
add_filter( 'admin_init' , 'adminbarmenus_register_fields' );
function adminbarmenus_register_fields() {
	register_setting( 'general', 'adminbarmenus_additional_shortcuts', 'esc_attr' );
	add_settings_field(
	'adminbarmenus_additional_shortcuts',
	'<label id="am-additional-shortcuts-label" for="am-additional-shortcuts">'.__('Show Admin shortcuts Links') .'</label>',
	'adminbarmenus_fields_html', 'general' );
}
function adminbarmenus_fields_html() {
	echo '<p><select name="adminbarmenus_additional_shortcuts" id="am-additional-shortcuts">';
	foreach(array('yes','no') as $item){
		if(get_option( 'adminbarmenus_additional_shortcuts', '' )==$item)	{
			echo '<option value="'.$item.'" selected>'.ucfirst($item).'</option>';
		}else{
			echo '<option value="'.$item.'">'.ucfirst($item).'</option>';
		}
	}
	echo '</select> More WordPress admin menus shortcut links under "Site Menu" & "Add New" section in Adminbar. <a href="https://wordpress.org/plugins/wp-adminbar-menus/" target="_blank">Know More</a></p>
	';
}


// Custom Adminbar menu  -------------->
function adminbarmenus_adminbar_menus() {
	if( adminbarmenus_main_site() && adminbarmenus_is_s_admin() ){
		register_nav_menus(	array('adminbarmenus_adminbar_menus' => __( 'WP : Adminbar Menu','am' )));
	}
}
add_action( 'init', 'adminbarmenus_adminbar_menus' );


// Adding custom links to adminbar  -------------->
function adminbarmenus_custom_adminbar_menus()
{
	global $wp_admin_bar;

	// Adding Plugins, Tools, Users, Posts, Pages & Settings shortcuts to "WordPress" menus ---------->
	if(get_option( 'adminbarmenus_additional_shortcuts', '' )!='no')
	{
		if(!is_admin() )
		{
			$wp_admin_bar->remove_menu( 'themes' );
			$wp_admin_bar->add_menu( array(
			'id'    => 'posts',
			'title' =>  __( 'Posts' ),
			'href'  => esc_url( get_admin_url() ).'edit.php',
			'parent' => 'site-name',
			'meta'  => array( 'class' => 'am-adminabr-posts' )
			));
			$wp_admin_bar->add_menu(  array(
			'id'    => 'pages',
			'title' => __( 'Pages' ),
			'href'  => esc_url( get_admin_url() ).'edit.php?post_type=page',
			'parent'=>'site-name',
			'meta'  => array( 'class' => 'am-adminabr-pages' )
			));
			$wp_admin_bar->add_menu( array(
			'id'    => 'themes',
			'title' => __( 'Themes' ),
			'href'  => esc_url( get_admin_url() ).'themes.php',
			'parent'=>'site-name',
			'meta'  => array( 'class' => 'am-adminabr-themes' )
			));
			$wp_admin_bar->add_menu( array(
			'id'    => 'plugins',
			'title' => __( 'Plugins' ),
			'href'  => esc_url( get_admin_url() ).'plugins.php',
			'parent'=>'site-name',
			'meta'  => array( 'class' => 'am-adminabr-plugins' )
			));
			$wp_admin_bar->add_menu( array(
			'id'    => 'tools',
			'title' => __( 'Tools' ),
			'href'  => esc_url( get_admin_url() ).'tools.php',
			'parent'=>'site-name',
			'meta'  => array( 'class' => 'am-adminabr-tools' )
			));
			$wp_admin_bar->add_menu( array(
			'id'    => 'users',
			'title' => __( 'Users' ),
			'href'  => esc_url( get_admin_url() ).'users.php',
			'parent'=>'site-name',
			'meta'  => array( 'class' => 'am-adminabr-users' )
			));
			$wp_admin_bar->add_menu( array(
			'id'    => 'settings',
			'title' => __( 'Settings' ),
			'href'  => esc_url( get_admin_url() ).'options-general.php',
			'parent'=>'site-name',
			'meta'  => array( 'class' => 'am-adminabr-settings' )
			));
		}
		// Add new  ------------------- >
		$wp_admin_bar->add_menu( array(
		'id'    => 'theme',
		'title' => __( 'Theme' ),
		'href'  => esc_url( get_admin_url() ).'theme-install.php',
		'parent'=>'new-content',
		'meta'  => array( 'class' => 'am-adminabr-new-theme' )
		));
		$wp_admin_bar->add_menu( array(
		'id'    => 'plugin',
		'title' => __( 'Plugin' ),
		'href'  => esc_url( get_admin_url() ).'plugin-install.php',
		'parent'=>'new-content',
		'meta'  => array( 'class' => 'am-adminabr-new-plugin' )
		));
	}

	$menu_name = 'adminbarmenus_adminbar_menus';
	if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) )
	{
		if($menu = wp_get_nav_menu_object( $locations[ $menu_name ] ))
		{
			$menu_items = wp_get_nav_menu_items($menu->term_id);
			foreach ( (array) $menu_items as $key => $menu_item )
			{
				$menu_item->classes[]='am-adminbar-menu menu-'.$menu_item->ID;
				$wp_admin_bar->add_menu(
				array(
				'id' => $menu_item->ID,
				'parent' => $menu_item->menu_item_parent,
				'title' => $menu_item->title,
				'href' => $menu_item->url,
				'group'=>false,
				'meta' =>  array(
				'class' =>implode(' ',$menu_item->classes),
				'onclick' => '',
				'target' => $menu_item->target,
				'title' =>$menu_item->attr_title,
				'rel' =>$menu_item->xfn,
				'html' =>'',
				'tabindex'=>''
				)
				)
				);
			}
		}
	}
}
add_action( 'wp_before_admin_bar_render', 'adminbarmenus_custom_adminbar_menus' );
?>