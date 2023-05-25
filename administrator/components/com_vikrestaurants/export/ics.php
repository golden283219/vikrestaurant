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
 * ICS reservations exporter.
 *
 * @since 1.3
 *
 * @deprecated 1.9 	Use VREOrderExportDriverIcs instead.
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
	 * Returns the generated ICS string.
	 *
	 * @param 	integer  $type
	 *
	 * @return 	string
	 */
	public function getString($type = 0)
	{
		VRELoader::import('library.order.export.factory');

		// get ICS export driver
		$driver = VREOrderExportFactory::getInstance('ics', $type == 0 ? 'restaurant' : 'takeaway', $this->options);

		// export string
		return $driver->export();
	}
	
	/**
	 * Downloads the specified ICS string.
	 *
	 * @param 	string 	$ics
	 * @param 	string 	$filename
	 *
	 * @return 	void
	 */
	public function export($ics = '', $filename = '')
	{
		header("Content-Type: application/octet-stream; "); 
		header("Content-Disposition: attachment; filename=$filename");
		header("Cache-Control: no-store, no-cache");
		
		$f = fopen('php://output', "w");
		fwrite($f, $ics);
		fclose($f);
		
		exit;
	}

	/**
	 * Echoes the specified ICS string.
	 *
	 * @param 	string 	$ics
	 * @param 	string 	$filename
	 *
	 * @return 	void
	 */
	public function renderBrowser($ics = '', $filename = '')
	{
		header("Content-Type: text/calendar; charset=utf-8");
		header("Content-Disposition: attachment; filename=$filename");

		echo $ics;
	}
}
