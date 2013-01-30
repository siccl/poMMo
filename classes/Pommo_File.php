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

/**
 * A class for working with files and directories
 */
class Pommo_File
{
	/**
	 * The path to the file or directory associated with this class
	 * @var string
	 */
	protected $_filePath = null;
	
	/**
	 * @param string $filePath
	 */
	public function __construct($filePath)
	{
		$this->_filePath = $filePath;
	}
	
	/**
	 * Deletes this file/directory and all its children
	 * @param string $filePath
	 * @return bool
	 */
	public function recursiveDelete($filePath = null) 
	{
		if ($filePath === null) {
			$filePath = $this->_filePath;
		}
		$handle = false;
		if (is_dir($filePath)) {
			$handle = opendir($filePath);
		} elseif (is_file($filePath)) {
			unlink($filePath);
			return true;
		}
		
		if (!$handle) {
			return false;
		}
		
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				self::recursiveDelete($filePath . '/' . $file);
			}
		}
		
		closedir($handle);
		
		rmdir($filePath);
		
		return true;
	}
	
	/**
	 * Copies this file/directory and all its children to $destination
	 * @param string $destination
	 * @param string $source
	 * @return bool
	 */
	public function recursiveCopy($destination, $source = null) 
	{ 
		if ($source === null) {
			$source = $this->_filePath;
		}
		if (is_file($source)) {
			copy($source, $destination);
			return true;
		}
		$directory = opendir($source); 
		
		@mkdir($destination); 
		
		while (false !== ($file = readdir($directory))) {
			if (($file != '.') && ($file != '..')) { 
				$this->recursiveCopy($destination . '/' . $file, $source . '/' . $file); 
			} 
		} 
		
		closedir($directory); 
		return true;
	} 
	
	/**
	 * Gets an array list of all the sub-directories found in this directory... recursively
	 * @param string $directory
	 * @return array
	 */
	public function getAllFoldersRecursive($directory = null) 
	{
		if ($directory === null) {
			$directory = $this->_filePath;
		}
		$allFolders = array($directory);

		$directories = glob(rtrim($directory, '/') . '/*', GLOB_ONLYDIR);
		
		foreach ($directories as $subDirectory) {
			$allFolders = array_merge($allFolders, self::getAllFoldersRecursive($subDirectory));
		}
		
		return $allFolders;
	}
}
?>
