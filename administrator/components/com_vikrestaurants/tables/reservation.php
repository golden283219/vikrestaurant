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

// track log if the user is an operator
VRELoader::import('library.operator.logger');

/**
 * VikRestaurants restaurant reservation table.
 *
 * @since 1.8
 */
class VRETableReservation extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_reservation', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'sid';
		$this->_requiredFields[] = 'id_table';
		$this->_requiredFields[] = 'checkin_ts';
		$this->_requiredFields[] = 'people';
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

		// check if new record
		if (empty($src['id']))
		{
			// generate serial ID if not specified
			if (!isset($src['sid']))
			{
				$src['sid'] = VikRestaurants::generateSerialCode(16, 'reservation-sid');
			}

			// generate confirmation code if not specified
			if (!isset($src['conf_key']))
			{
				$src['conf_key'] = VikRestaurants::generateSerialCode(12, 'reservation-confkey');
			}

			// register current user as author, if not specified
			if (!isset($src['created_by']))
			{
				$src['created_by'] = JFactory::getUser()->id;
			}

			// register current datetime as creation date, if not specified
			if (!isset($src['created_on']))
			{
				$src['created_on'] = VikRestaurants::now();
			}
		}
		else
		{
			// register current datetime as modified date, if not specified
			if (!isset($src['modified_on']))
			{
				$src['modified_on'] = VikRestaurants::now();
			}
		}

		// create checkin timestamp in case the date attribute is set
		if (!isset($src['checkin_ts']) && isset($src['date']))
		{
			// extract hours and minutes
			if (!empty($src['hourmin']))
			{
				list($hour, $min) = explode(':', $src['hourmin']);
			}
			else
			{
				$hour = isset($src['hour']) ? $src['hour'] : 0;
				$min  = isset($src['min'])  ? $src['min']  : 0;
			}

			$src['checkin_ts'] = VikRestaurants::createTimestamp($src['date'], (int) $hour, (int) $min);
		}

		// encode custom fields in JSON format in case they was passed as array/object
		if (isset($src['custom_f']) && !is_string($src['custom_f']))
		{
			$src['custom_f'] = json_encode($src['custom_f']);
		}

		// stringify coupon code in case an array/object was passed
		if (isset($src['coupon_str']) && !is_string($src['coupon_str']))
		{
			// always use an object
			$coupon = (object) $src['coupon_str'];

			// create coupon string
			$src['coupon_str'] = @$coupon->code . ';;' . @$coupon->value . ';;' . @$coupon->percentot;
		}

		/**
		 * Update "keep table locked" column every time the reservation
		 * is saved with PENDING status.
		 */
		if (isset($src['status']) && $src['status'] == 'PENDING')
		{
			// use server time() function because this value is never adjusted to the local time
			$src['locked_until'] = strtotime('+' . VREFactory::getConfig()->getUint('tablocktime') . ' minutes');

		}

		// bind the details before save
		return parent::bind($src, $ignore);
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		$is_new = empty($this->id);

		// instantiate logger
		$logger = VREOperatorLogger::getInstance();

		// check if the order has been already cached
		if (!$logger->getCached($this->id, 0))
		{
			// cache previous order details (0: restaurant)
			$logger->cache($this->id, 0);
		}

		// invoke parent to store the record
		if (!parent::store($updateNulls))
		{
			// do not proceed in case of error
			return false;
		}

		// check if we are updating the status of an existing record
		if (!$is_new && !empty($this->status))
		{
			$dbo = JFactory::getDbo();
			
			// try to update any reservation children
			$q = $dbo->getQuery(true)
				->update($dbo->qn('#__vikrestaurants_reservation'))
				->set($dbo->qn('status') . ' = ' . $dbo->q($this->status))
				->where($dbo->qn('id_parent') . ' = ' . $this->id);

			if (!empty($this->checkin_ts))
			{
				// update check-in date and time
				$q->set($dbo->qn('checkin_ts') . ' = ' . $this->checkin_ts);
			}

			if (!empty($this->people))
			{
				// update number of people too
				$q->set($dbo->qn('people') . ' = ' . $this->people);
			}

			if (!empty($this->locked_until))
			{
				// update locked time too
				$q->set($dbo->qn('locked_until') . ' = ' . $this->locked_until);
			}

			$dbo->setQuery($q);
			$dbo->execute();
		}

		// generate log (0: restaurant)
		$logger->generate($this->id, 0);

		/**
		 * Unset cached reservation every time something changes.
		 *
		 * @since 1.8.2
		 */
		VREOrderFactory::changed('restaurant', $this->id);

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
		if (!count($ids))
		{
			return false;
		}

		$ids = (array) $ids;

		$dbo = JFactory::getDbo();
			
		// delete reservations
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_reservation'))
			->where(array(
				$dbo->qn('id') . ' IN (' . implode(',', $ids) . ')',
				$dbo->qn('id_parent') . ' IN (' . implode(',', $ids) . ')',
			), 'OR');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$sff = (bool) $dbo->getAffectedRows();

		// delete reservations menus
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_res_menus_assoc'))
			->where($dbo->qn('id_reservation') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete reservations products
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_res_prod_assoc'))
			->where($dbo->qn('id_reservation') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$aff = $aff || $dbo->getAffectedRows();

		// delete reservations statuses
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_order_status'))
			->where($dbo->qn('id_order') . ' IN (' . implode(',', $ids) . ')')
			->where($dbo->qn('group') . ' = 1');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}

	/**
	 * Returns an object containing the bill total lines.
	 *
	 * @param 	integer  $id     The reservation ID.
	 * @param 	mixed 	 $total  If specified, the given amount will 
	 * 					         override the reservation total.
	 *
	 * @return 	mixed    The bill details on success, false otherwise.
	 */
	public function getBill($id, $total = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('discount_val', 'discount'))
			->select($dbo->qn('tip_amount', 'tip'))
			->select($dbo->qn('bill_value', 'total'))
			->from($dbo->qn('#__vikrestaurants_reservation'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			return false;
		}

		$bill = $dbo->loadObject();

		/**
		 * @todo add support for payment charge
		 */
		$bill->payCharge = 0;

		if (!is_null($total))
		{
			// override total
			$bill->total = $total;
		}

		// calculate total net (add discount, then subtract pay charge and tip)
		$bill->net = $bill->total + $bill->discount - $bill->payCharge - $bill->tip;

		// calculate final net (subtract discount from net)
		$bill->finalNet = $bill->net - $bill->discount;

		return $bill;
	}

	/**
	 * Updates the bill of the given reservation by the specified amount.
	 *
	 * @param 	integer  $id      The reservation ID.
	 * @param 	float    $amount  The amount to add/subtract.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function updateBill($id, $amount)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('bill_value'))
			->from($dbo->qn('#__vikrestaurants_reservation'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// reservation not found
			return false;
		}

		// add amount to bill value
		$total = (float) $dbo->loadResult() + $amount;

		// make sure the total is not lower than 0
		$total = max(array(0, $total));

		// create update data
		$data = array(
			'id'         => $id,
			'bill_value' => $total,
		);

		// save reservation
		return $this->save($data);
	}
}
