<?php
//stores the names of vars sent to submit.php
$vars = array();

foreach ($fields as $val) {
	
	//seperate type and name
	$temp = explode('|', $val);
	$type = $temp[0];
	$name = $temp[1];
	$translation = $temp[2];

	//save names to send to form to be able to know how many vars to expect
	//if date, add 3 values
	if ($type == 'date') {
		array_push($vars, $name.'_day', $name.'_month', $name.'_year');
	} elseif ($type == 'time') {
		array_push($vars, $name.'_hour', $name.'_minute');
	} elseif ($type == 'checkbox') {
		//save and seperate checkbox values
		$allvalues = explode('~', $temp[3]);
		//find var_names to add to vars
		$i = 0;
		foreach ($allvalues as $val) {
			if ($i%2 == 0) {
				//do nothing
			} else {
				//var names are first (before values)
				array_push($vars, $val);
			}
			$i++;
		}
	} else {
		array_push($vars, $name);
	}

	if ($type == 'textfield') {
		//include file
		include_once $path . 'textfield.php';
		//save remaining vars
		$size = $temp[3];
		wk_submit_textfield($name, $translation, $size, $object_id, $object_type);
	} elseif ($type == 'textarea') {
		//include file
		include_once 'textarea.php';
		//save remaining vars
		$cols = $temp[3];
		$rows = $temp[4];
		wk_submit_textarea($name, $translation, $cols, $rows, $object_id, $object_type);
	} elseif ($type == 'category') {
		//include file
		include_once 'category.php';
		wk_submit_category($name, $translation, $object_id, $object_type);
	} elseif ($type == 'date') {
		//include file
		include_once 'date.php';
		wk_submit_date($name, $translation, $language, $object_id, $object_type);
	} elseif ($type == 'time') {
		//include file
		include_once 'time.php';
		//save remaining vars
		$precision = $temp[3];
		wk_submit_time($name, $translation, $precision, $language, $object_id, $object_type);
	} elseif ($type == 'dropdown') {
		//include file
		include_once 'dropdown.php';
		//save remaining vars
		//empty arrys in order for the second dd not to remember values of the first
		$allvalues = '';
		$myvalues = '';
		//save and seperate dropdown values
		$allvalues = explode('~', $temp[3]);
		//format allvalues in couples to send to function
		$i = 0;
		foreach ($allvalues as $val) {
			if ($i%2 == 0) {
				$temp2 = $val;
			} else {
				$myvalues[$i/2] = $temp2.'|'.$val;
			}
			$i++;
		}
		wk_submit_dropdown($name, $translation, $myvalues, $object_id, $object_type);
	} elseif ($type == 'checkbox') {
		//include file
		include_once 'checkbox.php';
		//save remaining vars
		//save and seperate checkbox values
		$allvalues = explode('~', $temp[3]);
		//format allvalues in couples to send to function
		$i = 0;
		foreach ($allvalues as $val) {
			if ($i%2 == 0) {
				$temp2 = $val;
			} else {
				$myvalues[$i/2] = $temp2.'|'.$val;
			}
			$i++;
		}
		wk_submit_checkbox($name, $translation, $myvalues, $object_id, $object_type);
	} elseif ($type == 'photo') {
		//save remaining vars
		$width = $temp[3];
		$height = $temp[4];
		$size = $temp[5];
		$thumb_details = explode('~', $temp[6]);
		$thumb = $thumb_details[0];
		if($thumb){
			$thumb_width = $thumb_details[1];
			$thumb_height = $thumb_details[2];
			$cropratio = $thumb_details[3];
		}else{
			$thumb_width = 0;
			$thumb_height = 0;
		}
		//add stylesheet url
		$stylesheet = get_bloginfo('stylesheet_url');
		//display the button
		echo '<div class="'.$name.'-info"><div class="' . $name . '-button">Upload</div></div>';
		//display the iframe
		$params = "name=$name&translation=$translation&width=$width&height=$height&size=$size&stylesheet=$stylesheet&thumb=$thumb&thumb_width=$thumb_width&thumb_height=$thumb_height&cropratio=$cropratio";
?>
		<script type="text/javascript">
			function form_submitted(){
				//replace the button with the loading pic
				$("div.<?php echo $name; ?>-info div.<?php echo $name; ?>-button").replaceWith("<img class='<?php echo $name; ?>-loading' src='/wp-content/plugins/wk_submit/loading.gif' alt='loading' />");
				//remove any errors
				$("div.<?php echo $name; ?>-info span.<?php echo $name; ?>-error").hide();
			}
			function show_photo_info(thumb_url, photo_name, photo_url, delete_url){
				//replace the loading pic with the info
				//check if we display a thumb
				<?php if($thumb){ ?>
					$("div.<?php echo $name; ?>-info img.<?php echo $name; ?>-loading").replaceWith('<span class="<?php echo $name; ?>-thumb-info"><span class="photo-thumb"><img src="'+thumb_url+'" alt="<?php echo $name; ?>" /></span><span class="photo-name"><a href="'+photo_url+'" target="_blank">'+photo_name+'</a></span><span class="photo-delete"><a href="javascript:delete_this(\''+delete_url+'\');">Delete</a></span></span>');
				<?php }else{ ?>
					$("div.<?php echo $name; ?>-info img.<?php echo $name; ?>-loading").replaceWith('<span class="<?php echo $name; ?>-thumb-info"><span class="photo-name"><a href="'+photo_url+'" target="_blank">'+photo_name+'</a></span><span class="photo-delete"><a href="javascript:delete_this(\''+delete_url+'\');">Delete</a></span></span>');
				<?php } ?>
				//remove the iframe
				$("iframe#avatar").hide();
			}
			function show_error(message){
				//alert(message);
				$("div.<?php echo $name; ?>-info img.<?php echo $name; ?>-loading").replaceWith('<span class="<?php echo $name; ?>-error">'+message+'</span><div class="<?php echo $name; ?>-button">Upload</div>')
				//reload the iframe to show the form
				window.avatar.location = "/wp-content/plugins/wk_submit/photo.php?<?php echo $params; ?>";
			}
			function delete_this(delete_url){
				//delete the file and the post meta
				$.post(delete_url);
				//show the button again
				$("div.<?php echo $name; ?>-info span.<?php echo $name; ?>-thumb-info").replaceWith('<div class="<?php echo $name; ?>-button">Upload</div>');
				//reshow the iframe
				$("iframe#avatar").show();
				//reload the iframe to show the form
				window.avatar.location = "/wp-content/plugins/wk_submit/photo.php?<?php echo $params; ?>";
			}
		</script>
		<iframe id="<?php echo $name; ?>" name="<?php echo $name; ?>" frameborder="0" marginwidth="0px" marginheight="0px" scrolling="no" src ="/wp-content/plugins/wk_submit/photo.php?<?php echo $params; ?>" width="100%" height="200spx">
		</iframe>
		<?php
	}
}
?>
