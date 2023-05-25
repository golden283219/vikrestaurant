<?php
/** 
 * @package     VikRestaurants
 * @subpackage  com_vikrestaurants
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class ImageResizer
{	
	public static function proportionalImage($fileimg, $dest, $towidth, $toheight)
	{
		if (!file_exists($fileimg)) {
			return false;
		}

		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}
		
		list($owid, $ohei, $type) = getimagesize($fileimg);

		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;

			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
			}

			$imageresized = imagecreatetruecolor($new_width, $new_height);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					$imagetmp = imagecreatefrompng($fileimg);
			}

			imagecopyresampled($imageresized, $imagetmp, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);
		
			switch ($type) {
				case '1' :
					imagegif($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					imagepng($imageresized, $dest);
			}
		
			imagedestroy($imageresized);

			return true;

		} else {
			copy($fileimg, $dest);
		}

		return true;
	}
	
	public static function bandedImage($fileimg, $dest, $towidth, $toheight, $rgb)
	{
		if (!file_exists($fileimg)) {
			return false;
		}

		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}
		
		$exp = explode(",", $rgb);

		if (count($exp) == 3) {
			$r = trim($exp[0]);
			$g = trim($exp[1]);
			$b = trim($exp[2]);
		} else {
			$r = 0;
			$g = 0;
			$b = 0;
		}
		
		
		list($owid, $ohei, $type) = getimagesize($fileimg);
		
		if ($owid > $towidth || $ohei > $toheight) {
			$xscale = $owid / $towidth;
			$yscale = $ohei / $toheight;
			
			if ($yscale > $xscale) {
				$new_width = round($owid * (1 / $yscale));
				$new_height = round($ohei * (1 / $yscale));
				$ydest = 0;
				$diff = $towidth - $new_width;
				$xdest = ($diff > 0 ? round($diff / 2) : 0);
			} else {
				$new_width = round($owid * (1 / $xscale));
				$new_height = round($ohei * (1 / $xscale));
				$xdest = 0;
				$diff = $toheight - $new_height;
				$ydest = ($diff > 0 ? round($diff / 2) : 0);
			}
	
			$imageresized = imagecreatetruecolor($towidth, $toheight);

			$bgColor = imagecolorallocate($imageresized, (int)$r, (int)$g, (int)$b);
			imagefill($imageresized, 0, 0, $bgColor);

			switch ($type) {
				case '1' :
					$imagetmp = imagecreatefromgif($fileimg);
					break;
				case '2' :
					$imagetmp = imagecreatefromjpeg($fileimg);
					break;
				default :
					$imagetmp = imagecreatefrompng($fileimg);
			}

			imagecopyresampled($imageresized, $imagetmp, $xdest, $ydest, 0, 0, $new_width, $new_height, $owid, $ohei);

			switch ($type) {
				case '1' :
					imagegif($imageresized, $dest);
					break;
				case '2' :
					imagejpeg($imageresized, $dest);
					break;
				default :
					imagepng($imageresized, $dest);
			}

			imagedestroy($imageresized);
			
			return true;

		} else {
			copy($fileimg, $dest);
		}

		return true;
	}
	
	public static function croppedImage($fileimg, $dest, $towidth, $toheight)
	{
		if (!file_exists($fileimg)) {
			return false;
		}
		if (empty($towidth) && empty($toheight)) {
			copy($fileimg, $dest);
			return true;
		}
		
		list($owid, $ohei, $type) = getimagesize($fileimg);
		
		if ($owid <= $ohei) {
			$new_width = $towidth;
			$new_height = ($towidth/$owid)*$ohei;
		} else {
			$new_height = $toheight;
			$new_width = ($new_height/$ohei)*$owid;   
		}
		
		switch ($type) {
			case '1':
				$img_src=imagecreatefromgif($fileimg);
				$img_dest=imagecreate($new_width, $new_height);
				break;
			case '2':
				$img_src=imagecreatefromjpeg($fileimg);
				$img_dest=imagecreatetruecolor($new_width, $new_height);
				break;
			default:
				$img_src=imagecreatefrompng($fileimg);
				$img_dest=imagecreatetruecolor($new_width, $new_height);
		}
		
		imagecopyresampled($img_dest, $img_src, 0, 0, 0, 0, $new_width, $new_height, $owid, $ohei);
		
		switch ($type) {
			case '1':
				$cropped=imagecreate($towidth, $toheight);
				break;
			case '2':
				$cropped=imagecreatetruecolor($towidth, $toheight);
				break;
			default:
				$cropped=imagecreatetruecolor($towidth, $toheight);
		}
		
		imagecopy($cropped, $img_dest, 0, 0, 0, 0, $owid, $ohei);
		
		switch ($type) {
			case '1' :
				imagegif($cropped, $dest);
				break;
			case '2' :
				imagejpeg($cropped, $dest);
				break;
			default :
				imagepng($cropped, $dest);
		}
		
		imagedestroy($img_dest);
		imagedestroy($cropped);
		
		return true;
	}
	
}
