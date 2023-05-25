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
 * VikRestaurants take-away delivery area table.
 *
 * @since 1.8
 */
class VRETableTkarea extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_delivery_area', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'name';
		$this->_requiredFields[] = 'type';
		$this->_requiredFields[] = 'content';
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

		// fetch ordering for new delivery area
		if ($src['id'] == 0)
		{
			$src['ordering'] = $this->getNextOrder();
		}

		if (isset($src['attributes']) && is_array($src['attributes']))
		{
			$src['attributes'] = json_encode($src['attributes']);
		}

		if (isset($src['content']) && is_array($src['content']))
		{
			$src['content'] = json_encode($src['content']);
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
		
		// check type
		if (isset($this->type))
		{
			if ($this->type < 1 || $this->type > 4)
			{
				// register error message
				$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGETKAREA2')));

				// invalid type
				return false;
			}
			else if (isset($this->content) && !json_decode($this->content, true))
			{
				// register error message
				$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRTKAREAFIELDSET2')));

				// missing contents
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

		// delete delivery areas
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_delivery_area'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
