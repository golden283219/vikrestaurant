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
 * VikRestaurants API user-event config table.
 *
 * @since 1.8.4
 */
class VRETableApiuseroptions extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_api_login_event_options', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_login';
		$this->_requiredFields[] = 'id_event';
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

		// in case of new record, look for an existing one
		if (empty($src['id']) && !empty($src['id_login']) && !empty($src['id_event']))
		{
			// load existing record, if any
			$existing = $this->getOptions($src['id_login'], $src['id_event']);

			if ($existing)
			{
				// override record ID
				$src['id'] = $existing->id;
			}
		}

		if (isset($src['options']) && !is_string($src['options']))
		{
			// JSON-encode configuration
			$src['options'] = json_encode($src['options']);
		}

		// bind the details before save
		return parent::bind($src, $ignore);
	}

	/**
	 * Returns the record assigned to the specified login/event.
	 *
	 * @param 	integer  $id_login  The login primary key.
	 * @param 	string   $id_event  The event unique name.
	 *
	 * @return 	mixed    The record object if exists, null otherwise.
	 */
	public function getOptions($id_login, $id_event)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn($this->getTableName()))
			->where($dbo->qn('id_login') . ' = ' . (int) $id_login)
			->where($dbo->qn('id_event') . ' = ' . $dbo->q($id_event));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			return null;
		}

		$data = $dbo->loadObject();
		$data->options = (array) json_decode($data->options);

		return $data;
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

		// delete API config
		$q = $dbo->getQuery(true)
			->delete($dbo->qn($this->getTableName()))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return $dbo->getAffectedRows();
	}
}
