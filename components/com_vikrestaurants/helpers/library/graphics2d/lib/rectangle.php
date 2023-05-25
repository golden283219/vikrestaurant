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
 * Helper class to handle Rectangle shapes.
 * This class implements the Shape interface methods.
 *
 * @see 	Point 	Used to handle the center.
 *
 * @since  	1.7
 */
class Rectangle implements Shape
{
	/**
	 * The width of the rectangle.
	 *
	 * @var float
	 */
	private $width = 0;

	/**
	 * The height of the rectangle.
	 *
	 * @var float
	 */
	private $height = 0;

	/**
	 * Class constructor.
	 *
	 * @param 	float 	$width 		The rectangle width.
	 * @param 	float 	$height 	The rectangle height.
	 *
	 * @uses 	setWidth() 		Rectangle width setter.
	 * @uses 	setHeight() 	Rectangle height setter.
	 */
	public function __construct($width = 0, $height = 0)
	{
		$this->setWidth($width)->setHeight($height);
	}

	/**
	 * Set the width of the rectangle as the absolute passed value.
	 * @usedby 	Rectangle::__construct()
	 *
	 * @param 	float 	$width 	The rectangle width.
	 *
	 * @return 	Rectangle 	This object to support chaining.
	 */
	public function setWidth($width)
	{
		$this->width = abs($width);

		return $this;
	}

	/**
	 * Get the width of the rectangle.
	 *
	 * @return 	float 	The rectangle width.
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * Set the height of the rectangle as the absolute passed value.
	 * @usedby 	Rectangle::__construct()
	 *
	 * @param 	float 	$height 	The rectangle height.
	 *
	 * @return 	Rectangle 	This object to support chaining.
	 */
	public function setHeight($height)
	{
		$this->height = abs($height);

		return $this;
	}

	/**
	 * Get the height of the rectangle.
	 *
	 * @return 	float 	The rectangle height.
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @override interface method
	 * Calculate the perimeter of the rectangle with the formula:
	 * 2P = W * 2 + H * 2.
	 *
	 * @return 	float 	The rectangle perimeter.
	 */
	public function perimeter()
	{
		return $this->width*2 + $this->height*2;
	}

	/**
	 * @override interface method
	 * Calculate the area of the rectangle with the formula:
	 * A = W * H
	 *
	 * @return 	float 	The rectangle area.
	 */
	public function area()
	{
		return $this->width*$this->height;
	}

	/**
	 * @override interface method
	 * Calculate the centroid of the rectangle with the formula:
	 * C = W/2 ; H/2
	 *
	 * @return 	Point 	The rectangle centroid.
	 */
	public function centroid()
	{
		return new Point($this->width/2, $this->height/2);
	}

	/**
	 * Calculate the diagonal of the rectangle with the formula:
	 * D = √( W^2 + H^2 ) = Pythagorean theorem
	 *
	 * @return 	float 	The rectangle diagonal.
	 */
	public function diagonal()
	{
		return sqrt(pow($this->width, 2) + pow($this->height, 2));
	}

}
