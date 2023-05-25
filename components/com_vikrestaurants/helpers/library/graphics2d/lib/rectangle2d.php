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
 * Helper class to handle 2D Rectangle shapes.
 *
 * @see 	Rectangle 	This class extends the Rectangle object to support a position on the geometric plane.
 * @see 	Point 		Used to handle the center.
 *
 * The "o" represents the coordinates of the rectangle.
 * o-------------
 * -			-
 * -			-
 * --------------
 *
 * @since  	1.7
 */
class Rectangle2D extends Rectangle
{

	/**
	 * The position of the rectangle, which starts from the top-left corner.
	 *
	 * @var Point
	 */
	private $point;

	/**
	 * Class constructor.
	 *
	 * @param 	float 	$x 	The rectangle X position.
	 * @param 	float 	$y 	The rectangle Y position.
	 * @param 	float 	$w 	The rectangle width.
	 * @param 	float 	$h 	The rectangle height.
	 */
	public function __construct($x = 0, $y = 0, $w = 0, $h = 0)
	{
		parent::__construct($w, $h);

		$this->point = new Point($x, $y);
	}

	/**
	 * Get the coordinates of the top-left corner.
	 *
	 * @return 	Point 	The coordinates of the rectangle.
	 */
	public function getPoint()
	{
		return $this->point;
	}

	/**
	 * Get the X position of the top-left corner.
	 *
	 * @return 	float 	The x position of the rectangle.
	 */
	public function getX()
	{
		return $this->point->x;
	}

	/**
	 * Get the Y position of the top-left corner.
	 *
	 * @return 	float 	The Y position of the rectangle.
	 */
	public function getY()
	{
		return $this->point->y;
	}

	/**
	 * @override Rectangle method to support shifted position.
	 * Calculate the centroid of the rectangle with the formula:
	 * C = X + W/2 ; Y + H/2
	 *
	 * @return 	Point 	The rectangle centroid.
	 */
	public function centroid()
	{
		return new Point($this->point->x+$this->getWidth()/2, $this->point->y+$this->getHeight()/2);
	}

}
