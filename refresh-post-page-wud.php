<?php
/*
=== Refresh Post Page WUD ===
Contributors: wistudat.be
Plugin Name: Refresh Post Page WUD
Donate Reason: Stand together to help those in need!
Donate link: https://www.icrc.org/eng/donations/
Description: Easy to use auto refresh field per post and/or page, no extra code required.
Author: Danny WUD
Author URI: http://wud-plugins.com/
Plugin URI: http://wud-plugins.com/
Tags: auto refresh, refresh meta, meta tags, content refresh, refresh content, auto refresh content, automatic refresh page, automatic refresh post,auto refresh post, auto refresh page, automatic refresh, refresh a page, refresh pages
Requires at least: 3.6
Tested up to: 4.9
Stable tag: 1.0.7
Version: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: refresh-post-page-wud
Domain Path: /languages
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
//==============================================================================//
$wudversion='1.0.7';
//==============================================================================//

add_action('init', 'Wud_Rpp_Refresh_Cnt');
add_action('wp_head', 'Wud_Header');
add_action('plugins_loaded', 'wud_rpp_languages');
add_action('wp_head', 'wud_rpp_show_template');

	function wud_rpp_show_template() {
		global $template;
		$temp = basename($template);
		//echo $temp;
	}
	function wud_rpp_languages() {
			load_plugin_textdomain( 'rpp-wud', false, dirname(plugin_basename( __FILE__ ) ) . '/languages' );
	}
	
	function Wud_Rpp_Refresh_Cnt() {
		
		if (is_edit_page()){
		add_action('add_meta_boxes', 'Wud_Options');
		add_action('save_post', 'WUD_SaveSettings');
		}
	}

	function Wud_Rpp_Options() {
		global $post, $wudversion;
		
		$Wud_Rpp_Settings = @unserialize(get_post_meta($post->ID, 'Wud_Rpp_Value', true));	
		echo '<input type="hidden" name="wud_name_id" id="wud_name_id" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />	
		<p><label>'.__("Refresh content after", "rpp-wud").':</label> <input type="text" name="Wud_Rpp_Value[seconds]" id="wud_time" value="' . $Wud_Rpp_Settings['seconds'] . '" style="width: 50px;" /> '.__("seconds", "rpp-wud").'.</p>
		<p class="description">'.__("Empty or 0 = no content refresh", "rpp-wud").'</p>
		<p><b>'.__("Refresh Post Page WUD version", "rpp-wud").': '.$wudversion.'</b></p>';
	}

	function Wud_Options() {
		add_meta_box('Wud_Rpp_Options',''.__("Refresh Page Content WUD", "rpp-wud").'','Wud_Rpp_Options','page','side','low');	
		add_meta_box('Wud_Rpp_Options',''.__("Refresh Post Content WUD", "rpp-wud").'','Wud_Rpp_Options','post','side','low');	
		}

	function Wud_Header() {
		global $wp_query;
		//Check or it is a home page, page or post only.
		if (!is_admin() && (is_page() || is_single() || is_home())) {		
		$contentobj = $wp_query->get_queried_object();
			if (is_home()) {
				if(get_option('wud_rpp_title_time') !== "" && get_option('wud_rpp_title_time') > 0 ){
					$Wud_Rpp_Home_Time=get_option('wud_rpp_title_time');
					echo '<meta http-equiv="refresh" content="' . $Wud_Rpp_Home_Time . '" />';
				}
			}
			else{
			$Wud_Rpp_Settings = @unserialize(get_post_meta($contentobj->ID, 'Wud_Rpp_Value', true));
			if (intval($Wud_Rpp_Settings['seconds']) && intval($Wud_Rpp_Settings['seconds'] > 0)) {echo '<meta http-equiv="refresh" content="' . $Wud_Rpp_Settings['seconds'] . '" />';}
			}
		}
	}

	function WUD_SaveSettings($post_id) {
		global $post;
		if($_POST){
			if (!wp_verify_nonce($_POST["wud_name_id"], plugin_basename(__FILE__)))
				return $post_id;	
			 if ( !current_user_can( 'edit_post', $post_id ))
				 return $post_id;	
				$Wud_Rpp_Settings = $_POST['Wud_Rpp_Value'];
			
			if (empty($Wud_Rpp_Settings)) {
				delete_post_meta($post_id,'Wud_Rpp_Value',get_post_meta($post_id, 'Wud_Rpp_Value', true));
				return;
			}
			
			if (!intval($Wud_Rpp_Settings['seconds'])) {$Wud_Rpp_Settings['seconds'] = 0;} //not exist.
			if (intval($Wud_Rpp_Settings['seconds'])) {$Wud_Rpp_Settings['seconds'] = round($Wud_Rpp_Settings['seconds'],0);} //make numbers.
			if (intval($Wud_Rpp_Settings['seconds'])>3600) {$Wud_Rpp_Settings['seconds'] = 3600;} //max 1 hour.
			if (intval($Wud_Rpp_Settings['seconds'])<0) {$Wud_Rpp_Settings['seconds'] = 0;} //min 0 sec.
			
			if (!add_post_meta($post_id, 'Wud_Rpp_Value', serialize($Wud_Rpp_Settings), true))
				update_post_meta($post_id, 'Wud_Rpp_Value', serialize($Wud_Rpp_Settings));
		}
	}
	
	function is_edit_page($new_edit = null){
		//Take action only by new /save post.
		global $pagenow;
		if (!is_admin()) return false;
		if($new_edit == "edit")
			return in_array( $pagenow, array( 'post.php',  ) );
		elseif($new_edit == "new") 
			return in_array( $pagenow, array( 'post-new.php' ) );
		else 
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}


	add_action('admin_init', 'wud_rpp_reading_settings');  
	function wud_rpp_reading_settings() {  
		add_settings_section(  
			'wud_rpp_section',
			'Refresh Post Page WUD',
			'wud_rpp_text_box',
			'reading'
		);

		add_settings_field(
			'wud_rpp_title_time',
			'Refresh content after<br>Empty or 0 = no refresh.',
			'wud_rpp_input',
			'reading',
			'wud_rpp_section',
			array(
				'wud_rpp_title_time'
			)  
		); 

		register_setting('reading','wud_rpp_title_time', 'esc_attr');
	}

	function wud_rpp_text_box() {
		echo 'Refresh the content if "<b>Your latest posts</b>" on top of this page is selected.<br>If a "<b>Static Page</b>" is selected, use the timer on the selected Front/Post page.';  
	}

	function wud_rpp_input($args) {
		$option = get_option($args[0]);
		echo '<input  type="number"  min="0" max="3600" style="width: 70px;" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" />';
	}
?>
