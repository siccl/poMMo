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

class Pommo_Image
{
	/**
	 * @var string File
	 */
	private $_filePath;
	
	/**
	 * @var resource
	 */
	private $_imageHandle;
	
	/**
	 * @var array Info from getimagesize()
	 */
	private $_info;

	/**
	 * @param string $filePath
	 */
	public function __construct($filePath)
	{
		if (file_exists($filePath)) {
			$this->_filePath = $filePath;

			$info = getimagesize($filePath);

			$this->_info = array(
				'width' => $info[0],
				'height' => $info[1],
				'bits' => $info['bits'],
				'mime' => $info['mime']
			);

			$this->_imageHandle = $this->create($filePath);
		} else {
			exit('Error: Could not load image ' . $filePath . '!');
		}
	}

	/**
	 * @param string $filePath
	 * @return resource
	 */
	private function create($filePath)
	{
		$mime = $this->_info['mime'];

		if ($mime == 'image/gif') {
			return imagecreatefromgif($filePath);
		} elseif ($mime == 'image/png') {
			return imagecreatefrompng($filePath);
		} elseif ($mime == 'image/jpeg') {
			return imagecreatefromjpeg($filePath);
		}
	}

	/**
	 * Saves the stored image to a new file
	 * @param string $savePath
	 * @param int $quality
	 */
	public function save($savePath, $quality = 90)
	{
		$info = pathinfo($savePath);

		$extension = strtolower($info['extension']);

		if (is_resource($this->_imageHandle)) {
			if ($extension == 'jpeg' || $extension == 'jpg') {
				imagejpeg($this->_imageHandle, $savePath, $quality);
			} elseif ($extension == 'png') {
				imagepng($this->_imageHandle, $savePath, 0);
			} elseif ($extension == 'gif') {
				imagegif($this->_imageHandle, $savePath);
			}

			imagedestroy($this->_imageHandle);
		}
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @param bool $smallerOnly
	 * @return mixed
	 */
	public function resize($width = 0, $height = 0, $smallerOnly = false)
	{
		if (!$this->_info['width'] || !$this->_info['height']) {
			return;
		}
		
		if ($smallerOnly && $this->_info['width'] < $width && $this->_info['height'] < $height) {
			return;
		}

		$scale = min($width / $this->_info['width'], $height / $this->_info['height']);

		if ($scale == 1) {
			return;
		}

		$new_width = (int)($this->_info['width'] * $scale);
		$new_height = (int)($this->_info['height'] * $scale);
		$xpos = (int)(($width - $new_width) / 2);
		$ypos = (int)(($height - $new_height) / 2);

		$image_old = $this->_imageHandle;
		$this->_imageHandle = imagecreatetruecolor($width, $height);

		if (isset($this->_info['mime']) && $this->_info['mime'] == 'image/png') {
			imagealphablending($this->_imageHandle, false);
			imagesavealpha($this->_imageHandle, true);
			$background = imagecolorallocatealpha($this->_imageHandle, 255, 255, 255, 127);
			imagecolortransparent($this->_imageHandle, $background);
		} else {
			$background = imagecolorallocate($this->_imageHandle, 255, 255, 255);
		}

		imagefilledrectangle($this->_imageHandle, 0, 0, $width, $height, $background);

		imagecopyresampled($this->_imageHandle, $image_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->_info['width'], $this->_info['height']);
		imagedestroy($image_old);

		$this->_info['width'] = $width;
		$this->_info['height'] = $height;
	}

	/**
	 * 
	 * @param int $top_x
	 * @param int $top_y
	 * @param int $bottom_x
	 * @param int $bottom_y
	 */
	public function crop($top_x, $top_y, $bottom_x, $bottom_y)
	{
		$image_old = $this->_imageHandle;
		$this->_imageHandle = imagecreatetruecolor($bottom_x - $top_x, $bottom_y - $top_y);

		imagecopy($this->_imageHandle, $image_old, 0, 0, $top_x, $top_y, $this->_info['width'], $this->_info['height']);
		imagedestroy($image_old);

		$this->_info['width'] = $bottom_x - $top_x;
		$this->_info['height'] = $bottom_y - $top_y;
	}

	/**
	 * @param string $text
	 * @param int $x
	 * @param int $y
	 * @param int $size
	 * @param string $color
	 */
	private function text($text, $x = 0, $y = 0, $size = 5, $color = '000000')
	{
		$rgb = $this->html2rgb($color);

		imagestring($this->_imageHandle, $size, $x, $y, $text, imagecolorallocate($this->_imageHandle, $rgb[0], $rgb[1], $rgb[2]));
	}

	/**
	 * @param string $color
	 * @return array|bool
	 */
	private function html2Rgb($color)
	{
		if ($color[0] == '#') {
			$color = substr($color, 1);
		}

		if (strlen($color) == 6) {
			list($r, $g, $b) = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
		} elseif (strlen($color) == 3) {
			list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
		} else {
			return false;
		}

		$r = hexdec($r);
		$g = hexdec($g);
		$b = hexdec($b);

		return array($r, $g, $b);
	}
}

?>