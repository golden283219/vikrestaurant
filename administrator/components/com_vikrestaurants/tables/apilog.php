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
 * VikRestaurants API log table.
 *
 * @since 1.8
 */
class VRETableApilog extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_api_login_logs', 'id', $db);
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

		if (empty($src['id']))
		{
			if (empty($src['ip']))
			{
				// use current IP address in case it hasn't been specified
				$src['ip'] = JFactory::getApplication()->input->server->get('REMOTE_ADDR');
			}

			if (empty($src['createdon']))
			{
				// register current time in case it hasn't been specified
				$src['createdon'] = VikRestaurants::now();
			}

			// get logging modality:
			// - 2 always
			// - 1 only errors
			// - 0 never
			$mode = VREFactory::getConfig()->getUint('apilogmode');

			$id_login = isset($src['id_login']) ? $src['id_login'] : 0;
			$status   = isset($src['status'])   ? $src['status']   : 0;

			// check if we should insert a new record or if we should
			// update an existing one according to the loggin mode
			if ($mode == 0 || ($mode == 1 && !empty($src['status'])))
			{
				$dbo = JFactory::getDbo();
				
				$q = $dbo->getQuery(true)
					->select($dbo->qn('id'))
					->from($dbo->qn($this->getTableName()))
					->where($dbo->qn('id_login') . ' = ' . (int) $id_login)
					->where($dbo->qn('status') . ' = ' . (int) $status);

				$dbo->setQuery($q, 0, 1);
				$dbo->execute();

				if ($dbo->getNumRows())
				{
					// force update
					$src['id'] = $dbo->loadResult();
				}
			}
		}

		// bind the details before save
		return parent::bind($src, $ignore);
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

		// find number of existing logs
		$q = $dbo->getQuery(true)
			->select('COUNT(1)')
			->from($dbo->qn('#__vikrestaurants_api_login_logs'));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ((int) $dbo->loadResult() == count($ids))
		{
			// truncate all in case the user selected all the existing logs
			return $this->truncate();
		}

		// delete API logs
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_api_login_logs'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Method to truncate the table.
	 *
	 * @return  boolean  True on success.
	 */
	public function truncate()
	{
		$dbo = JFactory::getDbo();

		// truncate API logs
		$q = "TRUNCATE TABLE " . $dbo->qn('#__vikrestaurants_api_login_logs');

		$dbo->setQuery($q);
		return $dbo->execute();
	}
}
