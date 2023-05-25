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
 * VikRestaurants operator table.
 *
 * @since 1.8
 */
class VRETableOperator extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_operator', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'firstname';
		$this->_requiredFields[] = 'lastname';
		$this->_requiredFields[] = 'email';
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

		$user = null;

		// check if the user attribute was passed
		if (isset($src['user']))
		{
			if (empty($src['jid']))
			{
				// register user fields to create a new user account
				$user = array();
				$user['usertype']      = $src['user']['type'];
				$user['user_name']     = $src['firstname'] . ' ' . $src['lastname'];
				$user['user_mail']     = $src['email'];
				$user['user_username'] = $src['user']['username'];
				$user['user_pwd1']     = $src['user']['password'];
				$user['user_pwd2']     = $src['user']['confirm'];
			}

			// always unset 'user' attribute before saving an operator
			unset($src['user']);
		}

		if (isset($src['rooms']) && is_array($src['rooms']))
		{
			// stringify rooms list
			$src['rooms'] = implode(',', $src['rooms']);
		}

		if (isset($src['products']) && is_array($src['products']))
		{
			// stringify products list
			$src['products'] = implode(',', $src['products']);
		}

		// bind the details before save
		$return = parent::bind($src, $ignore);

		if ($return && $user)
		{
			try
			{
				// try to create a new Joomla User account
				$this->jid = RestaurantsHelper::createNewJoomlaUser($user);
			}
			catch (Exception $e)
			{
				// an error occurred, register error and abort saving
				$this->setError($e);

				return false;
			}
		}

		return $return;
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

		// delete operators
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_operator'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete operators logs
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_operator_log'))
			->where($dbo->qn('id_operator') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}

	/**
	 * Method to delete one or more logs.
	 *
	 * @param   mixed    $ids  Either the record ID or a list of records.
	 *
	 * @return  boolean  True on success.
	 */
	public function deleteLogs($ids = null)
	{
		if (!$ids)
		{
			return false;
		}

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();

		// delete operators logs
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_operator_log'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Method to delete all the logs older than the specified limit.
	 *
	 * @param   string   $limit  A date string to be passed to `strtotime()`.
	 *
	 * @return  integer  The number of deleted records.
	 */
	public function flushLogs($limit)
	{
		// calculate timestamp limit
		$limit = strtotime('-' . preg_replace("/^[-+\s]+/", '', $limit));

		if (!$limit)
		{
			// invalid limit
			return 0;
		}

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();

		// delete operators logs
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_operator_log'))
			->where($dbo->qn('createdon') . ' < ' . (int) $limit);

		$dbo->setQuery($q);
		$dbo->execute();

		return $dbo->getAffectedRows();
	}
}
