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
 * VikRestaurants statistics widget table.
 *
 * @since 1.8
 */
class VRETableStatswidget extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_stats_widget', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_user';
		$this->_requiredFields[] = 'widget';
		$this->_requiredFields[] = 'position';
		$this->_requiredFields[] = 'group';
		$this->_requiredFields[] = 'location';
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

		if (isset($src['id_user']) && !$src['id_user'])
		{
			// We are probably editing the default widget.
			// Unset ID to create a new widget and assign it
			// to the current user.
			$src['id']      = 0;
			$src['id_user'] = JFactory::getUser()->id;
		}

		// fetch ordering for new widgets that doesn't
		// specify a position index
		if (empty($src['id']) && empty($src['ordering']))
		{
			$src['ordering'] = $this->getNextOrder();
		}

		// JSON encode parameters in case of array/object
		if (isset($src['params']) && !is_scalar($src['params']))
		{
			$src['params'] = json_encode($src['params']);
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

		// delete widgets
		$q = $dbo->getQuery(true)
			->delete($dbo->qn($this->getTableName()))
			->where($dbo->qn('id_user') . ' > 0')
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Returns the configuration parameters stored
	 * within the record of the specified widget ID.
	 *
	 * @param 	integer  $id  The widget ID.
	 *
	 * @return 	array    The configuration associative array.
	 */
	public function getParams($id)
	{
		$dbo = JFactory::getDbo();

		// load widget configuration
		$q = $dbo->getQuery(true)
			->select($dbo->qn('params'))
			->from($dbo->qn($this->getTableName()))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// extract params from record
			return (array) json_decode($dbo->loadResult(), true);
		}
		
		// no confifuration
		return array();
	}
}
