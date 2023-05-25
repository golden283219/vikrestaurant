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
 * Driver class used to export the take-away orders and the
 * restaurant reservations in CSV format.
 *
 * @since 1.8
 */
class VREOrderExportDriverCsv extends VREOrderExportDriver
{
	/**
	 * @override
	 * Returns the form parameters required to the CSV driver.
	 *
	 * @return 	array
	 */
	public function getForm()
	{
		return array(
			/**
			 * Choose whether only the confirmed reservations
			 * will be retrieved. Closures are never retrieved
			 * even this option is turned off.
			 *
			 * @var checkbox
			 */
			'confirmed' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_EXPORT_DRIVER_CSV_CONFIRMED_STATUS_FIELD'),
				'help'    => JText::_('VRE_EXPORT_DRIVER_CSV_CONFIRMED_STATUS_FIELD_HELP'),
				'default' => 1,
			),

			/**
			 * Choose whether the reservations items should be
			 * retrieved and included within the CSV.
			 *
			 * @var checkbox
			 */
			'useitems' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_EXPORT_DRIVER_CSV_USE_ITEMS_FIELD'),
				'help'    => JText::_('VRE_EXPORT_DRIVER_CSV_USE_ITEMS_FIELD_HELP'),
				'default' => 0,
			),

			/**
			 * The separator character that will be used to separate
			 * the value of the columns.
			 *
			 * @var select
			 */
			'delimiter' => array(
				'type'    => 'select',
				'label'   => JText::_('VRE_EXPORT_DRIVER_CSV_DELIMITER_FIELD'),
				'help'    => JText::_('VRE_EXPORT_DRIVER_CSV_DELIMITER_FIELD_HELP'),
				'default' => ',',
				'options' => array(
					',' => JText::_('VRE_EXPORT_DRIVER_CSV_DELIMITER_FIELD_OPT_COMMA'),
					';' => JText::_('VRE_EXPORT_DRIVER_CSV_DELIMITER_FIELD_OPT_SEMICOLON'),
				),
			),

			/**
			 * The enclosure character that will be used to wrap,
			 * and escape, the value of the columns.
			 *
			 * @var select
			 */
			'enclosure' => array(
				'type'    => 'select',
				'label'   => JText::_('VRE_EXPORT_DRIVER_CSV_ENCLOSURE_FIELD'),
				'help'    => JText::_('VRE_EXPORT_DRIVER_CSV_ENCLOSURE_FIELD_HELP'),
				'default' => '"',
				'options' => array(
					'"'  => JText::_('VRE_EXPORT_DRIVER_CSV_ENCLOSURE_FIELD_OPT_DOUBLE_QUOTE'),
					'\'' => JText::_('VRE_EXPORT_DRIVER_CSV_ENCLOSURE_FIELD_OPT_SINGLE_QUOTE'),
				),
			),
		);
	}

	/**
	 * @override
	 * Exports the reservations in the given format.
	 *
	 * @return 	string 	The resulting export string.
	 */
	public function export()
	{
		// start catching output buffer
		ob_start();

		// open file resource pointing to PHP OUTPUT
		$handle = fopen('php://output', 'w');
		
		// output CSV to the given resource
		$this->output($handle);

		// catch buffer
		$buffer = ob_get_contents();
		
		// close resource
		fclose($handle);

		// close output buffer
		ob_end_clean();

		// strip trailing new line and return CSV string
		return trim($buffer, "\n");
	}

	/**
	 * @override
	 * Downloads the reservations in a file compatible with the given format.
	 *
	 * @param 	string 	$filename 	The name of the file that will be downloaded.
	 *
	 * @return 	void
	 *
	 * @uses 	export()
	 */
	public function download($filename = null)
	{
		if ($filename)
		{
			// strip file extension
			$filename = preg_replace("/\.csv$/i", '', $filename);
		}
		else
		{
			// use current date time as name
			$filename = date('Y-m-d H_i_s', VikRestaurants::now());
		}

		// declare headers
		header('Content-Encoding: UTF-8');
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename=' . $filename . '.csv');
		header('Cache-Control: no-store, no-cache');
		// UTF-8 BOM for correct encoding on Microsoft Excel (Windows only)
		//echo "\xEF\xBB\xBF";

		// open file resource pointing to PHP OUTPUT
		$handle = fopen('php://output', 'w');
		
		// output CSV to the given resource
		$this->output($handle);
		
		// close resource
		fclose($handle);
		exit;
	}

	/**
	 * Generates the CSV structure by putting the fetched
	 * bytes into the specified resource.
	 *
	 * @param 	mixed 	$handle  The resource pointer created with fopen().
	 *
	 * @return 	void
	 */
	protected function output($handle)
	{
		if (!$handle)
		{
			throw new RuntimeException('Invalid resource for CSV generation');
		}

		$dispatcher = VREFactory::getEventDispatcher();

		// retrieve settings
		$delimiter = $this->getOption('delimiter', ',');
		$enclosure = $this->getOption('enclosure', '"');

		$head = array();

		if ($this->isGroup('restaurant'))
		{
			// order number
			$head[] = JText::_('VRMANAGERESERVATION1');
			// order key
			$head[] = JText::_('VRMANAGERESERVATION2');
			// created on
			$head[] = JText::_('VRCREATEDON');
			// checkin
			$head[] = JText::_('VRMANAGERESERVATION3');
			// people
			$head[] = JText::_('VRMANAGERESERVATION4');
			// room
			$head[] = JText::_('VRMANAGETABLE4');
			// table
			$head[] = JText::_('VRMANAGERESERVATION5');
			// payment method
			$head[] = JText::_('VRMANAGERESERVATION20');
			// bill value
			$head[] = JText::_('VRMANAGERESERVATION10');
			// purchaser nominative
			$head[] = JText::_('VRMANAGERESERVATION18');
			// purcahser e-mail
			$head[] = JText::_('VRMANAGERESERVATION6');
			// purcahser phone
			$head[] = JText::_('VRMANAGERESERVATION16');
			// coupon
			$head[] = JText::_('VRMANAGERESERVATION8');
			// status
			$head[] = JText::_('VRMANAGERESERVATION12');
			// notes
			$head[] = JText::_('VRMANAGERESERVATIONTITLE3');

			$fields = 0;
		}
		else
		{
			// order number
			$head[] = JText::_('VRMANAGETKRES1');
			// order key
			$head[] = JText::_('VRMANAGETKRES2');
			// created on
			$head[] = JText::_('VRCREATEDON');
			// checkin
			$head[] = JText::_('VRMANAGETKRES3');
			// delivery service
			$head[] = JText::_('VRMANAGETKRES4');
			// payment method
			$head[] = JText::_('VRMANAGETKRES27');
			// total to pay
			$head[] = JText::_('VRMANAGETKRES8');
			// taxes
			$head[] = JText::_('VRMANAGETKRES21');
			// delivery charge
			$head[] = JText::_('VRMANAGETKRES31');
			// purchaser nominative
			$head[] = JText::_('VRMANAGETKRES25');
			// purcahser e-mail
			$head[] = JText::_('VRMANAGETKRES5');
			// purcahser phone
			$head[] = JText::_('VRMANAGETKRES23');
			// coupon
			$head[] = JText::_('VRMANAGETKRES7');
			// status
			$head[] = JText::_('VRMANAGETKRES9');
			// notes
			$head[] = JText::_('VRMANAGETKRESTITLE4');

			$fields = 1;
		}

		// get custom fields
		$fields = VRCustomFields::getList($fields, VRCustomFields::FILTER_EXCLUDE_REQUIRED_CHECKBOX | VRCustomFields::FILTER_EXCLUDE_SEPARATOR);
		// translate the fields
		VRCustomFields::translate($fields);

		// iterate fields and push them within the head
		foreach ($fields as $field)
		{
			// exclude custom fields that are already displayed by
			// using the purchaser information
			if (!VRCustomFields::isNominative($field)
				&& !VRCustomFields::isEmail($field)
				&& !VRCustomFields::isPhoneNumber($field))
			{
				$head[] = $field['langname'];
			}
		}

		// check if the items should be included
		if ($this->getOption('useitems'))
		{
			// items (both restaurant and take-away)
			$head[] = JText::_('VRMANAGETKRES22');
		}

		/**
		 * Trigger event to allow the plugins to manipulate the heading
		 * row of the CSV file. Here it is possible to attach new columns,
		 * detach existing columns and reorder them. Notice that the same
		 * changes must be applied to the body of the CSV, otherwise the
		 * columns might result shifted.
		 *
		 * @param 	array   &$head    The CSV head array.
		 * @param 	mixed   $handler  The current handler instance.
		 *
		 * @return 	void
		 *
		 * @since   1.8.5
		 */
		$dispatcher->trigger('onBuildHeadCSV', array(&$head, $this));

		// put head within the CSV
		$this->putRow($handle, $head, $delimiter, $enclosure);

		$config   = VREFactory::getConfig();
		$currency = VREFactory::getCurrency();

		$records = $this->getRecords();

		// iterate records and create arrays CSV-compatible
		foreach ($records as $obj)
		{
			$r = array();

			// extract coupon details
			$coupon = '';

			if (strlen($obj->coupon_str))
			{
				list($coupon_code, $coupon_type, $coupon_amount) = explode(';;', $obj->coupon_str);

				$coupon = $coupon_code . ' : ' . ($coupon_type == 1 ? $coupon_amount . '%' : $currency->format($coupon_amount)); 
			}

			// fetch status
			$status = strtoupper(JText::_('VRRESERVATIONSTATUS' . strtoupper($obj->status)));

			// decode stored CF data
			$cf_json = (array) json_decode($obj->custom_f, true);

			// translate custom fields
			$cf_json = VRCustomFields::translateObject($cf_json, $fields);

			// order number
			$r[] = $obj->id;
			// order key
			$r[] = $obj->sid;
			// creation date
			$r[] = date($config->get('dateformat') . ' ' . $config->get('timeformat'), $obj->created_on);
			// checkin
			$r[] = date($config->get('dateformat') . ' ' . $config->get('timeformat'), $obj->checkin_ts);

			if ($this->isGroup('restaurant'))
			{
				// people
				$r[] = $obj->people;
				// room
				$r[] = $obj->room_name;

				// table
				if (!$obj->cluster)
				{
					$r[] = $obj->table_name;
				}
				else
				{
					// get list of all the booked tables
					$tables = explode(',', $obj->cluster);
					// push original table in first position
					array_unshift($tables, $obj->table_name);
					// join all the tables and push within the csv
					$r[] = implode(', ', $tables);
				}

				// payment method
				$r[] = $obj->payment_name;
				// bill value
				$r[] = $currency->format($obj->bill_value);
			}
			else
			{
				// delivery service
				$r[] = JText::_($obj->delivery_service ? 'VRMANAGETKRES14' : 'VRMANAGETKRES15');
				// payment method
				$r[] = $obj->payment_name;
				// total to pay
				$r[] = $currency->format($obj->total_to_pay);
				// taxes
				$r[] = $currency->format($obj->taxes);
				// delivery charge
				$r[] = $currency->format($obj->delivery_service);
			}

			// purchaser name
			$r[] = $obj->purchaser_nominative;
			// purchaser e-mail
			$r[] = $obj->purchaser_mail;
			// purchaser phone
			$r[] = $obj->purchaser_phone;

			// coupon
			$r[] = $coupon;
			// status
			$r[] = $status;
			// notes
			$r[] = strip_tags($obj->notes);

			// add custom fields
			foreach ($fields as $field)
			{
				// exclude custom fields that are already displayed by
				// using the purchaser information
				if (!VRCustomFields::isNominative($field)
					&& !VRCustomFields::isEmail($field)
					&& !VRCustomFields::isPhoneNumber($field))
				{
					// get access key
					$k = JText::_($field['name']);

					$r[] = isset($cf_json[$k]) ? $cf_json[$k] : '';
				}
			}

			// check if the items should be included
			if ($this->getOption('useitems'))
			{
				// items (both restaurant and take-away)
				$r[] = implode("\r\n", $obj->items);
			}

			/**
			 * Trigger event to allow the plugins to manipulate the row that
			 * is going to be added into the CSV body. Here it is possible to
			 * attach new columns, detach existing columns and reorder them.
			 * Notice that the same changes must be applied to the head of the
			 * CSV, otherwise the columns might result shifted.
			 *
			 * @param 	array   &$row     The CSV body row.
			 * @param   object  $data     The row fetched from the database.
			 * @param 	mixed   $handler  The current handler instance.
			 *
			 * @return 	void
			 *
			 * @since   1.8.5
			 */
			$dispatcher->trigger('onBuildRowCSV', array(&$r, $obj, $this));

			// put records within the CSV
			$this->putRow($handle, $r, $delimiter, $enclosure);
		}

		/**
		 * Trigger event to allow the plugins to append additional rows
		 * within the CSV file.
		 *
		 * @param 	array   $records  An array of database records.
		 * @param 	mixed   $handler  The current handler instance.
		 *
		 * @return 	array   The rows to include. Must be an array of arrays.
		 *
		 * @since   1.8.5
		 */
		$results = $dispatcher->trigger('onAfterBuildRowsCSV', array($records, $this));

		// iterate plugin results
		foreach ($results as $res)
		{
			// iterate result rows
			foreach ($res as $row)
			{
				if (is_array($row))
				{
					// put records within the CSV
					$this->putRow($handle, $row, $delimiter, $enclosure);
				}
			}
		}
	}

	/**
	 * Returns the list of records to export.
	 *
	 * @return 	array 	A list of records.
	 */
	protected function getRecords()
	{
		$dispatcher = VREFactory::getEventDispatcher();

		$dbo = JFactory::getDbo();

		$currency = VREFactory::getCurrency();

		$q = $dbo->getQuery(true);

		// select all reservation columns
		$q->select('r.*');
		$q->select($dbo->qn('gp.name', 'payment_name'));

		if ($this->isGroup('restaurant'))
		{
			// load restaurant reservations
			$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));

			// get table details
			$q->select($dbo->qn('t.name', 'table_name'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_table', 't') . ' ON ' . $dbo->qn('r.id_table') . ' = ' . $dbo->qn('t.id'));

			// get room details
			$q->select($dbo->qn('rm.name', 'room_name'));
			$q->leftjoin($dbo->qn('#__vikrestaurants_room', 'rm') . ' ON ' . $dbo->qn('t.id_room') . ' = ' . $dbo->qn('rm.id'));

			// check if the items should be loaded
			if ($this->getOption('useitems'))
			{
				// get item details
				$q->select($dbo->qn('i.name', 'item_name'));
				$q->select($dbo->qn('i.quantity', 'item_quantity'));
				$q->select($dbo->qn('i.price', 'item_price'));
				$q->leftjoin($dbo->qn('#__vikrestaurants_res_prod_assoc', 'i') . ' ON ' . $dbo->qn('i.id_reservation') . ' = ' . $dbo->qn('r.id'));
			}

			// DO NOT take closures
			$q->where($dbo->qn('r.closure') . ' = 0');

			// DO NOT take children reservations
			$q->where($dbo->qn('r.id_parent') . ' = 0');

			$cluster = $dbo->getQuery(true)
				->select('GROUP_CONCAT(' . $dbo->qn('ti.name') . ')')
				->from($dbo->qn('#__vikrestaurants_reservation', 'ri'))
				->leftjoin($dbo->qn('#__vikrestaurants_table', 'ti') . ' ON ' . $dbo->qn('ri.id_table') . ' = ' . $dbo->qn('ti.id'))
				->where($dbo->qn('ri.id_parent') . ' = ' . $dbo->qn('r.id'));

			// recover all assigned tables
			$q->select('(' . $cluster . ') AS ' . $dbo->qn('cluster'));
		}
		else
		{
			// load takeaway orders
			$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'));

			// check if the items should be loaded
			if ($this->getOption('useitems'))
			{
				// get item details
				$q->select(sprintf(
					'IF(%2$s IS NOT NULL, CONCAT_WS(\' - \', %1$s, %2$s), %1$s) AS %3$s',
					$dbo->qn('e.name'),
					$dbo->qn('o.name'),
					$dbo->qn('item_name')
				));
				$q->select($dbo->qn('i.quantity', 'item_quantity'));
				$q->select($dbo->qn('i.price', 'item_price'));
				$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_res_prod_assoc', 'i') . ' ON ' . $dbo->qn('i.id_res') . ' = ' . $dbo->qn('r.id'));
				$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry', 'e') . ' ON ' . $dbo->qn('i.id_product') . ' = ' . $dbo->qn('e.id'));
				$q->leftjoin($dbo->qn('#__vikrestaurants_takeaway_menus_entry_option', 'o') . ' ON ' . $dbo->qn('i.id_product_option') . ' = ' . $dbo->qn('o.id'));
			}
		}

		/**
		 * Added support for payment name.
		 *
		 * @since 1.8.5
		 */
		$q->leftjoin($dbo->qn('#__vikrestaurants_gpayments', 'gp') . ' ON ' . $dbo->qn('r.id_payment') . ' = ' . $dbo->qn('gp.id'));

		if ($this->getOption('confirmed'))
		{
			// take only CONFIRMED records
			$q->where($dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'));
		}

		// include records with checkin equals or higher than 
		// the specified starting date
		$from = $this->getOption('fromdate');

		if ($from && $from !== $dbo->getNullDate())
		{
			$q->where($dbo->qn('r.checkin_ts') . ' >= ' . VikRestaurants::createTimestamp($from, 0, 0));
		}

		// include records with checkin equals or lower than 
		// the specified ending date
		$to = $this->getOption('todate');

		if ($to && $to !== $dbo->getNullDate())
		{
			$q->where($dbo->qn('r.checkin_ts') . ' <= ' . VikRestaurants::createTimestamp($to, 23, 59));
		}

		// retrieve only the selected records, if any
		$ids = $this->getOption('cid');

		if ($ids)
		{
			$q->where($dbo->qn('r.id') . ' IN (' . implode(',', array_map('intval', $ids)) . ')');
		}

		// order by ascending checkin
		$q->order($dbo->qn('r.checkin_ts') . ' ASC');

		/**
		 * Trigger event to allow the plugins to manipulate the query used to retrieve
		 * a standard list of records.
		 *
		 * @param 	mixed  &$query 	 The query string or a query builder object.
		 * @param 	mixed  $options  A configuration registry.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.5
		 */
		$dispatcher->trigger('onBeforeListQueryExportCSV', array(&$q, $this->options));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			return array();
		}

		$rows = array();

		foreach ($dbo->loadObjectList() as $obj)
		{
			if (!isset($rows[$obj->id]))
			{
				$rows[$obj->id] = $obj;

				$rows[$obj->id]->items = array();
			}

			// group reservation items
			if (!empty($obj->item_name))
			{
				$rows[$obj->id]->items[] = $obj->item_name . "\tx" . $obj->item_quantity . "\t(" . $currency->format($obj->item_price) . ")";
			}
		}

		/**
		 * Trigger event to allow the plugins to manipulate response fetched by
		 * the query used to retrieve a standard list of records.
		 *
		 * @param 	mixed  &$rows 	 An array of results (objects).
		 * @param 	mixed  $options  A configuration registry.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.5
		 */
		$dispatcher->trigger('onAfterListQueryExportCSV', array(&$rows, $this->options));

		return array_values($rows);
	}

	/**
	 * Inserts the row within the CSV file.
	 *
	 * @param 	mixed 	$handle     The resource pointer created with fopen().
	 * @param 	array   $row        The row to include.
	 * @param 	mixed   $delimiter  The delimiter used to separate the columns
	 * @param 	mixed   $enclosure  The enclosure used to wrap the values.
	 * 
	 * @return 	void
	 *
	 * @since   1.8.5
	 */
	protected function putRow($handle, $row, $delimiter = null, $enclosure = null)
	{
		fputcsv($handle, $row, $delimiter, $enclosure);
	}
}
