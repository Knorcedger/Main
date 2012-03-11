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
		//add stylesheet url
		$stylesheet = get_bloginfo('stylesheet_url');
		//display the iframe
		$params = "name=$name&translation=$translation&width=$width&height=$height&size=$size&stylesheet=$stylesheet";
?>
		<iframe name="<?php echo $name; ?>" frameborder="0" marginwidth="0px" marginheight="0px" scrolling="no" src ="/wp-content/plugins/wk_submit/photo.php?<?php echo $params; ?>" width="100%" height="25px">
		</iframe>
		<?php
	}
}
?>
