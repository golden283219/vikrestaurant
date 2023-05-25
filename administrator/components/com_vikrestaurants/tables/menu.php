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
 * VikRestaurants menu table.
 *
 * @since 1.8
 */
class VRETableMenu extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_menus', 'id', $db);

		// register name as required field
		$this->_requiredFields[] = 'name';
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

		// fetch ordering for new products
		if ($src['id'] == 0)
		{
			$src['ordering'] = $this->getNextOrder();
		}

		if (!empty($src['days_filter']))
		{
			$src['days_filter'] = implode(',', $src['days_filter']);
		}
		else
		{
			$src['days_filter'] = '';
		}

		if (!empty($src['working_shifts']))
		{
			$src['working_shifts'] = implode(',', $src['working_shifts']);
		}
		else
		{
			$src['working_shifts'] = '';
		}

		if (isset($src['cost']))
		{
			$src['cost'] = abs((float) $src['cost']);
		}

		// generate alias in case it is empty when creating or updating
		if (empty($src['alias']) && (empty($src['id']) || isset($src['alias'])))
		{
			// generate unique alias starting from name
			$src['alias'] = $src['name'];
		}
		
		// check if we are going to update an empty alias
		if (isset($src['alias']) && strlen($src['alias']) == 0)
		{
			// avoid to update an empty alias by using a uniq ID
			$src['alias'] = uniqid();
		}

		if (!empty($src['alias']))
		{
			VRELoader::import('library.sef.helper');
			// make sure the alias is unique
			$src['alias'] = VRESefHelper::getUniqueAlias($src['alias'], 'menu', $src['id']);
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

		// delete all menus
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_menus'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete all relationships between menus and special days
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_sd_menus'))
			->where($dbo->qn('id_menu') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete all relationships between menus and reservations
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_res_menus_assoc'))
			->where($dbo->qn('id_menu') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// retrieve all menus sections
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_menus_section'))
			->where($dbo->qn('id_menu') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// delete all sections products
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_section_product_assoc'))
				->where($dbo->qn('id_section') . ' IN (' . implode(',', $dbo->loadColumn()) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete all menus sections
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_menus_section'))
				->where($dbo->qn('id_menu') . ' IN (' . implode(',', $ids) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();
		}

		// retrieve all lang menus
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_lang_menus'))
			->where($dbo->qn('id_menu') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// delete lang sections beloning to retrieved menus
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_lang_menus_section'))
				->where($dbo->qn('id_parent') . ' IN (' . implode(',', $dbo->loadColumn()) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete lang menus
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_lang_menus'))
				->where($dbo->qn('id_menu') . ' IN (' . implode(',', $ids) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();
		}

		return $aff;
	}
}
