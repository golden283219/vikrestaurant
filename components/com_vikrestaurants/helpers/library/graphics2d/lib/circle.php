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
 * Helper class to handle Circle shapes.
 * This class implements the Shape interface methods.
 *
 * @see 	Point 	Used to handle the center.
 *
 * @since  	1.7
 */
class Circle implements Shape
{
	/**
	 * The center of the cirle.
	 *
	 * @var Point
	 */
	private $center = null;

	/**
	 * The radius of the circle.
	 *
	 * @var float
	 */
	private $radius = 0;

	/**
	 * Class constructor.
	 *
	 * @param 	float 	$radius 	The radius of the circle.
	 * @param 	float 	$x 			The x center of the circle.
	 * @param 	float 	$y 			The y center of the circle.
	 *
	 * @uses 	setRadius() 	Circle radius setter.
	 * @uses 	setCenter() 	Circle center setter.
	 */
	public function __construct($radius, $x = 0, $y = 0)
	{
		$this->setRadius($radius)
			->setCenter(new Point($x, $y));
	}

	/**
	 * Set the X position of the cicle center.
	 * @usedby 	Circle::setCenter()
	 *
	 * @param 	float 	$x 	The x center of the circle.
	 *
	 * @return 	Circle 	This object to support chaining.
	 */
	public function setX($x)
	{
		$this->center->x = $x;

		return $this;
	}

	/**
	 * Get the X position of the circle center.
	 *
	 * @return 	float 	The x center of the circle.
	 */
	public function getX()
	{
		return $this->center->x;
	}

	/**
	 * Set the Y position of the cicle center.
	 * @usedby 	Circle::setCenter()
	 *
	 * @param 	float 	$y 	The y center of the circle.
	 *
	 * @return 	Circle 	This object to support chaining.
	 */
	public function setY($y)
	{
		$this->center->y = $y;

		return $this;
	}

	/**
	 * Get the Y position of the circle center.
	 *
	 * @return 	float 	The y center of the circle.
	 */
	public function getY()
	{
		return $this->center->y;
	}

	/**
	 * Set the center of the circle.
	 * @usedby 	Circle::__construct()
	 *
	 * @param 	Point 	$center 	The center of the circle.
	 *
	 * @return 	Circle 	This object to support chaining.
	 *
	 * @uses 	setX() 		Center X position setter.
	 * @uses 	setY() 		Center Y position setter.
	 */
	public function setCenter(Point $center = null)
	{
		if ($this->center === null) {
			$this->center = new Point();
		}

		$this->setX($center->x);
		$this->setY($center->y);

		return $this;
	}

	/**
	 * Get the center of the circle.
	 *
	 * @return 	Point 	The circle center.
	 */
	public function getCenter()
	{
		return $this->center;
	}

	/**
	 * Set the radius of the circle as the absolute passed value.
	 * @usedby 	Circle::__construct()
	 *
	 * @param 	float 	$radius 	The circle radius.
	 *
	 * @return 	Circle 	This object to support chaining.
	 */
	public function setRadius($radius)
	{
		$this->radius = abs($radius);

		return $this;
	}

	/**
	 * Get the radius of the circle.
	 *
	 * @return 	float 	The circle radius.
	 */
	public function getRadius()
	{
		return $this->radius;
	}

	/**
	 * @override interface method
	 * Calculate the perimeter of the circle with the formula:
	 * 2P = PI * R * 2.
	 *
	 * @return 	float 	The circle perimeter.
	 */
	public function perimeter()
	{
		return M_PI * $this->radius * 2;
	}

	/**
	 * @override interface method
	 * Calculate the area of the circle with the formula:
	 * A = PI * R^2.
	 *
	 * @return 	float 	The circle area.
	 */
	public function area()
	{
		return M_PI * $this->radius * $this->radius; 
	}

	/**
	 * @override interface method
	 * Calculate the centroid of the circle, which is equal to its center.
	 *
	 * @return 	Point 	The circle centroid.
	 */
	public function centroid()
	{
		return $this->getCenter();
	}

}
