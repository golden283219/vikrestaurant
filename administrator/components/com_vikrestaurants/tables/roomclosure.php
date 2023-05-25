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
 * VikRestaurants room closure table.
 *
 * @since 1.8
 */
class VRETableRoomclosure extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_room_closure', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_room';
		$this->_requiredFields[] = 'start_ts';
		$this->_requiredFields[] = 'end_ts';
	}

	/**
	 * Method to bind an associative array or object to the Table instance. This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the Table instance.
	 * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function bind($src, $ignore = array())
	{
		$src = (array) $src;

		// fetch start datetime timestamp
		list($start_date, $start_time) = explode(' ', $src['start_ts']);
		list($start_hour, $start_min)  = explode(':', $start_time);

		$src['start_ts'] = VikRestaurants::createTimestamp($start_date, $start_hour, $start_min);

		// fetch end datetime timestamp
		list($end_date, $end_time) = explode(' ', $src['end_ts']);
		list($end_hour, $end_min)  = explode(':', $end_time);

		$src['end_ts'] = VikRestaurants::createTimestamp($end_date, $end_hour, $end_min);

		// bind the details before save
		return parent::bind($src, $ignore);
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to
	 * ensure they are safe to store in the database.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		// check integrity using parent
		if (!parent::check())
		{
			return false;
		}

		// check start date
		if ($this->start_ts == -1)
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGEROOMCLOSURE2')));

			// invalid start date
			return false;
		}

		// check end date
		if ($this->end_ts == -1)
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGEROOMCLOSURE3')));

			// invalid end date
			return false;
		}

		// make sure start date is lower than end date
		if ($this->start_ts >= $this->end_ts)
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGEROOMCLOSURE2')));

			// invalid start date
			return false;
		}

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed    $ids  Either the record ID or a list of records.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($ids = null)
	{
		if (!$ids)
		{
			return false;
		}

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();

		// delete room closures
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_room_closure'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
