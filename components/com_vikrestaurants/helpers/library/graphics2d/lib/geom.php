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
 * Helper class to handle geometric problems.
 *
 * @see 	Polygon
 * @see 	Point
 * @see 	Line
 * @see 	Circle
 *
 * @since  	1.7
 */
abstract class Geom
{
	/**
	 * Generate a shape following the specified arguments.
	 *
	 * @param 	integer 	$num_vertex 	The number of corners to generate.
	 * @param 	float 		$min_x 			The minimum x coordinate.
	 * @param 	float 		$max_x 			The maximum x coordinate.
	 * @param 	float 		$min_y 			The minimum y coordinate.
	 * @param 	float 		$max_y 			The maximum y coordinate.
	 *
	 * @return 	Polygon 	The generated polygon.
	 */
	public static function generateShape($num_vertex, $min_x = 0, $max_x = 100, $min_y = 0, $max_y = 100)
	{
		$polygon = new Polygon();

		if (($max_x-$min_x) * ($max_y-$min_y) < abs($num_vertex)) {
			return $polygon;
		}

		for ($num_vertex = abs($num_vertex); $num_vertex > 0; $num_vertex--) {
			do {
				$p = new Point(rand($min_x, $max_x), rand($min_y, $max_y));
			} while ($polygon->indexOf($p) !== -1);


			$polygon->addPoint($p);
		}

		return $polygon;
	}
	
	/**
	 * Check if a point lies on a line.
	 *
	 * @param 	Line 	$l 	The line on which the point should lie.
	 * @param 	Point 	$p 	The point to check.
	 *
	 * @return 	boolean 	True if the point lies on the line, otherwise false.
	 */
	public static function isPointOnLine(Line $l, Point $p)
	{
		$dxc = $p->x - $l->getFirstPoint()->x;
		$dyc = $p->y - $l->getFirstPoint()->y;

		$dxl = $l->getSecondPoint()->x - $l->getFirstPoint()->x;
		$dyl = $l->getSecondPoint()->y - $l->getFirstPoint()->y;

		if ($dyc == 0 && $dyl == 0) {
			return ($l->getStartX() <= $p->x && $p->x <= $l->getEndX());
		}

		if ($dxc == 0 && $dxl == 0) {
			return ($l->getStartY() <= $p->y && $p->y <= $l->getEndY());
		}

		return !( $dxc * $dyl - $dyc * $dxl);
	}

	/**
	 * Check if a point is wrapped in a Polygon.
	 * The function uses always the even-odd method to understand if a point is inside a polygon.
	 *
	 * @param 	Polygon 	$s 		The polygon that should wrap the point.
	 * @param 	Point 		$p 		The point to check.
	 * @param 	integer 	$mode 	The algorithm to use.
	 * 								Specify 1 for CROSSING NUMBER, 
	 * 								otherwise 2 for WINDING NUMBER.
	 *
	 * @return 	boolean 	True if the point is wrapped, otherwise false.
	 *
	 * @uses 	cn_Poly() 	Crossin number algorithm to verify Polygon wrapping.
	 * @uses 	wn_Poly() 	Winding number algorithm to verify Polygon wrapping.
	 */
	public static function isPointInsidePolygon(Polygon $s, Point $p, $mode = 1)
	{
		if ($mode == self::CROSSING_NUMBER) {
			return self::cn_Poly($s, $p);
		}

		return self::wn_Poly($s, $p);
	}

	/**
	 * Crossing Number method to check if a point is wrapped in a polygon.
	 * This methods may fail when the point is really close to at least one corner.
	 * Suggested use for low precision.
	 *
	 * @param 	Polygon 	$s 		The polygon that should wrap the point.
	 * @param 	Point 		$p 		The point to check.
	 *
	 * @return 	boolean 	True if the point is wrapped, otherwise false.
	 */
	protected static function cn_Poly(Polygon $s, Point $p)
	{
		if ($s->indexOf($p) !== -1) { // the point is a vertex of the polygon
			return true;
		}

		$cn = 0; // the  crossing number counter

		// loop through all edges of the polygon
		for ($i = 0; $i < $s->getNumPoints(); $i++) {    // edge from V[i]  to V[i+1]
			$j = ($i == $s->getNumPoints()-1 ? 0 : $i+1);

			$v1 = $s->getPoint($i);
			$v2 = $s->getPoint($j);

			if( 
				(($v1->y <= $p->y) && ($v2->y > $p->y))     // an upward crossing
				|| (($v1->y > $p->y) && ($v2->y <=  $p->y))
			) { // a downward crossing
				// compute the actual edge-ray intersect x-coordinate
				$vt = ($p->y - $v1->y) / ($v2->y - $v1->y);
				if ($p->x < $v1->x + $vt * ($v2->x - $v1->x)) {	
					// P.x < intersect
					$cn++; // a valid crossing of y=P.y right of P.x
				}
			}
		}

		return ($cn & 1); // 0 if even (out), and 1 if odd (in)
	}

	/**
	 * Winding Number method to check if a point is wrapped in a polygon.
	 * Suggested use for high precision.
	 *
	 * @param 	Polygon 	$s 		The polygon that should wrap the point.
	 * @param 	Point 		$p 		The point to check.
	 *
	 * @return 	boolean 	True if the point is wrapped, otherwise false.
	 *
	 * @uses 	isLef() 	Check if a point is on the left side of a line.
	 */
	protected static function wn_Poly(Polygon $s, Point $p)
	{
		if ($s->indexOf($p) !== -1) { // the point is a vertex of the polygon
			return true;
		}

		$wn = 0; // the  winding number counter

		// loop through all edges of the polygon
		for ($i = 0; $i < $s->getNumPoints(); $i++) {    // edge from V[i] to V[i+1]
			$j = ($i == $s->getNumPoints()-1 ? 0 : $i+1);

			$v1 = $s->getPoint($i);
			$v2 = $s->getPoint($j);

			if ($v1->y <= $p->y) {		// start y <= P.y
				if ($v2->y > $p->y) {	// an upward crossing
					 if (self::isLeft( $v1, $v2, $p ) > 0) {  // P left of edge
						 $wn++;	// have  a valid up intersect
					}
				}
			} else {  // start y > P.y (no test needed)
				if ($v2->y <= $p->y) { // a downward crossing
					 if (self::isLeft( $v1, $v2, $p ) < 0) { // P right of edge
						 $wn--; // have  a valid down intersect
					}
				}
			}
		}
		
		return $wn;
	}

	/**
	 * Check if a point is on the left side of a line.
	 *
	 * @param 	Point 	$p0 	The first point of the line.
	 * @param 	Point 	$p1 	The second point of the line.
	 * @param 	Point 	$p2 	The point to check.
	 *
	 * @return 	boolean 	True if the point is on the left side of the line.
	 */
	protected static function isLeft(Point $p0, Point $p1, Point $p2)
	{
		return (($p1->x - $p0->x) * ($p2->y - $p0->y) - ($p2->x - $p0->x) * ($p1->y - $p0->y));
	}

	/**
	 * Check if a point is inside a circle.
	 * A point is wrapped in a circle when the distance between the circle center 
	 * and the point is equals or higher than the radius of the circle.
	 *
	 * @param 	Circle 	$c 	The circle that should wrap the point.
	 * @param 	Point 	$p 	The point that should be wrapped.
	 *
	 * @return 	boolean 	True if the point is wrapped, otherwise false.
	 */
	public static function isPointInsideCircle(Circle $c, Point $p)
	{
		//( pow($p->x - $c->getCenter()->x, 2) + pow($p->y - $c->getCenter()->y, 2) ) <= pow($c->getRadius(), 2) 

		return ($c->getCenter()->getDistance($p) <= $c->getRadius());
	}

	/**
	 * Check if a point is inside a circle that lies on the Earth globe.
	 * A point is wrapped in a circle when the distance between the circle center 
	 * and the point is equals or higher than the radius of the circle.
	 *
	 * The distance between the circle center and the point need to be calculated differently
	 * as the globe is not a 2D geometric plane.
	 *
	 * @param 	Circle 	$c 	The circle that should wrap the point.
	 * @param 	Point 	$p 	The point that should be wrapped.
	 *
	 * @return 	boolean 	True if the point is wrapped, otherwise false.
	 */
	public static function isPointInsideCircleOnEarth(Circle $c, Point $p)
	{
		$lat_1 = $c->getCenter()->y * pi() / 180.0;
		$lng_1 = $c->getCenter()->x * pi() / 180.0;

		$lat_2 = $p->y * pi() / 180.0;
		$lng_2 = $p->x * pi() / 180.0;

		/** distance between 2 coordinates
		 * R = 6371 (Eart radius ~6371 km)
		 *
		 * coordinates in radians
		 * lat1, lng1, lat2, lng2
		 *
		 * Calculate the included angle fi
		 * fi = abs( lng1 - lng2 );
		 *
		 * Calculate the third side of the spherical triangle
		 * p = acos( 
		 *      sin(lat2) * sin(lat1) + 
		 *      cos(lat2) * cos(lat1) * 
		 *      cos( fi ) 
		 * )
		 * 
		 * Multiply the third side per the Earth radius (distance in km)
		 * D = p * R;
		 *
		 * MINIFIED EXPRESSION
		 *
		 * acos( 
		 *      sin(lat2) * sin(lat1) + 
		 *      cos(lat2) * cos(lat1) *
		 *      cos( abs(lng1-lng2) ) 
		 * ) * R
		 *
		 */

		return acos(
			sin($lat_2) * sin($lat_1) + 
			cos($lat_2) * cos($lat_1) *
			cos(abs($lng_1-$lng_2))
		) * 6371 < $c->getRadius();
	}

	/**
	 * Crossing Number method identifier.
	 *
	 * @var integer
	 */
	const CROSSING_NUMBER = 1;

	/**
	 * Winding Number method identifier.
	 *
	 * @var integer
	 */
	const WINDING_NUMBER = 2;

}
