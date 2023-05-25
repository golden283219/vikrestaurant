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
 * Helper class to handle geometric points.
 *
 * @see 	Point 	Used to handle the start point and the end point of the line.
 *
 * @since  	1.7
 */
class Point
{

	/**
	 * The X position of the point.
	 *
	 * @var float
	 */
	public $x;

	/**
	 * The Y position of the point.
	 *
	 * @var float
	 */
	public $y;

	/**
	 * Class constructor.
	 *
	 * @param 	float 	$x 	The X point position.
	 * @param 	float 	$y 	The Y point position.
	 *
	 * @uses 	setLocation() 	Point X and Y setter.
	 */
	public function __construct($x = 0, $y = 0)
	{
		$this->setLocation($x, $y);
	}

	/**
	 * Set the X and Y position of the point.
	 * @usedby	Point::__construct()
	 *
	 * @param 	float 	$x 	The X point position.
	 * @param 	float 	$y 	The Y point position.
	 *
	 * @return 	Point 	This object to support chaining.
	 *
	 * @uses 	setX() 	Point X position setter.
	 * @uses 	setY() 	Point Y position setter.
	 */
	public function setLocation($x, $y)
	{
		$this->setX($x)->setY($y);

		return $this;
	}

	/**
	 * Set the X position of the point.
	 * @usedby 	Point::setLocation()
	 *
	 * @param 	float 	$x 	The X point position.
	 *
	 * @return 	Point 	This object to support chaining.
	 */
	public function setX($x)
	{
		$this->x = floatval($x);

		return $this;
	}

	/**
	 * Set the Y position of the point.
	 * @usedby 	Point::setLocation()
	 *
	 * @param 	float 	$y 	The Y point position.
	 *
	 * @return 	Point 	This object to support chaining.
	 */
	public function setY($y)
	{
		$this->y = floatval($y);

		return $this;
	}

	/**
	 * Get the X position of the point.
	 *
	 * @return 	float 	The point X position.
	 */
	public function getX()
	{
		return $this->x;
	}

	/**
	 * Get the Y position of the point.
	 *
	 * @return 	float 	The point Y position.
	 */
	public function getY()
	{
		return $this->y;
	}

	/**
	 * Check if this point is equals to the given point.
	 * 
	 * @param 	mixed 	$p 	The point to compare.
	 *
	 * @return 	boolean 	True if they are equal, otherwise false.
	 */
	public function equalsTo($p)
	{
		return ($p instanceof Point && $this->x == $p->x && $this->y == $p->y);
	}

	/**
	 * Get the distance between this point and the given point.
	 * 
	 * @param 	Point 	$p 	The point to compare.
	 *
	 * @return 	float 	The distance between the 2 points.
	 *
	 * @uses 	getDistanceBetweenPoints() 	Static method to get the distance between 2 given points.
	 */
	public function getDistance(Point $p)
	{
		return self::getDistanceBetweenPoints($this, $p);
	}

	/**
	 * Get the distance between 2 given points.
	 * This function assumes that the points are located on a geometric place (2D).
	 *
	 * The distance is calculated with the formula:
	 * D = √( (x1 - x2)^2 + (y1 - y2)^2 )
	 * @usedby 	Point::getDistance()
	 * 
	 * @param 	Point 	$p1 	The first point.
	 * @param 	Point 	$p2 	The second point.
	 *
	 * @return 	float 	The distance between the 2 points.
	 */
	public static function getDistanceBetweenPoints(Point $p1, Point $p2)
	{
		return sqrt( pow($p1->x - $p2->x, 2) + pow($p1->y - $p2->y, 2) );
	}

	/**
	 * Get the medium point between this point and the given point.
	 * 
	 * @param 	Point 	$p 	The point to compare.
	 *
	 * @return 	Point 	The medium point between the 2 points.
	 *
	 * @uses 	getMediumBetweenPoints() 	Static method to get the medium point between 2 given points.
	 */
	public function getMediumPoint(Point $p)
	{
		return self::getMediumBetweenPoints($this, $p);
	}

	/**
	 * Get the medium point between 2 given points.
	 * This function assumes that the points are located on a geometric place (2D).
	 *
	 * The medium point is calculated with the formula:
	 *     x1 + x2   y1 + y2
	 * M = ------- ; -------
	 *        2         2
	 * @usedby 	Point::getMediumPoint()
	 * 
	 * @param 	Point 	$p1 	The first point.
	 * @param 	Point 	$p2 	The second point.
	 *
	 * @return 	Point 	The medium point between the 2 points.
	 */
	public static function getMediumBetweenPoints(Point $p1, Point $p2)
	{
		return new Point( ($p1->x+$p2->x) / 2, ($p1->y+$p2->y) / 2 );
	}

}
