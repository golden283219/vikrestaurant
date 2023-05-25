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
 * VikRestaurants customer table.
 *
 * @since 1.8
 */
class VRETableCustomer extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_users', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'billing_name';
		$this->_requiredFields[] = 'billing_mail';
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
			// register user fields to create a new user account
			$user = array();
			$user['usertype']      = array();
			$user['user_name']     = $src['billing_name'];
			$user['user_mail']     = $src['user']['usermail'];
			$user['user_username'] = $src['user']['username'];
			$user['user_pwd1']     = $src['user']['password'];
			$user['user_pwd2']     = $src['user']['confirm'];

			// always unset 'user' attribute before saving an operator
			unset($src['user']);
		}

		// JSON encode restaurant fields
		if (isset($src['fields']) && !is_string($src['fields']))
		{
			$src['fields'] = json_encode($src['fields']);
		}

		// JSON encode take-away fields
		if (isset($src['tkfields']) && !is_string($src['tkfields']))
		{
			$src['tkfields'] = json_encode($src['tkfields']);
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
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		$is_new = empty($this->id);

		// invoke parent to store the record
		if (!parent::store($updateNulls))
		{
			// do not proceed in case of error
			return false;
		}

		// get customer data
		$args = $this->getProperties();

		// trigger event
		VREFactory::getEventDispatcher()->trigger('onCustomerSave', array($args, $is_new));

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
		if (!count($ids))
		{
			return false;
		}

		$dbo = JFactory::getDbo();
			
		// delete customers
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_users'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete customer delivery addresses
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_user_delivery'))
			->where($dbo->qn('id_user') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}
}
