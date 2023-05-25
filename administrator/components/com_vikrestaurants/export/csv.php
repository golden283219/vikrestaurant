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
 * CSV reservations exporter.
 *
 * @since 1.3
 *
 * @deprecated 1.9 	Use VREOrderExportDriverCsv instead.
 */
class VikExporter
{	
	/**
	 * An array of options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Class constructor.
	 *
	 * @param 	integer  $from_ts
	 * @param 	integer  $to_ts
	 * @param 	array 	 $ids
	 */
	public function __construct($from_ts, $to_ts, $ids = array())
	{
		$config = VREFactory::getConfig();

		if (is_int($from_ts))
		{
			$from_ts = date($config->get('dateformat'), $from_ts);
		}

		if (is_int($to_ts))
		{
			$to_ts = date($config->get('dateformat'), $to_ts);
		}

		$this->options = array(
			'fromdate' => $from_ts,
			'todate'   => $to_ts,
			'cid'      => (array) $ids,
		);
	}
	
	/**
	 * Returns an empty array.
	 * Let the export function do the work.
	 *
	 * @param 	integer  $type
	 *
	 * @return 	array
	 */
	public function getString($type = 0)
	{
		$this->type = $type == 0 ? 'restaurant' : 'takeaway';

		return array();
	}
	
	/**
	 * Downloads a CSV file.
	 *
	 * @param 	array 	$csv (unused)
	 * @param 	string 	$filename
	 */
	public function export($csv = array(), $filename = '')
	{
		VRELoader::import('library.order.export.factory');

		// get CSV export driver
		$driver = VREOrderExportFactory::getInstance('csv', $this->type, $this->options);

		// download CSV
		return $driver->download($filename);
	}
}
