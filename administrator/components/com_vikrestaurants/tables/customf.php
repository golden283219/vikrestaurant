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
 * VikRestaurants custom fields table.
 *
 * @since 1.8
 */
class VRETableCustomf extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_custfields', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'group';
		$this->_requiredFields[] = 'name';
		$this->_requiredFields[] = 'type';
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

		// fetch ordering for new custom fields
		if ($src['id'] == 0)
		{
			$dbo = JFactory::getDbo();

			$src['ordering'] = $this->getNextOrder($dbo->qn('group') . ' = ' . (int) $src['group']);
		}

		// check if "choose" property is set
		if (!empty($src['choose']) && VRCustomFields::isSelect($src))
		{
			// join answers type select
			$src['choose'] = implode(';;__;;', array_filter((array) $src['choose']));
		}

		if (VRCustomFields::isInputText($src) && VRCustomFields::isPhoneNumber($src))
		{
			// use selected default prefix
			$src['choose'] = $src['def_prfx'];
		}

		if (VRCustomFields::isSeparator($src))
		{
			// use selected class suffix
			$src['choose'] = $src['sep_suffix'];
		}

		if (isset($src['group']))
		{
			if ($src['group'] == 0 && VRCustomFields::isDelivery($src))
			{
				// unset delivery rule in case of "restaurant" group
				$src['rule'] = 0;
			}

			if ($src['group'] == 0 || $src['required'] == 0)
			{
				// unset required for delivery in case of "restaurant" group
				// or if the field is not mandatory
				$src['required_delivery'] = 0;
			}
		}

		unset($src['def_prfx']);
		unset($src['sep_suffix']);

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

		// delete working shifts
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_custfields'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
