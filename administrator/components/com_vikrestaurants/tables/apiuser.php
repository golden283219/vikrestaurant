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
 * VikRestaurants API user table.
 *
 * @since 1.8
 */
class VRETableApiuser extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_api_login', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'username';
		$this->_requiredFields[] = 'password';
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

		// properly format "denied plugins" list
		if (isset($src['denied']) && is_array($src['denied']))
		{
			$src['denied'] = json_encode($src['denied']);
		}

		// properly format "allowed IPs" list
		if (isset($src['ips']) && is_array($src['ips']))
		{
			$src['ips'] = json_encode($src['ips']);
		}

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

		// make sure the username doesn't already exist
		if (isset($this->username))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select(1)
				->from($dbo->qn('#__vikrestaurants_api_login'))
				->where($dbo->qn('username') . ' = ' . $dbo->q($this->username));

			if ($this->id)
			{
				$q->where($dbo->qn('id') . ' <> ' . (int) $this->id);
			}
			
			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				// register error message
				$this->setError(JText::_('VRAPIUSERUSERNAMEEXISTS'));

				// invalid start date
				return false;
			}
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

		// delete API users
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_api_login'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete users logs
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_api_login_logs'))
			->where($dbo->qn('id_login') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete users-event configuration
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_api_login_event_options'))
			->where($dbo->qn('id_login') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}
}
