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
 * VikRestaurants menus product table.
 *
 * @since 1.8
 */
class VRETableMenusproduct extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_section_product', 'id', $db);

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
			if (empty($src['hidden']))
			{
				$dbo = JFactory::getDbo();

				$src['ordering'] = $this->getNextOrder($dbo->qn('hidden') . ' = 0');
			}
			else
			{
				$src['ordering'] = 0;
			}
		}
		else
		{
			$src['hidden'] = null;
		}

		if (isset($src['tags']) && is_array($src['tags']))
		{
			// join tags
			$src['tags'] = implode(',', $src['tags']);
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

		// delete products
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_section_product'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$aff = (bool) $dbo->getAffectedRows();

		// delete section products
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_section_product_assoc'))
			->where($dbo->qn('id_product') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();
			
		// delete products variations
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_section_product_option'))
			->where($dbo->qn('id_product') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}
}
