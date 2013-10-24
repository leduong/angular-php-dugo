<?php
class Controller_Admin_Filemanager extends Controller
{
	public function index()
	{
		
		$root_path = UPLOAD;
		$root_url = HTTP_SERVER.'/uploads/';
		$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');

		//get path &URL
		if (empty($_GET['path'])) {
			$current_path = realpath($root_path) . '/';
			$current_url = $root_url;
			$current_dir_path = '';
			$moveup_dir_path = '';
		} else {
			$current_path = realpath($root_path) . '/' . $_GET['path'];
			$current_url = $root_url . $_GET['path'];
			$current_dir_path = $_GET['path'];
			$moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
		}
		//sort by name, size or type
		$order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

		//viewing parent NOT allowed
		if (preg_match('/\.\./', $current_path)) {
			echo 'Access is not allowed.';
			exit;
		}
		//Not endding with slash
		if (!preg_match('/\/$/', $current_path)) {
			echo 'Parameter is not valid.';
			exit;
		}
		//file or dir not exist
		if (!file_exists($current_path) || !is_dir($current_path)) {
			echo 'Directory does not exist.';
			exit;
		}

		//go through children file & folder
		$file_list = array();
		if ($handle = opendir($current_path)) {
			$i = 0;
			while (false !== ($filename = readdir($handle))) {
				if ($filename{0} == '.') continue;
				$file = $current_path . $filename;
				if (is_dir($file)) {
					$file_list[$i]['is_dir'] = true; //sub folder
					$file_list[$i]['has_file'] = (count(scandir($file)) > 2); //is empty folder
					$file_list[$i]['filesize'] = 0; //file size
					$file_list[$i]['is_photo'] = false; //Picture
					$file_list[$i]['filetype'] = ''; //file type
				} else {
					$file_list[$i]['is_dir'] = false;
					$file_list[$i]['has_file'] = false;
					$file_list[$i]['filesize'] = filesize($file);
					$file_list[$i]['dir_path'] = '';
					$file_ext = strtolower(array_pop(explode('.', trim($file))));
					$file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
					$file_list[$i]['filetype'] = $file_ext;
				}
				$file_list[$i]['filename'] = $filename; //file name with extension
				$file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //Modification Date
				$i++;
			}
			closedir($handle);
		}

		$result = array();
		//parent folder based on root
		$result['moveup_dir_path'] = $moveup_dir_path;
		//current path based on root 
		$result['current_dir_path'] = $current_dir_path;
		//current URL
		$result['current_url'] = $current_url;
		//total count
		$result['total_count'] = count($file_list);
		//file list
		$result['file_list'] = $file_list;

		//JSON result
		header('Content-type: application/json; charset=UTF-8');
		echo json_encode($result);
		exit;
	}
	public function upload(){
		$save_path = UPLOAD;
		$save_url = HTTP_SERVER.'/uploads/';
		$ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
		//allowed max size
		$max_size = 1000000;

		//something being uploaded
		if (empty($_FILES) === false) {
			//original name
			$file_name = $_FILES['imgFile']['name'];
			//temp name
			$tmp_name = $_FILES['imgFile']['tmp_name'];
			//file size
			$file_size = $_FILES['imgFile']['size'];
			//check file name
			if (!$file_name) {
				alert("Please select a file to upload.");
			}
			//check directory to save
			if (@is_dir($save_path) === false) {
				alert("Path NOT exists");
			}
			//check permission
			if (@is_writable($save_path) === false) {
				alert("No permission to write.");
			}
			//check if it has been uploaded
			if (@is_uploaded_file($tmp_name) === false) {
				alert("This file has been uploaded before.");
			}
			//check file size
			if ($file_size > $max_size) {
				alert("File size exceed");
			}
			//get file extension
			$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);
			$file_ext = strtolower($file_ext);
			//check file extensions
			if (in_array($file_ext, $ext_arr) === false) {
				alert("File extension NOT allowed.");
			}
			//new file name 
			$new_file_name = date("Ymd").'_'.dechex(date("YmdHms")).'.'.$file_ext;
			//move file
			$file_path = $save_path . $new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				alert("Attempt to upload file failed.");
			}
			@chmod($file_path, 0644);
			$file_url = $save_url . $new_file_name;
			//insert picture & close window
			echo '<html>';
			echo '<head>';
			echo '<title>Insert Image</title>';
			echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
			echo '</head>';
			echo '<body>';
			echo '<script type="text/javascript">';
			echo 'parent.parent.KE.plugin["image"].insert("' . $_POST['id'] . '", "' . $file_url . '","' . $_POST['imgTitle'] . '","' . $_POST['imgWidth'] . '","' . $_POST['imgHeight'] . '","' . $_POST['imgBorder'] . '","' . $_POST['align'] . '");';
			echo '</script>';
			echo '</body>';
			echo '</html>';
		}
		exit;
		//show message & close window
	}
	protected function alert($msg)
	{
		echo '<html>';
		echo '<head>';
		echo '<title>error</title>';
		echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">';
		echo '</head>';
		echo '<body>';
		echo '<script type="text/javascript">alert("'.$msg.'");</script>';
		echo '</body>';
		echo '</html>';
		exit;
	}
}