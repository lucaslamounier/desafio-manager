<?php

/*
Plugin Name: CMS Desafio
Plugin URI: http://www.desafio.leg.br
Description: Plugin desenvolvido para gerenciar os conteúdos e inscrições dos desafios da câmara dos deputados.
Version: 0.0.1
Author: Lucas Lamounier
Author URI: http://github.com/lucaslamounier
Text Domain: desafio-manager
License: GPL2

*/

defined('ABSPATH') or die( 'Nope, not accessing this' );

class Desafio_manager {
	
	private static $instance;
	private static $wpdb;
	const TEXT_DOMAIN = "desafio-manager";
	
	public static function getInstance() {
		if (self::$instance == NULL) {
		  self::$instance = new self();
		}
		return self::$instance;
	}
	
	private function __construct() {
		add_action('init', array($this, 'inicializar'));		
		//add_action('admin_bar_menu', array($this, 'add_custom_view_post'), 1000);
		
		add_action( 'admin_bar_menu', array($this, 'remove_visualizar_desafio'), 999 );
		add_filter( 'preview_post_link', array($this, 'css_button_preview'));
		add_filter( 'preview_post_link', array($this, 'button_preview'));
	}
	
	/** Remove o nó ver desafio **/
	
	public function remove_visualizar_desafio( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'view' );
	}
	
	public function css_button_preview(){
		$id = $post->ID;
		$post_type = get_post_type( $id );
		$user = wp_get_current_user();
		//$allowed_roles = array('editor', 'administrator', 'author');
		$allowed_roles = array('administrator', 'editor_desafio');

		/** Só apresenta o botão se o tipo for desafio */
		if($post_type == 'desafio' && array_intersect($allowed_roles, $user->roles)){
			
			return "
				<style>
				  #post-preview {
					background: #fdcc3e;
					border: none;
					font-size: 16px;
					color: #000000;
					float: left;
					margin: 10px 0;
				}
			</style>";
		
		/** Se não oculta o botão */
		}else{
			return "
				<style>
				  #post-preview {
					display: none;
				}
			</style>";
		}
	}
	
	/** Override Button preview in post */
	public function button_preview($preview_link){
		
		if(!is_admin()){
			return;
		}
		
		global $current_screen;
		global $pagenow;
		global $wp;
		
		$post = get_post();
		$id = $post->ID;
		$slug = $post->post_name;
		$url_desafio = 'http://vvmctphp01:8080/desafios/preview/';
		//$url_desafio = 'http://10.251.12.129/desafios/preview/';
		
		$url = $url_desafio . $slug . "/".$id;
		
		$post_type = get_post_type( $id );
		
		if($post_type != 'desafio'){
			return;
		}
		
		if(is_admin()){
			if($pagenow == 'edit.php' && $_GET['post_type'] == 'desafio'){
				return;
			}else if(empty($slug) && empty($id)){
				return;
			}
		}
		return $url . "?origin=".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	

	/** Admin Toolbar Custom Button **/
	public function add_custom_view_post($wp_admin_bar){
		
		global $current_screen;
		global $pagenow;
		global $wp;  
				
		$post = get_post();
		$id = $post->ID;
		$slug = $post->post_name;
		$url_desafio = get_option('url_desafio');
		
		if(is_admin()){
			if($pagenow == 'edit.php' && $_GET['post_type'] == 'desafio'){
				return;
			}else if(empty($slug) && empty($id)){
				return;
			}
		}
		
		if(empty($url_desafio)){
			$frontend_url = 'http://vvmctphp01:8080/desafios/preview/';
			//$frontend_url = 'http://10.251.12.129/desafios/preview/';
			add_option('url_desafio', $frontend_url);
		}
		
		$frontend_url = 'http://vvmctphp01:8080/desafios/preview/';
		//$frontend_url = 'http://10.251.12.129/desafios/preview/';
		
		$url = $frontend_url . $slug . "/".$id;
		
		//$url = $url. "?origin=" . add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
		$url = $url . "?origin=" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		$args = array(
			'id'    => 'custom-preview-desafio',
			'title' => 'Preview do Desafio',
			'href'  => $url,
			'meta'  => array(
				'class' => 'teste',
				'title' => 'Clique aqui para visualizar uma prévia do desafio.',
				'target' => '_blank'
			)
		);
		$wp_admin_bar->add_node($args);
	}

	/**
	* Função de inicialização, centraliza a definição de filtros/ações
	*
	*/
	public static function inicializar(){
		global $wpdb;				
		//Mapear objetos WP
		Desafio_manager::$wpdb = $wpdb;
	}
}

Desafio_manager::getInstance();

?>