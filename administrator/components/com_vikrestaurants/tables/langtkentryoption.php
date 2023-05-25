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
 * VikRestaurants language take-away menu entry option table.
 *
 * @since 1.8
 */
class VRETableLangtkentryoption extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_lang_takeaway_menus_entry_option', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'id_option';
		$this->_requiredFields[] = 'id_parent';
		$this->_requiredFields[] = 'tag';
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

		// check alias only if not empty
		if (!empty($src['alias']))
		{
			$dbo = JFactory::getDbo();
			
			// try to recover real parent ID
			$q = $dbo->getQuery(true)
				->select($dbo->qn('id_entry'))
				->from($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry'))
				->where($dbo->qn('id') . ' = ' . (int) $src['id_parent']);

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$id_parent = (int) $dbo->loadResult();
			}
			else
			{
				$id_parent = 0;
			}

			VRELoader::import('library.sef.helper');
			// make sure the alias is unique
			$src['alias'] = VRESefHelper::getUniqueAlias($src['alias'], 'tkentryoption', $src['id_option'], $id_parent);
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

		// delete lang entry options
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_lang_takeaway_menus_entry_option'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
