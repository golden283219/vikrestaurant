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
 * Helper class to handle Polygon shapes.
 * This class implements the Shape interface methods.
 *
 * @see 	Point 			Used to handle the polygon corners.
 * @see 	Rectangle2D 	Used to handle polygon bounds.
 *
 * @since  	1.7
 */
class Polygon implements Shape
{
	/**
	 * The list containing all the polygon corners.
	 * It is not needed to push the first corner at the end to close the chain,
	 * as the system always close it with a segment from the last corner to the first corner.
	 *
	 * Do not repeat the first corner as last, otherwise there will be a segment with no length,
	 * even if this do not corrupt the functionality.
	 *
	 * @var array
	 */
	private $coordinates = array();

	/**
	 * Class constructor
	 *
	 * @param 	array 	$coordinates 	The list containing all the corners.
	 *
	 * @uses 	setPoints() 	Set the list of corners.
	 */
	public function __construct($coordinates = array())
	{
		$this->setPoints($coordinates);
	}

	/**
	 * Set the list of corners.
	 * @usedby 	Polygon::__construct()
	 *
	 * @param 	array 		$points 	The list of corners. A corner must be an instance of Point.
	 * @param 	boolean 	$clear 		True to reset the list, otherwise false ot append the new corners.
	 *
	 * @return 	Polygon 	This object to support chaining.
	 *
	 * @uses 	addPoint() 	Add a corner one by one.
	 */
	public function setPoints(array $points, $clear = true)
	{
		if ($clear || !is_array($this->coordinates)) {
			$this->coordinates = array();
		}

		foreach ($points as $p) {
			if ($p instanceof Point) {
				$this->addPoint($p);
			}
		}

		return $this;
	}

	/**
	 * Add a corner to the current list.
	 * @usedby 	Polygon::setPoints()
	 *
	 * @param 	Point 		$point 	The corner to push.
	 *
	 * @return 	Polygon 	This object to support chaining.
	 */
	public function addPoint(Point $point)
	{
		array_push($this->coordinates, $point);

		return $this;
	}

	/**
	 * Get the corner at the specified position.
	 *
	 * @param 	integer 	$i 	The index of the corner.
	 *
	 * @return 	Point 	The corner found, otherwise NULL.
	 */
	public function getPoint($i)
	{
		if ($i >= 0 && $i < count($this->coordinates)) {
			return $this->coordinates[$i];
		}
		return null;
	}

	/**
	 * Get the list containing all the corners.
	 *
	 * @return 	array 	The list of corners.
	 */
	public function getPoints()
	{
		return $this->coordinates;
	}

	/**
	 * Get the index of the specified corner.
	 *
	 * @param 	Point 	$p 	The point to search for.
	 *
	 * @return 	integer 	The index found, otherwise -1.
	 */
	public function indexOf($p)
	{
		foreach ($this->coordinates as $i => $c) {
			if ($c->equalsTo($p)) {
				return $i;
			}
		}

		return -1;
	}

	/**
	 * Get the total count of corners in the list.
	 * @usedby 	Polygon::getBounds()
	 *
	 * @return 	integer 	The corners count.
	 */
	public function getNumPoints()
	{
		return count($this->coordinates);
	}

	/**
	 * @override interface method
	 * Calculate the perimeter of the polygon by summing all the distances
	 * found between the contiguous corners.
	 *
	 * @return 	float 	The polygon perimeter.
	 */
	public function perimeter()
	{
		$perimeter = 0.0;

		// exclude the last point because it have to be considered with the last point
		for ($i = 0; $i < count($this->coordinates)-1; $i++) {
			// get the distance between contiguous points
			$perimeter += $this->coordinates[$i]->getDistance($this->coordinates[$i+1]);
		}

		// close the chain from the last point to the first one
		$perimeter += $this->coordinates[0]->getDistance($this->coordinates[count($this->coordinates)-1]);

		return $perimeter;
	}

	/**
	 * @override interface method
	 * Calculate the area of the polygon with the algorithm below:
	 *
	 * V = [ (-3, -2), (-1, 4), (6, 1), (3, 10), (-4, 9) ] -> list of polygon corners
	 *
	 * --- STEP #1 ---
	 * List the x and y coordinates of each vertex of the polygon in counterclockwise order.
	 * Repeat the coordinates of the first point at the end of the list.
	 *
	 * Lx = [-3, -1, 6,  3, -4, -3] -> list with x coords (first x repeated at the end)
	 *
	 * Ly = [-2,  4, 1, 10,  9, -2] <- list with y coords (first y repeated at the end)
	 *
	 * --- STEP #2 ---
	 * Multiply the x coordinate of each vertex by the y coordinate of the next (index + 1) vertex.
	 *
	 * S1 = SUM i->1 to n-1 (Lx[i] * Ly[i+1]) -> -3*4 + -1*1 + 6*10 + 3 *9 + -4*-2 = 82
	 *
	 * --- STEP #3 ---
	 * Multiply the y coordinate of each vertex by the x coordinate of the next (index + 1) vertex.
	 *
	 * S2 = SUM i->1 to n-1 (Ly[i] * Lx[i+1]) -> -2*-1 + 4*6 + 1*3 + 10*-4 + 9*-3 = -38
	 *
	 * --- STEP #4 ---
	 * Subtract the sum of the second products from the sum of the first products and divide this difference by 2.
	 *
	 * A = (S1 - S2) / 2 -> (82 - (-38)) / 2 = (82 + 38) / 2 = 120 / 2 = 60 
	 *
	 * @return 	float 	The polygon area.
	 */
	public function area()
	{
		$area_1 = 0;
		$area_2 = 0;

		for ($i = 0; $i < count($this->coordinates)-1; $i++) {
			$area_1 += $this->coordinates[$i]->x*$this->coordinates[$i+1]->y;
			$area_2 += $this->coordinates[$i]->y*$this->coordinates[$i+1]->x;
		}
		$area_1 += $this->coordinates[count($this->coordinates)-1]->x*$this->coordinates[0]->y;
		$area_2 += $this->coordinates[count($this->coordinates)-1]->y*$this->coordinates[0]->x;

		// NOTE: 
		// if we apply the algorhitm in clockwise order, we will get the same value but with negative sign.
		// It is needed to get the absolute value to have always a positive amount.
		return abs($area_1-$area_2)/2;
	}

	/**
	 * @override interface method
	 * Calculate the centroid of the polygon by getting the average x and y coordinates.
	 *
	 * V = [ (-3, -2), (-1, 4), (6, 1), (3, 10), (-4, 9) ] -> list of polygon corners
	 *
	 * --- STEP #1 ---
	 * List the x and y coordinates of each vertex of the polygon in counterclockwise order.
	 *
	 * Lx = [-3, -1, 6,  3, -4] -> list with x coords
	 *
	 * Ly = [-2,  4, 1, 10,  9] <- list with y coords
	 *
	 * --- STEP #2 ---
	 * Sum each value within the 2 lists.
	 *
	 * Sx = SUM i->1 to n (Lx[i]) -> -3 + (-1) + 6 + 3 + (-4) = 1
	 *
	 * Sy = SUM i->1 to n (Ly[i]) -> -2 + 4 + 1 + 10 + 9 = 22
	 *
	 * --- STEP #3 ---
	 *
	 * Divide the 2 sums by the count of the corners.
	 *
	 * Xavg = Sx / n -> 1 / 5 = 0.2
	 *
	 * Yavg = Sy / n -> 22 / 5 = 4.4
	 *
	 * @return 	Point 	The rectangle centroid.
	 */
	public function centroid()
	{
		$p = new Point(0, 0);
		foreach ($this->coordinates as $coord) {
			$p->x += $coord->x;
			$p->y += $coord->y;
		}
		$p->x /= count($this->coordinates);
		$p->y /= count($this->coordinates);

		return $p;
	}

	/**
	 * Get the rectangle that bounds the polygon.
	 * The x position of the rectangle is the corner with lowest x.
	 * The y position of the rectangle is the corner with lowest y.
	 * The width of the rectangle is the corner with highest x minus lowest x.
	 * The height of the rectangle is the corner with highest y minus lowest y.
	 *
	 * @return 	Rectangle2D 	The bounds of the polygon.
	 *
	 * @uses 	getNumPoints() 	Count the corners in the list.
	 */
	public function getBounds()
	{
		if (!count($this->coordinates)) {
			return new Rectangle2D();
		}

		$min = array($this->coordinates[0]->x, $this->coordinates[0]->y);
		$max = array($this->coordinates[0]->x, $this->coordinates[0]->y);

		for ($i = 1; $i < $this->getNumPoints(); $i++) {
			$min[0] = min(array($min[0], $this->coordinates[$i]->x));
			$min[1] = min(array($min[1], $this->coordinates[$i]->y));

			$max[0] = max(array($max[0], $this->coordinates[$i]->x));
			$max[1] = max(array($max[1], $this->coordinates[$i]->y));
		}

		return new Rectangle2D($min[0], $min[1], $max[0]-$min[0], $max[1]-$min[1]);
	}

}
