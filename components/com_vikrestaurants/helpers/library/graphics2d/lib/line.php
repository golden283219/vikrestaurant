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
 * Helper class to handle geometric lines.
 *
 * @see 	Point 	Used to handle the start point and the end point of the line.
 *
 * @since  	1.7
 */
class Line
{
	/**
	 * First point of the line.
	 *
	 * @var Point
	 */
	private $p1;

	/**
	 * Second point of the line.
	 *
	 * @var Point
	 */
	private $p2;

	/**
	 * Class constructor.
	 *
	 * @param 	float 	$x1 	The first X of the line.
	 * @param 	float 	$y1 	The first Y of the line.
	 * @param 	float 	$x2 	The second X of the line.
	 * @param 	float 	$y2 	The second Y of the line.
	 *
	 * @uses 	setPoints() 	Set the first and second points of the line.	
	 */
	public function __construct($x1, $y1, $x2, $y2)
	{
		$this->setPoints(
			new Point($x1, $y1), 
			new Point($x2, $y2)
		);
	}

	/**
	 * Set the first point and the second point of the line.
	 * @usedby 	Line::__construct()
	 *
	 * @param 	Point 	$p1 	The first point of the line.
	 * @param 	Point 	$p2 	The second point of the line.
	 *
	 * @return  Line 	This object to support chaining.
	 */
	public function setPoints(Point $p1 = null, Point $p2 = null)
	{
		if ($p1 === null) {
			$p1 = new Point();
		}

		if ($p2 === null) {
			$p2 = new Point();
		}

		$this->p1 = $p1;
		$this->p2 = $p2;

		return $this;
	}

	/**
	 * Get a list containing the first point and second point of the line.
	 *
	 * @return 	array 	A list of points.
	 */
	public function getPoints()
	{
		return array($this->p1, $this->p2);
	}

	/**
	 * Get the first point of the line.
	 * @usedby 	Line::linesIntersection();
	 *
	 * @return 	Point 	The first point.
	 */
	public function getFirstPoint()
	{
		return $this->p1;
	}

	/**
	 * Get the second point of the line.
	 * @usedby 	Line::linesIntersection();
	 *
	 * @return 	Point 	The second point.
	 */
	public function getSecondPoint()
	{
		return $this->p2;
	}

	/**
	 * Get the start X position of the line.
	 * The start X is the lowest value between the 2 x points.
	 *
	 * @return 	float 	The start X position.
	 */
	public function getStartX()
	{
		if ($this->p1->x <= $this->p2->x) {
			return $this->p1->x;
		}

		return $this->p2->x;
	}

	/**
	 * Get the end X position of the line.
	 * The end X is the highest value between the 2 x points.
	 *
	 * @return 	float 	The end X position.
	 */
	public function getEndX()
	{
		if ($this->p1->x >= $this->p2->x) {
			return $this->p1->x;
		}

		return $this->p2->x;
	}

	/**
	 * Get the start Y position of the line.
	 * The start Y is the lowest value between the 2 y points.
	 *
	 * @return 	float 	The start Y position.
	 */
	public function getStartY()
	{
		if ($this->p1->y <= $this->p2->y) {
			return $this->p1->y;
		}

		return $this->p2->y;
	}

	/**
	 * Get the end Y position of the line.
	 * The end Y is the highest value between the 2 y points.
	 *
	 * @return 	float 	The end Y position.
	 */
	public function getEndY()
	{
		if ($this->p1->y >= $this->p2->y) {
			return $this->p1->y;
		}

		return $this->p2->y;
	}

	/**
	 * Check if this line intersect with the specified line.
	 *
	 * @param 	Line 	$line 	The line object to check for.
	 *
	 * @return 	boolean 	True if they intersect, otherwise false.
	 *
	 * @uses 	linesIntersection() 	static method to check the intersection.
	 */
	public function intersect(Line $line)
	{
		return self::linesIntersection($this, $line);
	}

	/**
	 * Check if the 2 given lines intersect each other.
	 * @usedby 	Line::intersect()
	 *
	 * @param 	Line 	$l1 	The first line object.
	 * @param 	Line 	$l2 	The second line object.
	 *
	 * @return 	boolean 	True if they intersect, otherwise false.
	 *
	 * @uses 	getFirstPoint() 	First point getter.
	 * @uses 	getSecondPoint() 	Second point getter.
	 */
	public static function linesIntersection(Line $l1, Line $l2)
	{
		$p0_x = $l1->getFirstPoint()->x;
		$p0_y = $l1->getFirstPoint()->y;

		$p1_x = $l1->getSecondPoint()->x;
		$p1_y = $l1->getSecondPoint()->y;

		$p2_x = $l2->getFirstPoint()->x;
		$p2_y = $l2->getFirstPoint()->y;

		$p3_x = $l2->getSecondPoint()->x;
		$p3_y = $l2->getSecondPoint()->y; 
		
		$s1_x = $p1_x - $p0_x;
		$s1_y = $p1_y - $p0_y;

		$s2_x = $p3_x - $p2_x;
		$s2_y = $p3_y - $p2_y;

		if (-$s2_x * $s1_y + $s1_x * $s2_y == 0) {
			// collision detected -> one point of segments in common
			return true;
		}

		$s = (-$s1_y * ($p0_x - $p2_x) + $s1_x * ($p0_y - $p2_y)) / (-$s2_x * $s1_y + $s1_x * $s2_y);
		$t = ( $s2_x * ($p0_y - $p2_y) - $s2_y * ($p0_x - $p2_x)) / (-$s2_x * $s1_y + $s1_x * $s2_y);

		if ($s >= 0 && $s <= 1 && $t >= 0 && $t <= 1) {
			// collision detected
			return true;
		}

		return false; // no collision
	}
	
}
