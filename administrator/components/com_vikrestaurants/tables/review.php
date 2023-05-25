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
 * VikRestaurants review table.
 *
 * @since 1.8
 */
class VRETableReview extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_reviews', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'name';
		$this->_requiredFields[] = 'title';
		$this->_requiredFields[] = 'rating';
		$this->_requiredFields[] = 'id_takeaway_product';
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

		if (empty($src['ipaddr']))
		{
			$src['ipaddr'] = JFactory::getApplication()->input->server->getString('REMOTE_ADDR');
		}

		if (empty($src['timestamp']) || $src['timestamp'] == JFactory::getDbo()->getNullDate())
		{
			$src['timestamp'] = VikRestaurants::now();
		}
		else
		{
			// fetch datetime timestamp
			list($date, $time) = explode(' ', $src['timestamp']);
			list($hour, $min)  = explode(':', $time);

			$src['timestamp'] = VikRestaurants::createTimestamp($date, $hour, $min);
		}

		if (empty($src['conf_key']))
		{
			// auto-generate confirmation key
			$src['conf_key'] = VikRestaurants::generateSerialCode(12, 'review-confkey');
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
		
		// check rating value
		if (isset($this->rating) && ($this->rating < 1 || $this->rating > 5))
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGEREVIEW5')));

			// invalid rating
			return false;
		}

		// make sure that the product exists, otherwise a user would be
		// able to post reviews for products that don't exist
		if (empty($this->id))
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select(1)
				->from($dbo->qn('#__vikrestaurants_takeaway_menus_entry'))
				->where($dbo->qn('id') . ' = ' . (int) $this->id_takeaway_product);

			$dbo->setQuery($q, 0, 1);
			$dbo->execute();

			if ($dbo->getNumRows() == 0)
			{
				// register error message
				$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRTKCARTROWNOTFOUND')));

				// invalid product
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

		// delete rooms
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_reviews'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}
}
