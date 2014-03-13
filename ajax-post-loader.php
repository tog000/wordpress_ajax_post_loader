<?php
/**
 * @package Ajax_Post_Loader
 * @author Gabriel Trisca
 * @version 0.0.1
 */
/*
Plugin Name: AJAX Post Loader
Plugin URI: http://tog000.com.ar
Description: Allows ferching posts via AJAX
Author: Gabriel Trisca
Version: 0.0.1
Author URI: http://tog000.com.ar
License: GPL2
*/

AJAXPostLoader::init();

class AJAXPostLoader {

	public static $apl_folder;

	private static $weekly_feed_zone = 'all-crops-weekly-feed';
	private static $monthly_silo_zone = 'all-crops-monthly-silo';
	private static $weekly_report_zone = 'all-crops-weekly-report';

	/**
	 * Setup the class variables & hook functions.
	 */
	public static function init() {

		self::$apl_folder = plugins_url( '', __FILE__ );

		add_action( 'wp_ajax_nopriv_ajax-post-loader', __CLASS__ . '::handle_request' );
		add_action( 'wp_ajax_ajax-post-loader', __CLASS__ . '::handle_request' );
	}

	/**
	 * Handle incoming AJAX requests
	*/
	public static function handle_request(){

		$zone = $_REQUEST['zone']; 				//REQUIRED
		$page = intval($_REQUEST['page'])-1;	//REQUIRED
		$posts = $_REQUEST['posts'];			//REQUIRED

		$category = isset($_REQUEST['category'])?$_REQUEST['category']:False;
		
		// Get the posts from Zoninator
		$query = z_get_zone_query($zone);

		$query->set("post_status","publish");

		// If category present select it
		if($category && $category!="" && $category!="*"){
			$query->set("category_name",$category);
			$query->get_posts();
		}

		$filtered = array_splice($query->posts, $posts*$page, $posts);

		foreach($filtered as $post){

			// Fix the excerpt
			$post->post_excerpt = do_shortcode($post->post_excerpt);
			$post->post_excerpt	= str_replace("\r\n\r\n","</p><p>",$post->post_excerpt);
			$post->post_excerpt	= "<p>".$post->post_excerpt."</p>";

			$post->permalink = get_permalink($post->ID);

			$post->post_content = "";
			$post->thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
			$post->thumbnail = $post->thumbnail[0];
			$post->comments_link = get_comments_link($post->ID);
			$post->news_kind = get_post_meta($post->ID, 'ift_news_kind', true);
			$post->term_list = get_the_term_list($post->ID, 'portfolio_category', '', ', ', '');
			$categories = get_the_category($post->ID);

			foreach($categories as $category) {
				$post->categories .= '<a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '">'.$category->cat_name.'</a>'.", ";
			}

			$post->categories = substr($post->categories,0,strlen($post->categories)-2);

		}

		$results = array("total_items"=>$query->found_posts,"total_pages"=>ceil($query->found_posts/$posts),"current_page"=>$page+1);

		// Add `meta` info
		$results["posts"] = $filtered;
		$json = json_encode($results);
		
		header( "Content-Type: application/json" );
		die($json);

	}

}