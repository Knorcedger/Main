<?php  
/* 
Plugin Name: wk_navigation
Plugin URI: http://thinkdesquared.com 
Description: A navigation plugin
Version: 0.3.5
Author: Achilleas Tsoumitas 
Author URI: http://knorcedger.com 
*/
?>
<?php
/* EXAMPLE
<?php
		wk_navigation_calculations('gr', 10, 1);
		global $wpdb;
		$posts_num = $wpdb->get_var("SELECT COUNT(*) AS posts_num FROM $wpdb->posts WHERE post_status='publish' AND post_type='post' GROUP BY 'post_status'");
		$my_query = new WP_Query();
		$my_query->query("showposts=10&offset=$limit_down");
		while ($my_query->have_posts()) : $my_query->the_post();
			setup_postdata($post);
			$post_ID = get_the_ID();
			?>
			<div class="entry">
				<h2 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> <span class="timestamp"><?php the_time('dMy') ?></span></h2>
				<?php the_excerpt(); ?>
				<a class="more-link" href="<?php the_permalink(); ?>"><span class="hidden">Διαβάστε περισσότερα</span></a>
			</div> <!-- close entry -->
		<?php
		endwhile;
		?>
		
		<?php wk_navigation_display('gr', $posts_num, 1); ?>
*/
/*
posts_num CALCULATION EXAMPLES
$posts_num = $wpdb->get_var("SELECT COUNT(*) AS posts_num FROM $wpdb->posts WHERE post_status='publish' AND post_type='post' GROUP BY 'post_status'");
$posts_num = $wpdb->get_var("SELECT COUNT(*) AS posts_num FROM $wpdb->postmeta WHERE meta_key='content_type' AND meta_value='new' GROUP BY 'meta_value'");
*/
/**
 * Displays a contact form in wordpress
 * 
 * @return 
 * @param string $language The language used in the contact form
 * @param string $posts_num The total number of the posts
 * @param int $mode[optional] Which mode to use. Mode 1 works for greek chars in url
 * @param string $page_type[optional] If we use a standar wp page (only available for now: cat)
 */
function wk_navigation_calculations($language, $posts_num, $mode = 1, $page_type = ''){
	include 'languages/'.$language.'.php';
	
	global $paged;
	global $limit_up;
	global $limit_down;
	global $wk_url;
	global $wk_addon;
	
	//find from page_type
	if($page_type == 'cat'){
		$cat = get_category_by_path($_SERVER['REQUEST_URI'], false);
		$posts_num = $cat->count;
	}
	
	if($mode == 0){
		//find the page for the navigation
		$temp = explode("/page/", $_SERVER['REQUEST_URI']);
		//if temp[1] not empty, its not the first page
		if($temp[1] != ''){
			$temp2 = explode("/", $temp[1]);
			$paged = $temp2[0];
		}else{
			$paged = 1;
		}
		//create some vars for the pagination
		$temp = explode('page/', $_SERVER['REQUEST_URI']);
		if($temp[0] == ''){
			$wk_url = '/';
		}else{
			$temp = explode('?', $temp[0]);
			$wk_url = $temp[0];
		}
		$temp = explode('?', $_SERVER['REQUEST_URI']);
		if($temp[1] != ''){
			$wk_addon = '?'.$temp[1];
		}else{
			$wk_addon = '';
		}
		//for pagination
		$limit_down = $paged*10 - 10;
		$limit_up = $paged*10;
	}else{
		$paged = $_GET['page'];
		//if paged empty, its the first page
		if($paged == ''){
			$paged = 1;
		}
		$temp = explode('/?', $_SERVER['REQUEST_URI']);
		if($temp[0] == ''){
			$wk_url = '/';
		}else{
			$wk_url = $temp[0];
		}
		$temp = explode('?', $_SERVER['REQUEST_URI']);
		if($temp[1] != ''){
			$wk_addon = '?'.$temp[1];
			$temp2 = explode('&page=', $wk_addon);
			$wk_addon = $temp2[0];
		}else{
			$wk_addon = '';
		}
		
		//for pagination
		$limit_down = $paged*10 - 10;
		$limit_up = $paged*10;
	}
}
function wk_navigation_display($language, $posts_num, $mode = 1, $page_type = ''){

	include 'languages/'.$language.'.php';
	
	global $wk_url;
	global $wk_addon;
	global $paged;
	//echo $wk_addon . " " . $wk_url;
	if($wk_addon != ''){
		$wk_url = $wk_url . $wk_addon;
	}

	//find from page_type
	if($page_type == 'cat'){
		$cat = get_category_by_path($_SERVER['REQUEST_URI'], false);
		$posts_num = $cat->count;
	}

	
	if($mode == 0){
	?>
		<div id="wp-pagenavi">
			<span class="pages"><?php echo $page_txt; ?> <?php echo $paged; ?> <?php echo $from_txt; ?> <?php $last_page = intval($posts_num/10); $last_page++; echo $last_page; ?></span>
			<span class="navi-choices">
				<a href="<?php echo $wk_url.$wk_addon; ?>" title="« Πρώτη Σελίδα">« <?php echo $first_txt; ?></a>
				<a href="<?php if($paged <= 2){echo $wk_url.$wk_addon;}else{$new_paged = $paged-1; echo $wk_url.'page/'.$new_paged.'/'.$wk_addon;} ?>">«</a>
				<?php
				if($paged < 4){
					$start = 1;
				}else{
					$start = $paged-1;
				}
				if($last_page - $paged >3){
					$end = $paged + 3;
				}else{
					$end = $last_page+1;
				}
				for($i=$start; $i<$end; $i++){
					if($i == $paged){
				?>
						<span class="current"><?php echo $paged; ?></span>
				<?php
					}else{
				?>
						<a href="<?php if($i != 1){echo $wk_url.'page/'.$i.'/'.$wk_addon;}else{echo $wk_url.$wk_addon;} ?>" title="<?php echo $i; ?>"><?php echo $i; ?></a>
				<?php
					}
				}
				?>
				<a href="<?php if($paged != $last_page){$new_paged = $paged+1; echo $wk_url.'page/'.$new_paged.'/'.$wk_addon;}else{echo $_SERVER['REQUEST_URI'];} ?>">»</a>
				<a href="<?php if($last_page != 1){echo $wk_url.'page/'.$last_page.'/'.$wk_addon;}else{echo $wk_url.$wk_addon;} ?>" title="Τελευταία Σελίδα »"><?php echo $last_txt; ?> »</a>
			</span> <!-- close navi-choices -->
		</div> <!-- close wp-pagenavi -->
	<?php
	}else{
	?>
		<div id="wp-pagenavi">
			<span class="pages"><?php echo $page_txt; ?> <?php echo $paged; ?> <?php echo $from_txt; ?> <?php $last_page = intval($posts_num/10); $last_page++; echo $last_page; ?></span>
			<span class="navi-choices">
				<a href="<?php echo $wk_url; ?>" title="« Πρώτη Σελίδα">« <?php echo $first_txt; ?></a>
				<a href="<?php if($paged <= 2){echo $wk_url;}else{$new_paged = $paged-1; if($wk_addon != ''){echo $wk_url.'&page='.$new_paged;}else{echo $wk_url.'?page='.$new_paged;}} ?>">«</a>
				<?php
				if($paged < 4){
					$start = 1;
				}else{
					$start = $paged-1;
				}
				if($last_page - $paged >3){
					$end = $paged + 3;
				}else{
					$end = $last_page+1;
				}
				for($i=$start; $i<$end; $i++){
					if($i == $paged){
				?>
						<span class="current"><?php echo $paged; ?></span>
				<?php
					}else{
				?>
						<a href="<?php if($i != 1){if($wk_addon != ''){echo $wk_url.'&page='.$i;}else{echo $wk_url.'?page='.$i;}}else{echo $wk_url;} ?>" title="<?php echo $i; ?>"><?php echo $i; ?></a>
				<?php
					}
				}
				?>
				<a href="<?php if($paged != $last_page){$new_paged = $paged+1; if($wk_addon != ''){echo $wk_url.'&page='.$new_paged;}else{echo $wk_url.'?page='.$new_paged;}}else{echo $_SERVER['REQUEST_URI'];} ?>">»</a>
				<a href="<?php if($last_page != 1){if($wk_addon != ''){echo $wk_url.'&page='.$last_page;}else{echo $wk_url.'?page='.$last_page;}}else{echo $wk_url;} ?>" title="Τελευταία Σελίδα »"><?php echo $last_txt; ?> »</a>
			</span> <!-- close navi-choices -->
		</div> <!-- close wp-pagenavi -->
	<?php
	}
}
