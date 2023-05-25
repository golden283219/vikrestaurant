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
 * Wrapper class to handle the properties of a special day
 * for the take-away group.
 *
 * @since 1.8
 */
class VRESpecialDayTakeaway extends VRESpecialDay
{
	/**
	 * Flag used to check whether the delivery service is enabled.
	 *
	 * @var boolean
	 */
	protected $delivery;

	/**
	 * Flag used to check whether the pick-up service is enabled.
	 *
	 * @var boolean
	 */
	protected $pickup;

	/**
	 * A list containing all the accepted delivery areas.
	 *
	 * @var array
	 *
	 * @since 1.8.2
	 */
	protected $deliveryAreas = array();

	/**
	 * The minimum cost needed to proceed with the purchase.
	 * False in case the amount should not overwrite the global one.
	 *
	 * @var float|bool
	 *
	 * @since 1.8.3
	 */
	protected $minCostOrder;

	/**
	 * Class constructor.
	 *
	 * @param 	mixed  $args  Either an array or an object containing
	 * 						  the properties of the special day record.
	 */
	public function __construct($args)
	{
		// let parent initialize the object first
		parent::__construct($args);

		$args = (object) $args;

		
		if ($args->delivery_service == 0)
		{
			// both services are enabled
			$this->delivery = true;
			$this->pickup   = true;
		}
		else if ($args->delivery_service == 1)
		{
			// only delivery is enabled
			$this->delivery = true;
			$this->pickup   = false;
		}
		else if ($args->delivery_service == 2)
		{
			// only pickup is enabled
			$this->delivery = false;
			$this->pickup   = true;
		}
		else
		{
			// get delivery service flag from configuration
			$service = VREFactory::getConfig()->getUint('deliveryservice');

			// rely on default configuration
			$this->delivery = $service == 1 || $service == 2;
			$this->pickup   = $service == 0 || $service == 2;
		}

		if ($args->delivery_areas)
		{
			// JSON decode the list of accepted delivery areas
			$this->deliveryAreas = (array) json_decode($args->delivery_areas);
		}

		// make sure we have a valid amount
		if ((float) $args->minorder > 0)
		{
			// assign the specified value
			$this->minCostOrder = (float) $args->minorder;
		}
		else
		{
			// use the global amount
			$this->minCostOrder = false;
		}
	}

	/**
	 * Checks whether this special and the specified one
	 * shares the same delivery service configuration.
	 *
	 * @param 	VRESpecialDay  $sd  The special day to compare.
	 *
	 * @return 	boolean
	 */
	public function hasSameService($sd)
	{
		// first of all validate instance
		if (!$sd instanceof VRESpecialDay)
		{
			return false;
		}

		// compare delivery service configuration
		return $this->delivery == $sd->delivery && $this->pickup == $sd->pickup;
	}

	/**
	 * @override
	 * Returns a list of menus assigned to the special day.
	 *
	 * @param 	boolean  $objects  True to return the database objects.
	 *
	 * @return 	array
	 *
	 * @since 	1.8.2
	 */
	public function getMenus($objects = false)
	{
		// obtain menus from parent
		$menus = parent::getMenus();

		if ($objects)
		{
			if (!$menus)
			{
				// no assigned menus
				return array();
			}

			// retrieve menu details from database
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_menus'))
				->where($dbo->qn('published') . ' = 1')
				->where($dbo->qn('id') . ' IN (' . implode(',', $menus) . ')')
				->order($dbo->qn('ordering') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$menus = $dbo->loadObjectList();
			}
			else
			{
				$menus = array();
			}
		}

		return $menus;
	}

	/**
	 * Returns a list of supported delivery areas.
	 *
	 * @param 	boolean  $objects  True to return the database objects.
	 *
	 * @return 	array
	 *
	 * @since 	1.8.2
	 */
	public function getDeliveryAreas($objects = false)
	{
		$areas = $this->deliveryAreas;

		if ($objects)
		{
			// retrieve menu details from database
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
				->where($dbo->qn('published') . ' = 1')
				->order($dbo->qn('ordering') . ' ASC');

			if ($areas)
			{
				$q->where($dbo->qn('id') . ' IN (' . implode(',', $areas) . ')');
			}

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$areas = $dbo->loadObjectList();
			}
			else
			{
				$areas = array();
			}
		}

		return $areas;
	}
}
