<?php
/* 
 Plugin Name: wk_content_types
 Plugin URI: http://knorcedger.com
 Description: Content type test
 Version: 0.8.9
 Author: Achilleas Tsoumitas
 Author URI: http://knorcedger.com
 */
?>
<?php
function wk_content_types() {
}

//find and save the content type
$content_type = '';
$content_type = $_GET['type'];
//check if it is an edit
$edit_post = intval($_GET['post']);
//echo $edit_post;
if($edit_post > 0) {
	$content_type = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = '$edit_post' AND meta_key = 'content_type'");
}

//add the main menu pages
add_action("admin_menu", "add_admin_pages");
function add_admin_pages() {
	//fetch options
	require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wk_content_types/options.php';
	global $current_user;
	get_currentuserinfo();
	//add pages
	foreach ($pages as $page) {
		$pagedata = explode('|', $page);
		add_submenu_page($pagedata[0], $pagedata[1], $pagedata[2], $pagedata[3], $pagedata[4]);
	}
}

//reorder main menu
add_action("admin_head", "reorder_main_menu");
function reorder_main_menu() {
	wp_enqueue_script('jquery');
	?>
	<script>
		jQuery(document).ready(function() {
			jQuery("#menu-posts .wp-submenu a[href='post-new.php']").parent().hide();
			var last = jQuery("#menu-posts .wp-submenu ul li:last");
			jQuery("#menu-posts .wp-submenu a[href='post-new.php']").parent().insertAfter(last);
			jQuery("#menu-posts .wp-submenu a[href='edit-tags.php?taxonomy=post_tag']").parent().insertAfter(last);
			jQuery("#menu-posts .wp-submenu a[href='categories.php']").parent().insertAfter(last);
			jQuery("#menu-posts .wp-submenu a[href='edit.php']").parent().insertAfter(last);
		});
	</script>
<?php
}

//add external css and javascript
add_action("admin_footer", "add_external");
function add_external() {
	?>
	<link rel="stylesheet" href="/wp-content/plugins/wk_content_types/style.css" type="text/css" media="screen" charset="utf-8" />
<?php
	include_once 'script.php';
}

//find the sum of the boxes we have to pass (not show) before we find the boxes for our page
$box_id = 0;
$sum = 0;
add_action("admin_menu", "wk_content_types_init");
function wk_content_types_init() {
	global $box_id;
	global $content_type;
	require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wk_content_types/options.php';
	$sum = 0;
	//find the id of this post type (την σειρά δηλαδή με την οποία είναι στο options)
	for ($i = 0; $i < sizeof($names); $i++) {
		if($names[$i] != $content_type){
			$sum += $boxes[$names[$i]];
		}else{
			break;
		}
	}
	//echo "sum=".$sum;
	$box_id = $sum;
}

//add the action of creating the meta box
add_action('admin_menu', 'add_box');
function add_box() {
	global $box_id;
	global $wpdb;
	global $content_type;
	require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wk_content_types/options.php';
	//add the boxes for this post type
	for($i=$box_id; $i<$boxes[$content_type]+$box_id;$i++){
		$b = $box[$i];
		add_meta_box($b['name'], $b['title'], 'add_fields', 'post', $b['place'], $b['priority']);
	}

	//add the hidden vars box
	add_meta_box('hidden', 'hidden', 'add_hidden', 'post', 'side', 'low');
}

//add the meta boxes
function add_fields() {
	global $box_id;
	global $wpdb;
	global $content_type;
	$edit_post = intval($_GET['post']);

	require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wk_content_types/options.php';
	//if type of the entry
	//find the id of this post type (την σειρά δηλαδή με την οποία είναι στο options)
    for ($i = 0; $i < sizeof($names); $i++) {
    	if($names[$i] == $content_type){
    		$type_id = $i;
			break;
    	}
    }

	//add the fields for this box
	$fields = $box[$box_id]['fields'];
	$path = '../wp-content/plugins/wk_core/';
	//send object_id
	if($_GET['post'] != ''){
		$object_id = intval($_GET['post']);
	}else{
		$object_id = 0;
	}
	$object_type = 'post';
	$language = 'gr';
	include $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wk_core/field_loop.php';
	//include the custom (external) code
	if($box[$box_id]['custom'] != ''){
		include $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wk_content_types/custom/' . $box[$box_id]['custom'];
	}
	$box_id++;
}


//add_dummy is used for content types without boxes, just to remove the boxes and change the menu css
function add_hidden() {
	
	global $content_type;
	require $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/wk_content_types/options.php";
	
	//also change the css for the menu to show our option
	wp_enqueue_script('jquery');
	//dont change the selected menu if its an edit
	if($_GET['post'] == ''){
	?>
	<script>
		jQuery(document).ready(function() {
			//remove current cless from Add new
			jQuery("#menu-posts .wp-submenu ul li:not('.dont-touch-me')").removeClass("current");
			jQuery("#menu-posts .wp-submenu ul li:not('.dont-touch-me') a").removeClass("current");
			//add current classes to our type
			var type = "<?php echo $content_type; ?>";
			if(type != "") {
				jQuery("#menu-posts .wp-submenu ul li a[href='post-new.php?type="+type+"']").parent().addClass("current");
				jQuery("#menu-posts .wp-submenu ul li a[href='post-new.php?type="+type+"']").addClass("current");
			}
		});
	</script>
	<?php
	}
	?>
	<p class="hidden">
		<input type="text" name="content_type" id="content_type" value="<?php echo $content_type; ?>" size="30" />
	</p>
	<?php
	//remove dummy meta box
	echo "<style type='text/css'>#hidden {display: none !important}</style>";
}

//save data
add_action('save_post', 'save_data');
function save_data($post_ID) {
	global $wpdb;
	global $content_type;
	global $sum;
	global $box_id;
	if(!wp_is_post_revision($post_ID) && $_POST['post_type'] == 'post' && is_admin()) {
		//print_r($_POST);
		//require 'fire.php';
		$content_type = $_POST['content_type'];
		change_post_meta($post_ID, 'content_type', $content_type);
		//check all the boxes, and save the ones that have a value
		require $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/wk_content_types/options.php';
		//find again the correct sum
		$sum = 0;
		//find the id of this post type (την σειρά δηλαδή με την οποία είναι στο options)
		for ($i = 0; $i < sizeof($names); $i++) {
			if($names[$i] != $content_type){
				$sum += $boxes[$names[$i]];
			}else{
				break;
			}
		}
		//run through all the boxes of this content type to check for input
		$t = $boxes[$content_type]+$sum;
		
		for($i=$sum; $i<$t; $i++){
			$b = $box[$i];
			//echo "sum=$sum, t=$t";
			//check if we have custom php file
			if($b['custom'] == ''){
				//each box can have multiple inputs, run through them
				for($j=0; $j<sizeof($b['fields']); $j++){
				
					$temp = explode('|', $b['fields'][$j]);
					$level = $temp[0];
					$type = $temp[1];
					$name = $temp[2];
					$translation = $temp[3];
					$default = $temp[4];
				
					//check the type and add the approprite custom fields
					if ($type == 'date') {
						$input = $_POST[$name.'_day'];
						change_post_meta($post_ID, $name.'_day', $input);
						$input = $_POST[$name.'_month'];
						change_post_meta($post_ID, $name.'_month', $input);
						$input = $_POST[$name.'_year'];
						change_post_meta($post_ID, $name.'_year', $input);
						//array_push($vars, $name.'_day', $name.'_month', $name.'_year');
					} elseif ($type == 'time') {
						$input = $_POST[$name.'_hour'];
						change_post_meta($post_ID, $name.'_hour', $input);
						$input = $_POST[$name.'_minute'];
						change_post_meta($post_ID, $name.'_minute', $input);
						//array_push($vars, $name.'_hour', $name.'_minute');
					} elseif ($type == 'checkbox') {
						//save and seperate checkbox values
						$allvalues = explode('~', $temp[5]);
						//find var_names to add to vars
						$k = 0;
						foreach ($allvalues as $val) {
							if ($k%2 == 0) {
								//do nothing
							} else {
								//var names are first (before values), so we have the value as input, and as the desired value inside the mysql key
								$input = $_POST[$val];
								//name = val is used to empty this cell
								if($input != ''){
									$name = $input;
								}else{
									$name = $val;
								}
								//empty values are not deleted, just left empty
								change_post_meta($post_ID, $name, $input);
							}
							$k++;
						}
					}else{
						$input = $_POST[$name];
						change_post_meta($post_ID, $name, $input);
					}
				}
			}else{
				//save the custom codes data
				//find the single values
				$temp = $b['fields'][0];
				$singles = explode('|', $temp);
				//save all singles
				foreach($singles as $single){
					$input = $_POST[$single];
					change_post_meta($post_ID, $single, $input);
				}
				
				//find the values that should be saved like date0, date1, date2...
				$temp2 = $b['fields'][1];
				$bases = explode('|', $temp2);
				//run through all the values of the custom
				//the first values indicates how many values we have for each value, example: first = 2, we have date0, date1
				$num = $_POST[$singles[0]];
				//echo $num;
				
				for($j=0; $j<$num; $j++){
					//read all fields
					foreach($bases as $base){
						$input = $_POST[$base.$j];
						//echo $base.$j . '=  ' . $input;
						change_post_meta($post_ID, $base.$j, $input);
					}
				}
				//require 'fire.php';
			}
		}
	}
}

//a function to simplify changing post meta
function change_post_meta($post_ID, $name, $input){
	if($name != ''){
		$add = add_post_meta($post_ID, $name, $input, TRUE);
		if ($add) {
			//value just added, do nothing
		} else {
			//prevent content_type from being deleted for any reason
			if($name == 'content_type' && $input == ''){
				//dont delete the content type
			}else{
				update_post_meta($post_ID, $name, $input);
			}
		}
	}
}
?>
