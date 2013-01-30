<?php
/**
 * Copyright (C) 2005, 2006, 2007, 2008  Brice Burgess <bhb@iceburg.net>
 * 
 * This file is part of poMMo (http://www.pommo.org)
 * 
 * poMMo is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published 
 * by the Free Software Foundation; either version 2, or any later version.
 * 
 * poMMo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
 * the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with program; see the file docs/LICENSE. If not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**********************************
	INITIALIZATION METHODS
*********************************/
require ('../bootstrap.php');

Pommo::init();
$logger = & Pommo::$_logger;
$dbo = & Pommo::$_dbo;

/**********************************
	JSON OUTPUT INITIALIZATION
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Json.php');
require_once(Pommo::$_baseDir.'classes/Pommo_File.php');
$json = new Pommo_Json();
$json = array();

define('ROOT_IMAGE_DIRECTORY', '../uploadedimages/');
define('IMAGE_DIRECTORY', ROOT_IMAGE_DIRECTORY . 'data/');

// EXAMINE CALL
switch ($_REQUEST['action']) {
	
	case 'directory': 
		$directories = glob(rtrim(IMAGE_DIRECTORY . str_replace('../', '', $_POST['directory']), '/') . '/*', GLOB_ONLYDIR); 
		
		if ($directories) {
			$i = 0;
			foreach ($directories as $directory) {
				$json[$i]['data'] = basename($directory);
				$json[$i]['attributes']['directory'] = mb_substr($directory, strlen(IMAGE_DIRECTORY));
				
				$children = glob(rtrim($directory, '/') . '/*', GLOB_ONLYDIR);
				
				if ($children)  {
					$json[$i]['children'] = ' ';
				}
				
				$i++;
			}
		}
		
		break;
	
	case 'files' :
		
		if (!empty($_POST['directory'])) {
			$directory = IMAGE_DIRECTORY . str_replace('../', '', $_POST['directory']);
		} else {
			$directory = IMAGE_DIRECTORY;
		}
		
		$allowed = array(
			'.jpg',
			'.jpeg',
			'.png',
			'.gif',
            '.pdf',
            '.ico'
		);
		
		$files = glob(rtrim($directory, '/') . '/*');
		
		if ($files) {
			foreach ($files as $file) {
				if (is_file($file)) {
					$ext = strrchr($file, '.');
				} else {
					$ext = '';
				}	
				
				if (in_array(strtolower($ext), $allowed)) {
					$size = filesize($file);
		
					$i = 0;
		
					$suffix = array(
						'B',
						'KB',
						'MB',
						'GB',
						'TB',
						'PB',
						'EB',
						'ZB',
						'YB'
					);
		
					while (($size / 1024) > 1) {
						$size = $size / 1024;
						$i++;
					}
						
					$json[] = array(
						'filename' => basename($file),
						'file'     => mb_substr($file, strlen(IMAGE_DIRECTORY)),
						'size'     => round(mb_substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]
					);
				}
			}
		}

		break;
	
	case 'image' :
		require_once('../classes/Pommo_Image.php');
		$imagePath = $_GET['image'];
		$thumbWidth = 100;
		$thumbHeight = 100;
		
		if (!file_exists(IMAGE_DIRECTORY . $imagePath) || !is_file(IMAGE_DIRECTORY . $imagePath)) {
			exit;
		}
		
		$info = pathinfo($imagePath);
		$extension = $info['extension'];
		
		$old_image = $imagePath;
		$new_image = 'cache/' . mb_substr($imagePath, 0, strrpos($imagePath, '.')) . '-' . $thumbWidth . 'x' . $thumbHeight . '.' . $extension;
		
		if (!file_exists(ROOT_IMAGE_DIRECTORY . $new_image) || (filemtime(ROOT_IMAGE_DIRECTORY . $old_image) > filemtime($rootImageDirectory . $new_image))) {
			$path = '';
			
			$directories = explode('/', dirname(str_replace('../', '', $new_image)));
			
			foreach ($directories as $directory) {
				$path = $path . '/' . $directory;
				
				if (!file_exists(ROOT_IMAGE_DIRECTORY . $path)) {
					@mkdir(ROOT_IMAGE_DIRECTORY . $path, 0777);
				}		
			}
			
			
			$image = new Pommo_Image(IMAGE_DIRECTORY . $old_image);
			$image->resize($thumbWidth, $thumbHeight, true);
			$image->save(ROOT_IMAGE_DIRECTORY . $new_image);
		}
	
		echo mb_substr(ROOT_IMAGE_DIRECTORY . $new_image, 3);
		exit;
	case 'create':
		$directory = '';
		
		if (isset($_POST['directory'])) {
			if (isset($_POST['name']) || $_POST['name']) {
				$directory = rtrim(IMAGE_DIRECTORY . str_replace('../', '', $_POST['directory']), '/');							   
				
				if (!is_dir($directory)) {
					$json['error'] = _('Warning: Please select a directory!');
				}
				
				if (file_exists($directory . '/' . str_replace('../', '', $_POST['name']))) {
					$json['error'] = _('Warning: A file or directory with the same name already exists!');
				}
			} else {
				$json['error'] = _('Warning: Please enter a new name!');
			}
		} else {
			$json['error'] = _('Warning: Please select a directory!');
		}
		
		if (!isset($json['error'])) {	
			mkdir($directory . '/' . str_replace('../', '', $_POST['name']), 0777);
			
			$json['success'] = _('Success: Directory created!');
		}	
		break;
	
	case 'delete':
		
		$path = '';
		if (isset($_POST['path'])) {
			$path = rtrim(IMAGE_DIRECTORY . str_replace('../', '', html_entity_decode($_POST['path'], ENT_QUOTES, 'UTF-8')), '/');
			 
			if (!file_exists($path)) {
				$json['error'] = _('Warning: Please select a directory or file!');
			}
			
			if ($path == rtrim(IMAGE_DIRECTORY, '/')) {
				$json['error'] = _('Warning: You can not delete this directory!');
			}
		} else {
			$json['error'] = _('Warning: Please select a directory or file!');
		}
		
		if (!isset($json['error'])) {
			$fileObject = new Pommo_File($path);
			$fileObject->recursiveDelete();
			$json['success'] = _('Success: Your file or directory has been deleted!');
		}				
		
		break;
	
	case 'upload':
		
		if (isset($_POST['directory'])) {
			if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
				$filename = basename(html_entity_decode($_FILES['image']['name'], ENT_QUOTES, 'UTF-8'));
				
				if ((strlen($filename) < 3) || (strlen($filename) > 255)) {
					$json['error'] = _('Warning: Filename must be a between 3 and 255!');
				}
					
				$directory = rtrim(IMAGE_DIRECTORY . str_replace('../', '', $_POST['directory']), '/');
				
				if (!is_dir($directory)) {
					$json['error'] = _('Warning: Please select a directory!');
				}
				
				if ($_FILES['image']['size'] > 500000) {
					$json['error'] = _('Warning: File too big please keep below 500kb and no more than 1000px height or width!');
				}
				
				$allowed = array(
					'image/jpeg',
					'image/pjpeg',
					'image/png',
					'image/x-png',
					'image/gif'
				);
						
				if (!in_array($_FILES['image']['type'], $allowed)) {
					$json['error'] = _('Warning: Incorrect file type!') . ': ' . $_FILES['image']['type'];
				}
				
				$allowed = array(
					'.jpg',
					'.jpeg',
					'.gif',
					'.png',
					'.flv',
                    '.pdf',
                    '.ico'
				);
						
				if (!in_array(strtolower(strrchr($filename, '.')), $allowed)) {
					$json['error'] = _('Warning: Incorrect file type!') . ': ' . strtolower(strrchr($filename, '.'));
				}

				if ($_FILES['image']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = 'error_upload_' . $_FILES['image']['error'];
				}			
			} else {
				$json['error'] = _('Warning: Please select a file!');
			}
		} else {
			$json['error'] = _('Warning: Please select a directory!');
		}
		
		if (!isset($json['error'])) {	
			if (@move_uploaded_file($_FILES['image']['tmp_name'], $directory . '/' . $filename)) {		
				$json['success'] = _('Success: Your file has been uploaded!');
			} else {
				$json['error'] = _('Warning: File could not be uploaded for an unknown reason!');
			}
		}
		
		break;
	
	case 'move':
		
		if (isset($_POST['from']) && isset($_POST['to'])) {
			$from = rtrim(IMAGE_DIRECTORY . html_entity_decode($_POST['from'], ENT_QUOTES, 'UTF-8'), '/');
			
			if (!file_exists($from)) {
				$json['error'] = _('Warning: File or directory does not exist!');
			}
			
			if ($from . '/' == IMAGE_DIRECTORY) {
				$json['error'] = _('Warning: Can not alter your root directory!');
			}
			
			$to = rtrim(IMAGE_DIRECTORY . html_entity_decode($_POST['to'], ENT_QUOTES, 'UTF-8'), '/');

			if (!file_exists($to)) {
				$json['error'] = _('Warning: Move to directory does not exists!');
			}	
			
			if (file_exists($to . '/' . basename($from))) {
				$json['error'] = _('Warning: A file or directory with the same name already exists!');
			}
		} else {
			$json['error'] = _('Warning: Please select a directory!');
		}
		
		if (!isset($json['error'])) {
			rename($from, $to . '/' . basename($from));
			
			$json['success'] = _('Success: Your file or directory has been moved!');
		}
		
		break;
	
	case 'folders':
		
		$fileObject = new Pommo_File(IMAGE_DIRECTORY);
		$folders = $fileObject->getAllFoldersRecursive();
		
		$options = '';
		foreach ($folders as $folder) {
			$pathRelative = mb_substr($folder, strlen(IMAGE_DIRECTORY));
			$depth = substr_count($pathRelative, '/');
			$options .= '<option value="' . $pathRelative . '">' 
				. str_repeat('.', $depth * 4) . $pathRelative . '</option>';
		}
		
		echo $options;
		exit;
		
	case 'copy':
		if (isset($_POST['path']) && isset($_POST['name'])) {
			if ((mb_strlen($_POST['name']) < 3) || (mb_strlen($_POST['name']) > 255)) {
				$json['error'] = _('Warning: Filename must be a between 3 and 255!');
			}
				
			$old_name = rtrim(IMAGE_DIRECTORY . str_replace('../', '', html_entity_decode($_POST['path'], ENT_QUOTES, 'UTF-8')), '/');
			
			if (!file_exists($old_name) || $old_name . '/' == IMAGE_DIRECTORY) {
				$json['error'] = _('Warning: Can not copy this file or directory!');
			}
			
			if (is_file($old_name)) {
				$ext = strrchr($old_name, '.');
			} else {
				$ext = '';
			}		
			
			$new_name = dirname($old_name) . '/' . str_replace('../', '', html_entity_decode($_POST['name'], ENT_QUOTES, 'UTF-8') . $ext);
																			   
			if (file_exists($new_name)) {
				$json['error'] = _('Warning: A file or directory with the same name already exists!');
			}			
		} else {
			$json['error'] = _('Warning: Please select a directory or file!');
		}
		
		if (!isset($json['error'])) {
			$fileObject = new Pommo_File($old_name);
			$fileObject->recursiveCopy($new_name);
			
			$json['success'] = _('Success: Your file or directory has been copied!');
		}
		
		break;
	
	case 'rename':
		if (isset($_POST['path']) && isset($_POST['name'])) {
			if ((mb_strlen($_POST['name']) < 3) || (mb_strlen($_POST['name']) > 255)) {
				$json['error'] = _('Warning: Filename must be a between 3 and 255!');
			}
				
			$old_name = rtrim(IMAGE_DIRECTORY . str_replace('../', '', html_entity_decode($_POST['path'], ENT_QUOTES, 'UTF-8')), '/');
			
			if (!file_exists($old_name) || $old_name . '/' == IMAGE_DIRECTORY) {
				$json['error'] = _('Warning: Can not rename this directory!');
			}
			
			if (is_file($old_name)) {
				$ext = strrchr($old_name, '.');
			} else {
				$ext = '';
			}		
			
			$new_name = dirname($old_name) . '/' . str_replace('../', '', html_entity_decode($_POST['name'], ENT_QUOTES, 'UTF-8') . $ext);
																			   
			if (file_exists($new_name)) {
				$json['error'] = _('Warning: A file or directory with the same name already exists!');
			}			
		}
		
		if (!isset($json['error'])) {
			rename($old_name, $new_name);
			
			$json['success'] = _('Success: Your file or directory has been renamed!');
		}
		
		break;
	
	default:
		die('invalid request passed to '.__FILE__);
	break;
}

echo json_encode($json);
die();
?>
