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
 * Update adapter for com_vikrestaurants 1.8 version.
 *
 * This class can include update() and finalise().
 *
 * NOTE. do not call exit() or die() because the update won't be finalised correctly.
 * Return false instead to stop in anytime the flow without errors.
 *
 * @since 1.8
 */
abstract class VikRestaurantsUpdateAdapter1_8
{
	/**
	 * Method run during update process.
	 *
	 * @param 	object 	 $parent  The parent that calls this method.
	 *
	 * @return 	boolean  True on success, otherwise false to stop the flow.
	 */
	public static function update($parent)
	{
		self::adjustReservationClosure();

		return true;
	}

	/**
	 * Method run during postflight process.
	 *
	 * @param 	object 	 $parent  The parent that calls this method.
	 *
	 * @return 	boolean  True on success, otherwise false to stop the flow.
	 */
	public static function finalise($parent)
	{
		return true;
	}

	/**
	 * Method run before executing VikRestaurants for the first time
	 * after the update completion.
	 *
	 * @param 	object 	 $parent  The parent that calls this method.
	 *
	 * @return 	boolean  True on success, otherwise false to stop the flow.
	 */
	public static function afterupdate($parent)
	{
		// update BC version to the current one before executing the process,
		// so that in case of errors it won't be executed anymore
		VREFactory::getConfig()->set('bcv', '1.8');

		self::setupRecordsAlias();

		return true;
	}

	/**
	 * Marks the closure column for all the reservations that
	 * own a purchaser nominative equals to CLOSURE.
	 *
	 * @return 	booleam
	 */
	protected static function adjustReservationClosure()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->update($dbo->qn('#__vikrestaurants_reservation'))
			->set($dbo->qn('closure') . ' = 1')
			->where('BINARY ' . $dbo->qn('purchaser_nominative') . ' = ' . $dbo->q('CLOSURE'));

		$dbo->setQuery($q);
		$dbo->execute();

		return true;
	}

	/**
	 * Creates a default alias for all the tables that
	 * require it for routing purposes.
	 *
	 * @return 	boolean
	 */
	protected static function setupRecordsAlias()
	{
		$dbo = JFactory::getDbo();

		// create alias for menus
		$table = JTableVRE::getInstance('menu', 'VRETable');

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name')))
			->from($dbo->qn('#__vikrestaurants_menus'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadAssocList() as $menu)
			{
				$menu['alias'] = '';

				$table->save($menu);
			}
		}

		// create alias for take-away menus
		$table = JTableVRE::getInstance('tkmenu', 'VRETable');

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'title')))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadAssocList() as $menu)
			{
				$menu['alias'] = '';

				$table->save($menu);
			}
		}

		// create alias for products
		$table = JTableVRE::getInstance('tkentry', 'VRETable');

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name', 'id_takeaway_menu')))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadAssocList() as $prod)
			{
				$prod['alias'] = '';

				$table->save($prod);
			}
		}

		// create alias for options
		$table = JTableVRE::getInstance('tkentryoption', 'VRETable');

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'name', 'id_takeaway_menu_entry')))
			->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option'))
			->order($dbo->qn('ordering') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadAssocList() as $opt)
			{
				$opt['alias'] = '';

				$table->save($opt);
			}
		}

		return true;
	}
}
