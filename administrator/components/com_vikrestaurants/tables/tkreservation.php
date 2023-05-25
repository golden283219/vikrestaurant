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
 * VikRestaurants take-away order table.
 *
 * @since 1.8
 */
class VRETableTkreservation extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_takeaway_reservation', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'sid';
		$this->_requiredFields[] = 'checkin_ts';
		$this->_requiredFields[] = 'delivery_service';
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
				$src['sid'] = VikRestaurants::generateSerialCode(16, 'order-sid');
			}

			// generate confirmation code if not specified
			if (!isset($src['conf_key']))
			{
				$src['conf_key'] = VikRestaurants::generateSerialCode(12, 'order-confkey');
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

		// encode route details in JSON format in case they was passed as array/object
		if (isset($src['route']) && !is_string($src['route']))
		{
			$src['route'] = json_encode($src['route']);
		}

		/**
		 * Update "keep orders locked" column every time the order
		 * is saved with PENDING status.
		 */
		if (isset($src['status']) && $src['status'] == 'PENDING')
		{
			// use server time() function because this value is never adjusted to the local time
			$src['locked_until'] = strtotime('+' . VREFactory::getConfig()->getUint('tklocktime') . ' minutes');
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
		// instantiate logger
		$logger = VREOperatorLogger::getInstance();
		
		// check if the order has been already cached (1: take-away)
		if (!$logger->getCached($this->id, 1))
		{
			// cache previous order details (1: take-away)
			$logger->cache($this->id, 1);
		}

		// invoke parent to store the record
		if (!parent::store($updateNulls))
		{
			// do not proceed in case of error
			return false;
		}

		// generate log (1: take-away)
		$logger->generate($this->id, 1);

		/**
		 * Unset cached reservation every time something changes.
		 *
		 * @since 1.8.2
		 */
		VREOrderFactory::changed('takeaway', $this->id);

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

		$dbo = JFactory::getDbo();
			
		// delete orders
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_takeaway_reservation'))
			->where($dbo->qn('id') . ' IN (' . implode(',', $ids) . ')');
		
		$dbo->setQuery($q);
		$dbo->execute();

		$sff = (bool) $dbo->getAffectedRows();

		// get order products
		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc'))
			->where($dbo->qn('id_res') . ' IN (' . implode(',', $ids) . ')');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// delete orders products toppings
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_res_prod_topping_assoc'))
				->where($dbo->qn('id_assoc') . ' IN (' . implode(',', $dbo->loadColumn()) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows();

			// delete orders products
			$q = $dbo->getQuery(true)
				->delete($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc'))
				->where($dbo->qn('id_res') . ' IN (' . implode(',', $ids) . ')');

			$dbo->setQuery($q);
			$dbo->execute();

			$aff = $aff || $dbo->getAffectedRows(); 
		}

		// delete orders statuses
		$q = $dbo->getQuery(true)
			->delete($dbo->qn('#__vikrestaurants_order_status'))
			->where($dbo->qn('id_order') . ' IN (' . implode(',', $ids) . ')')
			->where($dbo->qn('group') . ' = 2');
		
		$dbo->setQuery($q);
		$dbo->execute();

		return $aff || $dbo->getAffectedRows();
	}

	/**
	 * Returns an object containing the bill total lines.
	 *
	 * @param 	integer  $id  The order ID.
	 *
	 * @return 	mixed    The bill details on success, false otherwise.
	 */
	public function getBill($id)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('discount_val', 'discount'))
			->select($dbo->qn('tip_amount', 'tip'))
			->select($dbo->qn('total_to_pay', 'total'))
			->select($dbo->qn('delivery_charge', 'deliveryCharge'))
			->select($dbo->qn('pay_charge', 'payCharge'))
			->select($dbo->qn('taxes'))
			->from($dbo->qn('#__vikrestaurants_takeaway_reservation'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			return false;
		}

		$bill = $dbo->loadObject();

		// calculate total net (add discount, then subtract pay charge and tip)
		$bill->net = $bill->total + $bill->discount - $bill->payCharge - $bill->tip - $bill->deliveryCharge - $bill->taxes;

		// calculate final net (subtract discount from net)
		$bill->finalNet = $bill->net - $bill->discount;

		return $bill;
	}

	/**
	 * Updates the bill of the given order by the specified amount.
	 *
	 * @param 	integer  $id      The order ID.
	 * @param 	float    $amount  The amount to add/subtract.
	 * @param 	float    $taxes   The taxes to add/subtract.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	public function updateBill($id, $amount, $taxes = null)
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->select($dbo->qn('total_to_pay'))
			->select($dbo->qn('taxes'))
			->from($dbo->qn('#__vikrestaurants_takeaway_reservation'))
			->where($dbo->qn('id') . ' = ' . (int) $id);

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// order not found
			return false;
		}

		$data = $dbo->loadAssoc();

		// add amount to bill value
		$total = (float) $data['total_to_pay'] + (float) $amount;

		if (!is_null($taxes))
		{
			if (VREFactory::getConfig()->getUint('tkusetaxes') == 1)
			{
				// taxes EXCLUDED, add them to total cost
				$total += $taxes;
			}
			
			// add taxes to bill
			$taxes = (float) $data['taxes'] + (float) $taxes;
		}

		// make sure the total is not lower than 0
		$data['total_to_pay'] = max(array(0, $total));
		// make sure the taxes amount is not lower than 0
		$data['taxes'] = max(array(0, $taxes));

		// save order
		return $this->save($data);
	}
}
