<?php
/* 
 Plugin Name: wk_cms
 Plugin URI: http://thinkdesquared.com
 Description: Wordpress CMS
 Version: 0.1.0
 Author: Achilleas Tsoumitas
 Author URI: http://knorcedger.com
 */
?>
<?php
wp_enqueue_script('jquery');
add_action('admin_menu', 'wk_cms_options_page');

function wk_cms_options_page() {
  add_options_page('wk_cms', 'wk_cms', 'publish_posts', 'wk_cms/options-page.php');
}

if (is_admin()) {
	wp_enqueue_script('jquery');
	wp_enqueue_script('wk_cms.js', '/wp-content/plugins/wk_cms/wk_cms.js');
	wp_enqueue_style('wk_cms.css', '/wp-content/plugins/wk_cms/wk_cms.css');
}

function wk_cms_function() {
  echo '<div class="wrap">';
  echo '<p>Function text</p>';
  echo '</div>';
}

add_action('wp_dashboard_setup', 'example_add_dashboard_widgets' );

function example_add_dashboard_widgets(){

	$cat_ids = get_all_category_ids();
		
	foreach($cat_ids as $id){
		$cat = get_category($id);
		wp_add_dashboard_widget($cat->cat_ID, $cat->cat_name, test);
	}
}

function test(){

}

/**
 * A simple text cutter
 * 
 * @return string The cutten text
 * @param string $text
 * @param int $limit The characters to cut at
 * @param string $allow The tags to allow (<br><a>...)
 */
function wk_cut_text($text, $limit, $allow = '') {
	//strinp tags
	$text = strip_tags($text, $allow);
	//check if we have to cut
	if (strlen($text) >= $limit) {
		//find where to cut
		$cut = strpos($text, " ", $limit);
		if ($cut > $limit) {
			$text = substr($text, 0, $cut);
			$text = $text.'...';
		}
	}
	return $text;
}
?>
