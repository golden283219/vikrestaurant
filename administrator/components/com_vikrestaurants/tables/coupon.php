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
 * VikRestaurants coupon table.
 *
 * @since 1.8
 */
class VRETableCoupon extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_coupons', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'code';
		$this->_requiredFields[] = 'group';
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

		if (isset($src['code']) && strlen($src['code']) == 0)
		{
			// generate coupon code in case it was specified as an empty string
			$src['code'] = VikRestaurants::generateSerialCode(12, 'coupon');
		}

		if (isset($src['datestart']))
		{
			// convert start date to UNIX timestamp
			if (strlen($src['datestart']))
			{
				$src['datestart'] = VikRestaurants::createTimestamp($src['datestart'], 0, 0);
			}
			else
			{
				$src['datestart'] = -1;
			}
		}

		if (isset($src['dateend']))
		{
			// convert end date to UNIX timestamp
			if (strlen($src['dateend']))
			{
				$src['dateend'] = VikRestaurants::createTimestamp($src['dateend'], 23, 59);
			}
			else
			{
				$src['dateend'] = -1;
			}
		}

		if (isset($src['datestart']) && isset($src['dateend']))
		{
			// unset both dates in case at least one of them is invalid
			if ($src['datestart'] == -1 || $src['dateend'] == -1)
			{
				$src['datevalid'] = '';
			}
			else
			{
				$src['datevalid'] = $src['datestart'] . '-' . $src['dateend'];
			}
		}

		unset($src['datestart']);
		unset($src['dateend']);

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

		// make sure start date is equals or lower than end date
		if (!empty($this->datevalid))
		{
			// extract start and and dates from property
			list($start, $end) = explode('-', $this->datevalid);

			if ($start > $end)
			{
				// register error message
				$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRMANAGECOUPON5')));

				// invalid start date
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

		// delete coupon codes
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_coupons'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		return (bool) $dbo->getAffectedRows();
	}

	/**
	 * Uses the specified coupon code.
	 *
	 * @param 	mixed  $coupon  Either the coupon ID or its code.
	 * @param 	mixed  $group   The group to which the coupon belong:
	 * 							- 0  restaurant;
	 * 						    - 1  takeaway.
	 *
	 * @return 	mixed  The coupon details on success, false otherwise.
	 */
	public function redeem($coupon, $group = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		$q->select('*');
		$q->from($dbo->qn('#__vikrestaurants_coupons'));
		$q->where(1);

		if (!is_null($group))
		{
			// filter by group if specified
			$q->where($dbo->qn('group') . ' = ' . (int) $group);
		}

		if (preg_match("/[^0-9]/", $coupon))
		{
			// the coupon code contains letters too, search by code only
			$q->where($dbo->qn('code') . ' = ' . $dbo->q($coupon));
		}
		else
		{
			// the coupon code contains only numbers, search by ID too
			$q->andWhere(array(
				$dbo->qn('id') . ' = ' . (int) $coupon,
				$dbo->qn('code') . ' = ' . $dbo->q($coupon),
			), 'OR');
		}

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// coupon code not found
			return false;
		}

		// get coupon data
		$data = $dbo->loadObject();

		if ($data->type == 2)
		{
			// in case of GIFT coupon, delete it
			$this->delete($data->id);
		}
		else
		{
			// otherwise increase the usages counter
			$data->usages++;

			// create array to save essential columns
			$src = array(
				'id'     => $data->id,
				'usages' => $data->usages,
			);
			$this->save($src);
		}

		return $data;
	}
}
