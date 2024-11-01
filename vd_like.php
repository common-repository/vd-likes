<?php
/*
Plugin Name: VD Like
Plugin URI: vrajeshdave.wordpress.com
Description: by using this plugin ,you can srt like unlike  in your post,page,and anything! Just user shortcode [vd_likes] in post or page.you can use it by do_shortcode() method for developer EX: echo do_shortcode('[vd_likes id='POST_ID']');
Version: 1.0
Author: Vrajesh Dave
Author URI: vrajeshdave.wordpress.com
License: GPLv2 or later
*/
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );
if ( ! class_exists( 'VD_Like' ) ) {
	class VD_Like
	{		
		protected $tag = 'vd_likes';	
		protected $name = 'Like';
		protected $version = '1.0';
		public function __construct()
		{
			add_shortcode( $this->tag, array( &$this, 'shortcode' ) );
			add_action( 'wp_ajax_vd_like', array(&$this, 'vd_set_like_ajax'));
			add_action( 'wp_ajax_nopriv_vd_like',array(&$this, 'vd_set_like_ajax'));
			add_action( 'wp_ajax_vd_downlike', array(&$this, 'vd_set_downlike_ajax'));
			add_action( 'wp_ajax_nopriv_vd_downlike',array(&$this, 'vd_set_downlike_ajax'));
			add_action('admin_init', array(&$this, 'vd_like_admin_init'));
			add_action('admin_menu', array(&$this, 'vd_like_add_menu'));		
			
		}
		function vd_like_activate(){}
		function vd_like_deactivate(){}
		protected function _enqueue()
		{
			
			$vd_like_path = plugin_dir_url( __FILE__ );
			$vd_like_path_css = plugin_dir_url( __FILE__ ).'css/';
			$vd_like_path_js = plugin_dir_url( __FILE__ ).'js/';
			if ( !wp_style_is($this->tag, 'enqueued' ) ) {
				wp_enqueue_style(
					$this->tag,
					$vd_like_path_css . 'vd_like_css.css',
					array(),
					$this->version
				);				
				wp_enqueue_style(
					$this->tag.'vd_like_font',
					$vd_like_path_css . 'vd_like_font.css',
					array(),
					$this->version
				);
				if( !wp_style_is( 'dashicons' ) ){					
					wp_enqueue_style( 'dashicons' );
				}
			}
			
			if ( !wp_script_is($this->tag, 'enqueued' ) ) {
				if (!wp_script_is( 'jquery')) {				
					wp_enqueue_script( 'jquery' );
				}									
				wp_register_script(
					'jquery-' . $this->tag.'localize',
					$vd_like_path_js . 'vd_like_js.js',
					array( 'jquery'),
					'1.0' 
				);
				wp_enqueue_script('jquery-' . $this->tag.'localize');
				$options = array(
					 'ajaxurl' => admin_url( 'admin-ajax.php' )
				);
				wp_localize_script('jquery-' . $this->tag.'localize','vd_likes_obj', $options );
			}
			
		}
		
		public function vd_set_like_ajax(){			
			$get_pid = (is_numeric($_REQUEST['pid']) && ((int)$_REQUEST['pid']>0))  ? $_REQUEST['pid']:0;
			$vd_like_user_id = get_current_user_id();
			if ($vd_like_user_id == 0) {
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$uid = $_SERVER['HTTP_CLIENT_IP'];
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$uid = $_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$uid = $_SERVER['REMOTE_ADDR'];
				}
			} else {
				$uid=$vd_like_user_id ;
				/******Used to get listing of user rating********/
				$user_like= get_user_meta($uid,'like_once', true);			
				if(empty($user_like)){ $user_like = array(); }			
				if (in_array($get_pid,$user_like)) {	
					
				}
				else{	
					array_push($user_like,$get_pid);				
				}
				update_user_meta( $uid,'_vd_user_like',$user_like);
				
			}
			/***************get post like**********************/
			$post_like = get_post_meta($get_pid,'_vd_post_like',true);						
			$post_all_like = get_post_meta($get_pid,'_vd_like',true);	
			/**************get post unlike***********************/
			$post_downlike= get_post_meta($get_pid,'_vd_post_downlike',true);		
			$post_all_downlike= get_post_meta($get_pid,'_vd_unlike',true);					
			
			if(empty($post_like)){ $post_like = array(); }			
			if (in_array($uid,$post_like)){				
				/****So check if user unlike that perticular post**************/		
				/*******************************************/			
				if(empty($post_downlike)){ $post_downlike = array(); }			
				if (in_array($uid,$post_downlike)){	
						if(($key = array_search($uid, $post_downlike)) !== false) {  
							unset($post_downlike[$key]);
							update_post_meta($get_pid,'_vd_post_downlike',$post_downlike);		
							update_post_meta($get_pid,'_vd_unlike',++$post_all_downlike);		
						}
						/****like added*********/
						if (!in_array($uid,$post_like)){		
							array_push($post_like,$uid);
							update_post_meta($get_pid,'_vd_post_like',$post_like);
							update_post_meta($get_pid,'_vd_like',++$post_all_like);	
						}
				}				
			}
			else{						
						if(empty($post_downlike)){ $post_downlike = array(); }		
						if(($key = array_search($uid, $post_downlike)) !== false) {  
							unset($post_downlike[$key]);	
							update_post_meta($get_pid,'_vd_post_downlike',$post_downlike);		
							update_post_meta($get_pid,'_vd_unlike',++$post_all_downlike);	
						}
				array_push($post_like,$uid);
				update_post_meta($get_pid,'_vd_post_like',$post_like);
				update_post_meta($get_pid,'_vd_like',++$post_all_like);	
			}
			$result_vd_like=array('like'=>$post_all_like,'downlike'=>$post_all_downlike);
			echo json_encode($result_vd_like);			
			die();
		}
		/*******************************SET AJAX DOWN LIkE************************************/
		public function vd_set_downlike_ajax(){
			$get_pid = (is_numeric($_REQUEST['pid']) && ((int)$_REQUEST['pid']>0))  ? $_REQUEST['pid']:0;			
			$vd_like_user_id = get_current_user_id();
			if ($vd_like_user_id == 0) {
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$uid = $_SERVER['HTTP_CLIENT_IP'];
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$uid = $_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$uid = $_SERVER['REMOTE_ADDR'];
				}
			} else {
				$uid=$vd_like_user_id;
				/******Used to get listing of user rating********/
				$user_like= get_user_meta($uid,'like_once', true);
			
				if(empty($user_like)){ $user_like = array(); }			
				if (in_array($get_pid,$user_like)) {							
				}
				else{
					array_push($user_like,$get_pid);				
				}
				update_user_meta( $uid,'_vd_user_like',$user_like);
				
			}
			/***************get post like**********************/
			$post_like = get_post_meta($get_pid,'_vd_post_like',true);						
			$post_all_like = get_post_meta($get_pid,'_vd_like',true);	  //add likes
			/**************get post unlike***********************/
			$post_downlike= get_post_meta($get_pid,'_vd_post_downlike',true);		
			$post_all_downlike= get_post_meta($get_pid,'_vd_unlike',true);  //add dislikes
								
			
			if(empty($post_downlike)){ $post_downlike = array(); }			
			if (in_array($uid,$post_downlike)){				
				/****So check if user like that perticular post**************/		
				if(empty($post_like)){ $post_like = array(); }			
				if (in_array($uid,$post_like)){						
						if(empty($post_like)){ $post_like = array(); }			
						if(($key = array_search($uid, $post_like)) !== false) { 
							unset($post_like[$key]);																
							update_post_meta($get_pid,'_vd_post_like',$post_like);		
							update_post_meta($get_pid,'_vd_like',--$post_all_like);		
						}						
						/*********unlike added**************/		
						if (!in_array($uid,$post_downlike)){				
							array_push($post_downlike,$uid);				
							update_post_meta($get_pid,'_vd_post_downlike',$post_downlike);		
							update_post_meta($get_pid,'_vd_unlike',--$post_all_downlike);		
						}					
				}
			}
			else{				
					if(empty($post_like)){ $post_like = array(); }			
					if(($key = array_search($uid, $post_like)) !== false) { 
							unset($post_like[$key]);
							//print_r($post_like);die();								
							update_post_meta($get_pid,'_vd_post_like',$post_like);		
							update_post_meta($get_pid,'_vd_like',--$post_all_like);		
					}					
					array_push($post_downlike,$uid);				
					update_post_meta($get_pid,'_vd_post_downlike',$post_downlike);		
					update_post_meta($get_pid,'_vd_unlike',--$post_all_downlike);			
			}			
			$result_vd_like=array('like'=>$post_all_like,'downlike'=>$post_all_downlike);
			echo json_encode($result_vd_like);
			die();
		}
		
		public function shortcode( $atts, $content = null )
		{
			extract( shortcode_atts( array(				
				'id' => false
			), $atts ) );			
			$this->_enqueue();				
			$like = $this->_get_like_link($id);					
			return $like;
		}
		protected function _get_current_post_id($id=false){
			$id = !$id ? get_the_ID() : $id;
			return $id;
		}
		protected function _get_up_like($id=false){
			$like = get_post_meta($id, '_vd_like', true) ? get_post_meta($id, '_vd_like', true) : 0;
			return $like;
		}
		protected function _get_down_like($id=false){
			$downlike = get_post_meta($id, '_vd_unlike', true) ? get_post_meta($id, '_vd_unlike', true) : 0;
			return $downlike;
		}
		protected function _get_like_link($id=false){
			$id =$this->_get_current_post_id($id);			
			$vd_uplike = $this->_get_up_like($id);
			$vd_downlike =$this->_get_down_like($id);
			$vd_current_uid = $this->_vd_get_current_user();
			$vd_like_user_check=$this->_vd_is_user_like($vd_current_uid,$id);
			$vd_down_like_user_check = $this->_vd_is_user_downlike($vd_current_uid,$id);
			if($vd_like_user_check == true){		
				$like_thumb_up='icon-thumbs-up-alt';				
			}else{			
				$like_thumb_up='icon-thumbs-up';
			}
			if($vd_down_like_user_check == true){		
				$like_thumb_down='icon-thumbs-down-alt';
			}else{			
				$like_thumb_down='icon-thumbs-down';
			}
			$readonly='';			
			if(get_option('_vd_like_user_login')==1){				
				if(is_user_logged_in()){
					$readonly='';
				}else{
						$readonly="disable";						
				}					
			}		
			$like_content='<div id="vd_like" class=""><a class="'.$readonly.' vd_like_link" href="javascript:void(0)" data-id="'.$id .'"><i class="demo-icon '.$like_thumb_up.'"></i></a><span class="vd_up_count">'.$vd_uplike.'</span><a class="'.$readonly.' vd_downlike_link" href="javascript:void(0)"  data-id="'.$id .'"><i class="demo-icon '.$like_thumb_down.'"></i></a><span class="vd_down_count">'.$vd_downlike.'</span></div>';
			return $like_content;
		}
		protected function _vd_is_user_like($vd_like_uid=false,$vd_like_pid=false){
			$get_vd_like=$this->_get_up_like_per_user($vd_like_pid);
				
			if(!empty($get_vd_like))	{
				if(in_array($vd_like_uid,$get_vd_like))
				{				
					return true;
				}
				else{					
					return false;
				}
			}
		}
		protected function _vd_is_user_downlike($vd_like_uid=false,$vd_like_pid=false){
			$get_vd_down_like=$this->_get_down_like_per_user($vd_like_pid);		
			if(!empty($get_vd_down_like)){
				if(in_array($vd_like_uid,$get_vd_down_like))
				{					
					return true;
				}
				else{
					return false;
				}
			}
		}
		protected function _get_up_like_per_user($vd_pid){
			$post_like = get_post_meta($vd_pid,'_vd_post_like',true);			
			return $post_like;
		}
		protected function _get_down_like_per_user($vd_pid){
			$post_downlike = get_post_meta($vd_pid,'_vd_post_downlike',true);			
			return $post_downlike;
		}
		protected function _vd_get_current_user($id=false){
			$vd_like_user_id = get_current_user_id();
			if ($vd_like_user_id == 0) {
				if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					$uid = $_SERVER['HTTP_CLIENT_IP'];
				} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					$uid = $_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
					$uid = $_SERVER['REMOTE_ADDR'];
				}
			} else {
				$uid=$vd_like_user_id;
			}
			return $uid;
		}
		public function vd_like_admin_init()
		{		
			$this->vd_like_init_settings();			
		}
		
		public function vd_like_init_settings()
		{			
			register_setting('vd_like_template-group', '_vd_like_user_login');
			register_setting('vd_like_template-group', '_vd_like_rate_color');
		} 
		
		public function vd_like_add_menu()
		{
			add_options_page('VD Likes', 'VD Likes', 'manage_options', 'vd_likes_template', array(&$this, 'vd_likes_settings_page'));
		} 
		
		public function vd_likes_settings_page()
		{
			if(!current_user_can('manage_options'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}			
			include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
		} 
		
	}
 }
if(class_exists('VD_Like'))
{	
	$vd_like_obj = new VD_Like();
	register_activation_hook(__FILE__, array(&$vd_like_obj, 'vd_like_activate'));
	register_deactivation_hook(__FILE__, array(&$vd_like_obj, 'vd_like_deactivate'));	
		
	if(isset($vd_like_obj))
	{		
		function plugin_vd_like_settings_link($links)
		{ 
			$settings_link = '<a href="options-general.php?page=vd_likes_template">Settings</a>'; 
			array_unshift($links, $settings_link); 
			return $links; 
		}
		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'plugin_vd_like_settings_link');
	}

}
?>
