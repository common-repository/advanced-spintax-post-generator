<?php
/*
Plugin Name: Advanced Spintax Post Generator
Description: Create posts from spintax templates - a small plugin to help you quickly build out your website for multiple locations, without duplicate content.
Version: 0.1.1
Author: DesignSmoke Web Design
Author URI: https://www.designsmoke.com/
Text Domain: aspgspintax
Domain Path: /languages
*/

if(!function_exists('wp_get_current_user')) {
    include_once(ABSPATH . "wp-includes/pluggable.php"); 
}

$GLOBALS['aspgspintax_nonce'] = "Top 10 Reasons to Use a Nonce";

function aspgspintax_init() {
    $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages'; /* Relative to WP_PLUGIN_DIR */
    load_plugin_textdomain( 'anti-spam-zapper', false, $plugin_rel_path );
}
add_action('plugins_loaded', 'aspgspintax_init');

if(!class_exists("WordPress_SimpleSettings")) {
    include('wordpress-simple-settings.php');
}

class AdvancedSpintaxPostGenerator extends WordPress_SimpleSettings {
    var $prefix = 'advancedspintaxpostgenerator'; // this is super recommended
    
	function __construct() {
		parent::__construct(); // this is required
		// Actions
		add_action('admin_menu', array($this, 'menu') );

		register_activation_hook(__FILE__, array($this, 'activate') );
	}
	function menu() {
		$icon_url = 'dashicons-chart-pie';

		//add_options_page("Advanced Spintax Post Generator", "Advanced Spintax Post Generator", 'publish_posts', "advancedspintaxpostgenerator", array($this, 'admin_page') );
		add_menu_page("Advanced Spintax Post Generator", "Advanced Spintax Post Generator", 'publish_posts', "advancedspintaxpostgenerator", array($this, 'admin_page'), $icon_url);
	}
	function admin_page() {
		include 'admin.php';
	}
	function activate() {
		if($this->get_setting('aspgspintax_install_date') === false)
			$ret = $this->add_setting('aspgspintax_install_date', date('Y-m-d h:i:s'));

		if($this->get_setting('aspgspintax_rating_div') === false)
			$ret = $this->add_setting('aspgspintax_rating_div', 'no');
		
	}
}
$GLOBALS['AdvancedSpintaxPostGenerator'] = new AdvancedSpintaxPostGenerator();


//based on https://gist.github.com/irazasyed/11256369
class aspgspintax_spintax
{
    public function process($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*)\}/x',
            array($this, 'replace'),
            $text
        );
    }
    public function replace($text)
    {
        $text = $this->process($text[1]);
        $parts = explode('|', $text);
        return $parts[array_rand($parts)];
    }
}

function aspgspintax_isnone($v) {
	return !isset($v) || empty($v) || $v === "" || $v === NULL;
}

function aspgspintax_checkperms() {
	// TODO: Add additional user checks here
	return current_user_can('publish_posts');
}

function aspgspintax_create_post() {
	check_ajax_referer($GLOBALS['aspgspintax_nonce'], 'security'); //will literally die if there's no nonce

	global $wpdb; //database

	if(!current_user_can('publish_posts')) {
		wp_die(); //no perms.
		return; //just in case
	}
	if(!aspgspintax_checkperms()) {
		wp_die(); //no perms.
		return; //just in case
	}

	// Default response
	$response = array(
		'message' => esc_html__('Success!', 'aspgspintax'),
		'success' => 1,
		'ID' => 0,
	);

	$spintax = new aspgspintax_spintax();
	//$seed = $GLOBALS['post']->ID.'-'.$GLOBALS['aspgspintax_seed'].'-'.'aspg'; //create a psuedorandom seed to keep the same content on updates
	$seed = microtime();

	$template_id = intval($_POST['template_id']);
	$title = "";
	$content = "";
	$tags = "";
	$categories = "";

	$post_type = "";
	$slug = "";

	$date = "";
	$status = "";

	if(aspgspintax_isnone($template_id)) {

		// Sent to wp_insert_post which uses sanitize_post()
		$content = $_POST['content']; // WPCS: sanitization ok.
		$post_type = $_POST['post_type']; // WPCS: sanitization ok.
		$title = $_POST['title']; // WPCS: sanitization ok.
		$status = $_POST['status']; // WPCS: sanitization ok.
		$tags = $_POST['tags']; // WPCS: sanitization ok.
		$categories = $_POST['categories']; // WPCS: sanitization ok.
		$slug = $_POST['slug']; // WPCS: sanitization ok.


		if(aspgspintax_isnone($status))
			$status = 'draft';
		if(aspgspintax_isnone($post_type))
			$post_type = 'post';


		srand((int)md5($seed));
		$tags = $spintax->process($tags);

		srand((int)md5($seed));
		$categories = $spintax->process($categories);

		srand((int)md5($seed));
		$slug = $spintax->process($slug);

		$tags = array_map('trim', explode(',', $tags));
		$categories = array_map('trim', explode(',', $categories));
	}
	else {
		//TODO: Allow saving/loading/scheduling templates
		//TODO: Featured image
	}

	if(aspgspintax_isnone($content)) {
		$response['message'] = esc_html__('Error, no post content!', 'aspgspintax');
		$response['success'] = 0;

		goto aspgspintax_main_sub3;
	}

	if($date === '' || !isset($date)) {
		$date = date("Y-m-d H:i:s", time());
	}
	else {
		$date = date("Y-m-d H:i:s", intval($date)); //int
	}
	

	if(true) {
		$my_post = array(
		  'post_type' => $post_type,
		  'post_title'    => $title,
		  'post_content'  => $content,
		  'post_status'   => $status,
		  'post_category' => $categories,
		  'tags_input' => $tags,
		  'post_date' => $date,
		  'post_slug' => $slug,
		);
		
		srand((int)md5($seed));
		$my_post['post_title'] = $spintax->process($my_post['post_title']);
		$my_post['post_content'] = $spintax->process($my_post['post_content']);

		$id = wp_insert_post($my_post);

		if($id == 0) {
			$response['message'] = esc_html__('Failed to create post. Unknown error.', 'aspgspintax');
			$response['success'] = 0;
		}
		else {
			$response['ID'] = $id;
		}

		goto aspgspintax_main_sub3;
	}

	
	aspgspintax_main_sub3: { // Eh, screw good practice. How bad can it be?
		wp_send_json($response);
		wp_die();
	}
}
add_action( 'wp_ajax_aspgspintax_create_post', 'aspgspintax_create_post' );



// Add settings page link on left
function aspgspintax_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=advancedspintaxpostgenerator') ) .'">'.esc_html__('Generate Posts','aspgspintax').'</a>';
   $links[] = '<a href="https://www.designsmoke.com/" target="_blank">'.esc_html__('DesignSmoke','aspgspintax').'</a>';
   return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'aspgspintax_action_links' );