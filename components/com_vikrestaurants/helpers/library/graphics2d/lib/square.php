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
 * Helper class to handle Square shapes.
 *
 * @see 	Rectangle 	This class extends the Rectangle object to have same width and height.
 * @see 	Point 		Used to handle the center.
 *
 * @since  	1.7
 */
class Square extends Rectangle
{
	/**
	 * Class constructor.
	 *
	 * @param 	float 	$side 		The side of the square.
	 *
	 * @uses 	Rectangle::__construct() 	Parent constructor to set same width and height.
	 */
	public function __construct($side = 0)
	{
		parent::__construct($side, $side);
	}

	/**
	 * @override parent method
	 * Set the width of the square. This method will affect the height too.
	 *
	 * @param 	float 	$width 		The width of the square.
	 *
	 * @return 	Square 	This object to support chaining.
	 *
	 * @uses 	setSide() 	Square side setter to affect both width and height.
	 */
	public function setWidth($width)
	{
		$this->setSide($width);

		return $this;
	}

	/**
	 * @override parent method
	 * Set the height of the square. This method will affect the width too.
	 *
	 * @param 	float 	$height 	The height of the square.
	 *
	 * @return 	Square 	This object to support chaining.
	 *
	 * @uses 	setSide() 	Square side setter to affect both width and height.
	 */
	public function setHeight($height)
	{
		$this->setSide($height);

		return $this;
	}

	/**
	 * Set the side of the square. This method will affect both width the height.
	 *
	 * @param 	float 	$side 		The side of the square.
	 *
	 * @return 	Square 	This object to support chaining.
	 *
	 * @uses 	Rectangle::setWidth() 	Set the width of the rectangle.
	 * @uses 	Rectangle::setHeight() 	Set the height of the rectangle.
	 */
	public function setSide($side)
	{
		parent::setWidth($side);
		parent::setHeight($side);

		return $this;
	}

	/**
	 * Get the side of the square.
	 *
	 * @return 	float 	The square side.
	 *
	 * @uses 	Rectangle::getWidth() 	Rectangle width getter.
	 */
	public function getSide()
	{
		// getWidth() or getHeight() will return always the same value.
		return $this->getWidth();
	}

}
