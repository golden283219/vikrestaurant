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

/**
 * Utility class working with colors.
 *
 * @since  1.8
 */
abstract class VREHtmlColor
{
	/**
	 * Converts a RGB color in a HEX string.
	 * This function accepts up to 3 arguments (r,g,b) or
	 * an array/object that contains the color attributes.
	 *
	 * Usage:
	 * rgb2hex(128, 128, 128);
	 * rgb2hex(array('r' => 128, 'g' => 128, 'b' => 128));
	 * rgb2hex(array('red' => 128, 'green' => 128, 'blue' => 128));
	 *
	 * @return 	string 	The hex color (prefixed with #).
	 */
	public static function rgb2hex()
	{
		$args = func_get_args();
		$rgb  = array();
		$n    = count($args);

		if ($n >= 3)
		{
			$rgb[] = $args[0];
			$rgb[] = $args[1];
			$rgb[] = $args[2];
			$rgb[] = array_pop($args); // alpha (if specified)
		}
		else 
		{
			// use array notation
			$color = (array) array_shift($args);

			// find RED color
			if (isset($color['r']))
			{
				$rgb[] = $color['r'];
			}
			else if (isset($color['red']))
			{
				$rgb[] = $color['red'];
			}

			// find GREEN color
			if (isset($color['g']))
			{
				$rgb[] = $color['g'];
			}
			else if (isset($color['green']))
			{
				$rgb[] = $color['green'];
			}

			// find BLUE color
			if (isset($color['b']))
			{
				$rgb[] = $color['b'];
			}
			else if (isset($color['blue']))
			{
				$rgb[] = $color['blue'];
			}

			// find ALPHA
			if (isset($color['a']))
			{
				$rgb[] = $color['a'];
			}
			else if (isset($color['alpha']))
			{
				$rgb[] = $color['alpha'];
			}
		}

		// check RGB length
		if (count($rgb) < 3)
		{
			return false;
		}

		// convert to HEX
		$hex = '#'
			. sprintf('%02x', $rgb[0])
			. sprintf('%02x', $rgb[1])
			. sprintf('%02x', $rgb[2]);

		if (count($rgb) == 4)
		{
			if (strpos($rgb[3], '%') !== false)
			{
				// percentage value
				$rgb[3] = intval($rgb[3]);
				$rgb[3] *= 255 / 100;
			}

			$hex .= sprintf('%02x', $rgb[3]);
		}

		return $hex;
	}

	/**
	 * Converts a HEX color in RGB representation.
	 *
	 * @param 	string 	 $hex 	  The HEX color (prefixed or not with #)
	 * @param 	boolean  $string  True to return the RGB string.
	 *
	 * @return 	mixed 	 The RGB object or its string.
	 */
	public static function hex2rgb($hex, $string = false)
	{
		// trim # at the beginning, if any
		$hex = ltrim($hex, '#');
		$len = strlen($hex);

		// validate HEX color
		if (preg_match('/[^0-9a-f]/i', $hex) || !in_array($len, array(3, 4, 6, 8)))
		{
			return false;
		}
		
		// when length is 3 or 4, repeat every char twice
		if ($len == 3 || $len == 4)
		{
			$hex = preg_replace('/(.)/', '$1$1', $hex);
		}

		if (strlen($hex) == 6)
		{
			// maximum opacity
			$hex .= 'FF';
		}
		
		$color = new stdClass;
		$color->red 	= (int) hexdec(substr($hex, 0, 2));
		$color->green 	= (int) hexdec(substr($hex, 2, 2));
		$color->blue 	= (int) hexdec(substr($hex, 4, 2));
		$color->alpha 	= (int) hexdec(substr($hex, 6, 2));

		// calculate alpha percentage
		$color->alphaPercent = $color->alpha / 255;
		
		if ($string)
		{
			if ($len == 4 || $len == 8)
			{
				// display RGBA
				$color = sprintf('rgba(%d, %d, %d, %.2f)', $color->red, $color->green, $color->blue, $color->alphaPercent);
			}
			else
			{
				// display RGB
				$color = sprintf('rgb(%d, %d, %d)', $color->red, $color->green, $color->blue);
			}
		}

		return $color;
	}

	/**
	 * Checks whether the specified color is light.
	 *
	 * @param 	mixed 	 $color  Either a HEX string or a RGB object.
	 *
	 * @return 	boolean  True if light, false otherwise.
	 */
	public static function light($color)
	{
		if (is_string($color))
		{
			// convert HEX color to RGB
			$color = self::hex2rgb($color);
		}

		// HSP (Highly Sensitive Poo) equation
	    $hsp = sqrt(
	    	0.299 * pow($color->red, 2) +
	    	0.587 * pow($color->green, 2) +
	    	0.114 * pow($color->blue, 2)
	    );

	    // using the HSP value, determine whether the color is light or dark
	    // return $hsp > 127.5;
	    return $hsp > 160;
	}

	/**
	 * Checks whether the specified color is dark.
	 *
	 * @param 	mixed 	 $color  Either a HEX string or a RGB object.
	 *
	 * @return 	boolean  True if dark, false otherwise.
	 */
	public static function dark($color)
	{
		return self::light($color) === false;
	}

	/**
	 * Generates a random HEX(A) color.
	 *
	 * @param 	boolean  $alpha  True to include the opacity for HEXA.
	 *
	 * @return  string 	 The hex color.
	 */
	public static function random($alpha = false)
	{
		if ($alpha)
		{
			// use alpha
			$max = 0xFFFFFFFF;
			$pad = 8;
		}
		else
		{
			// do not use alpha
			$max = 0xFFFFFF;
			$pad = 6;
		}

		// generate color
		return '#' . strtoupper(str_pad(dechex(mt_rand(0, $max)), $pad, '0', STR_PAD_LEFT));
	}
}
