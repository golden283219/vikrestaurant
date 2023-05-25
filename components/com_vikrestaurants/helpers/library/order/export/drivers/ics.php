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
 * restaurant reservations in ICS format.
 *
 * @since 1.8
 */
class VREOrderExportDriverIcs extends VREOrderExportDriver
{
	/**
	 * ICS declarations buffer.
	 *
	 * @var string
	 */
	private $ics;

	/**
	 * @override
	 * Returns the form parameters required to the ICS driver.
	 *
	 * @return 	array
	 */
	public function getForm()
	{
		return array(
			/**
			 * An optional subject to be used instead of the
			 * default one.
			 *
			 * @var text
			 */
			'subject' => array(
				'type'    => 'text',
				'label'   => JText::_('VRE_EXPORT_DRIVER_ICS_SUBJECT_FIELD'),
				'help'    => JText::_('VRE_EXPORT_DRIVER_ICS_SUBJECT_FIELD_HELP'),
			),

			/**
			 * Include past events.
			 * If disabled, reservations older than the current
			 * month won't have to be included.
			 *
			 * @var checkbox
			 */
			'pastevents' => array(
				'type'    => 'checkbox',
				'label'   => JText::_('VRE_EXPORT_DRIVER_ICS_PAST_DATES_FIELD'),
				'help'    => JText::_('VRE_EXPORT_DRIVER_ICS_PAST_DATES_FIELD_HELP'),
				'default' => true,
			),

			/**
			 * Events default reminder.
			 * The minutes in advance since the event date time
			 * for which the alert will be triggered.
			 *
			 * @var select
			 */
			'reminder' => array(
				'type'    => 'select',
				'label'   => JText::_('VRE_EXPORT_DRIVER_ICS_REMINDER_FIELD'),
				'help'    => JText::_('VRE_EXPORT_DRIVER_ICS_REMINDER_FIELD_HELP'),
				'default' => -1,
				'options' => array(
					-1  => JText::_('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_NONE'),
					0   => JText::_('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_EVENT_TIME'),
					5   => JText::sprintf('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_N_MIN', 5),
					10  => JText::sprintf('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_N_MIN', 10),
					15  => JText::sprintf('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_N_MIN', 15),
					30  => JText::sprintf('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_N_MIN', 30),
					60  => JText::plural('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_N_HOURS', 1),
					120 => JText::plural('VRE_EXPORT_DRIVER_ICS_REMINDER_OPT_N_HOURS', 2),
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
		// init buffer
		$this->ics = '';

		/**
		 * Starts the calendar declaration.
		 * 
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-4-icalendar-object.html
		 */
		$this->addLine('BEGIN', 'VCALENDAR');

		// create ICS header
		$this->createHeader();

		// iterate records to export
		foreach ($this->getRecords() as $event)
		{
			// use registry for ease of use
			$event = new JRegistry($event);

			// add event properties
			$this->addEvent($event);
		}

		/**
		 * Closes the calendar
		 *
		 * @see BEGIN:VCALENDAR
		 */
		$this->addLine('END', 'VCALENDAR');

		// return generated buffer
		return $this->ics;
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
		// obtain export string
		$buffer = $this->export();

		if ($filename)
		{
			// strip file extension
			$filename = preg_replace("/\.ics$/i", '', $filename);
		}
		else
		{
			// use current date time as name
			$filename = date('Y-m-d H_i_s', VikRestaurants::now());
		}

		// declare headers
		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename=' . $filename . '.ics');
		header('Content-Length: ' . strlen($buffer));
		header('Cache-Control: no-store, no-cache');
		
		// output buffer for download
		echo $buffer;
	}

	/**
	 * Returns the list of records to export.
	 *
	 * @return 	array 	A list of records.
	 */
	protected function getRecords()
	{
		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true);

		// select all reservation columns
		$q->select('r.*');

		if ($this->isGroup('restaurant'))
		{
			// load restaurant reservations
			$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));

			// exclude all children reservations (clusters)
			$q->where($dbo->qn('r.id_parent') . ' = 0');
			// exclude all closures
			$q->where($dbo->qn('r.closure') . ' = 0');
		}
		else
		{
			// load takeaway orders
			$q->from($dbo->qn('#__vikrestaurants_takeaway_reservation', 'r'));
		}

		// take only CONFIRMED records
		$q->where($dbo->qn('r.status') . ' = ' . $dbo->q('CONFIRMED'));

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

		// check whether the past events should be excluded
		if (!$this->getOption('pastevents'))
		{
			// get current datetime
			$now = getdate();

			// set the threshold at the beginning of this month
			$threshold = mktime(0, 0, 0, $now['mon'], 1, $now['year']);

			$q->where($dbo->qn('r.checkin_ts') . ' >= ' . $threshold);
		}

		// order by ascending checkin
		$q->order($dbo->qn('r.checkin_ts') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows() == 0)
		{
			return array();
		}

		return $dbo->loadObjectList();
	}

	/**
	 * Creates the header of the calendar.
	 *
	 * @return 	void
	 */
	protected function createHeader()
	{
		/**
		 * This property specifies the identifier corresponding to the highest version number
		 * or the minimum and maximum range of the iCalendar specification that is required
		 * in order to interpret the iCalendar object.
		 * 
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-7-4-version.html
		 */
		$this->addLine('VERSION', '2.0');

		/**
		 * This property specifies the identifier for the product that created the iCalendar object.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-7-3-product-identifier.html
		 */
		$this->addLine('PRODID', '-//e4j//VikRestaurants ' . VIKRESTAURANTS_SOFTWARE_VERSION . '//EN');

		/**
		 * This property defines the calendar scale used for the calendar information
		 * specified in the iCalendar object.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-7-1-calendar-scale.html
		 */
		$this->addLine('CALSCALE', 'GREGORIAN');

		// fetch calendar name, built as "restaurant - group"
		$calname = VREFactory::getConfig()->get('restname') . ' - ';

		if ($this->isGroup('restaurant'))
		{
			// restaurant group
			$calname .= JText::_('VRMENUTITLEHEADER1');
		}
		else
		{
			// take-away group
			$calname .= JText::_('VRMENUTITLEHEADER5');
		}

		/**
		 * This non standard property defines the default name that will be used
		 * when creating a new subscription.
		 */
		$this->addLine('X-WR-CALNAME', $calname);

		// $this->addLine('X-WR-TIMEZONE', JFactory::getApplication()->get('offset', 'UTC'));
	}

	/**
	 * Adds a reservation as event within the calendar.
	 *
	 * @param 	JRegistry 	$event  The event to include.
	 *
	 * @return 	void
	 */
	protected function addEvent($event)
	{
		$config = VREFactory::getConfig();
		$vik    = VREApplication::getInstance();

		/**
		 * Provide a grouping of component properties that describe an event.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-6-1-event-component.html
		 */
		$this->addLine('BEGIN', 'VEVENT');

		/**
		 * This property specifies the persistent, globally unique identifier for the
		 * iCalendar object. This can be used, for example, to identify duplicate calendar
		 * streams that a client may have been given access to.
		 *
		 * Generate a md5 string of the order number because "UID" values MUST NOT include any 
		 * data that might identify a user, host, domain, or any other private sensitive information.
		 *
		 * @link https://icalendar.org/New-Properties-for-iCalendar-RFC-7986/5-3-uid-property.html
		 */
		$this->addLine('UID', md5($event->get('id') . '-' . $event->get('sid')));

		/**
		 * This property specifies when the calendar component begins.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-2-4-date-time-start.html
		 */
		$this->addLine(
			array('DTSTART', 'VALUE=DATE-TIME'),
			$this->tsToCal($event->get('checkin_ts'))
		);

		// fetch checkout
		if ($this->isGroup('restaurant'))
		{
			if (!$event->get('stay_time'))
			{
				// use average time of stay
				$event->set('stay_time', $config->get('averagetimestay'));
			}

			$checkout = strtotime('+' . $event->get('stay_time') . ' minutes', $event->get('checkin_ts'));	
		}
		else
		{
			$checkout = strtotime('+' . $config->getUint('tkminint') . ' minutes', $event->get('checkin_ts'));
		}

		$event->set('checkout_ts', $checkout);

		/**
		 * This property specifies the date and time that a calendar component ends.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-2-2-date-time-end.html
		 */
		$this->addLine(
			array('DTEND', 'VALUE=DATE-TIME'),
			$this->tsToCal($event->get('checkout_ts'))
		);

		/**
		 * In the case of an iCalendar object that specifies a "METHOD" property, this property
		 * specifies the date and time that the instance of the iCalendar object was created.
		 * In the case of an iCalendar object that doesn't specify a "METHOD" property, this
		 * property specifies the date and time that the information associated with the calendar
		 * component was last revised in the calendar store.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-7-2-date-time-stamp.html
		 */
		$this->addLine('DTSTAMP', $this->tsToCal($event->get('created_on')));

		/**
		 * In case an event is modified through a client, it updates the Last-Modified property to the
		 * current time. When the calendar is going to refresh an event, in case the Last-Modified is
		 * not specified or it is lower than the current one, the changes will be discarded.
		 * For this reason, it is needed to specify our internal modified date in order to refresh
		 * any existing events with the updated details.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-7-3-last-modified.html
		 */
		$modified = max(array($event->get('created_on'), $event->get('modified_on')));
		$this->addLine('LAST-MODIFIED', $this->tsToCal($modified));

		// fetch URI
		if ($this->isGroup('restaurant'))
		{
			$uri = 'index.php?option=com_vikrestaurants&view=reservation&ordnum=' . $event->get('id') . '&ordkey=' . $event->get('sid');
		}
		else
		{
			$uri = 'index.php?option=com_vikrestaurants&view=order&ordnum=' . $event->get('id') . '&ordkey=' . $event->get('sid');
		}

		$uri = $vik->routeForExternalUse($uri);

		/**
		 * This property may be used to convey a location where a more dynamic
		 * rendition of the calendar information can be found.
		 *
		 * @link https://icalendar.org/New-Properties-for-iCalendar-RFC-7986/5-5-url-property.html
		 */
		$this->addLine(array('URL', 'VALUE=URI'), $uri);

		// fetch summary
		$summary = $this->getOption('subject');

		// retrieve customer name
		$customer = $event->get('purchaser_nominative');

		if (!$customer)
		{
			// use e-mail in case the name is missing
			$customer = $event->get('purchaser_mail');
		}

		if (!$customer)
		{
			// fallback to "Guest"
			$customer = JText::_('VRMANAGECUSTOMER15');
		}

		// retrieve service name
		$service = JText::_($event->get('delivery_service') ? 'VRMANAGETKRES14' : 'VRMANAGETKRES15');

		// retrieve people
		$people = (int) $event->get('people', 0);

		if ($summary)
		{
			// Use summary defined by the customer.
			// Replace tags with reservation values.
			$summary = preg_replace("/{customer}/", $customer, $summary);
			$summary = preg_replace("/{service}/", $service, $summary);
			$summary = preg_replace("/{people}/", $people, $summary);
		}
		else
		{
			// use default summary
			if ($this->isGroup('restaurant'))
			{
				// Reservation for N people
				$summary = JText::sprintf('VREXPORTSUMMARY', $people);
			}
			else
			{
				// Take-Away Order for CUSTOMER
				$summary = JText::sprintf('VRTKEXPORTSUMMARY', $customer);
			}
		}

		/**
		 * This property defines a short summary or subject for the calendar component.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-1-12-summary.html
		 */
		$this->addLine('SUMMARY', $this->escape($summary));

		// fetch description
		$description = "";
		
		foreach ((array) json_decode($event->get('custom_f', true)) as $k => $v)
		{
			if (!empty($v))
			{
				$description .= JText::_($k) . ': ' . $v . "\\n";
			}
		}
		
		/**
		 * This property provides a more complete description of the calendar component
		 * than that provided by the "SUMMARY" property.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-1-5-description.html
		 */
		if ($description)
		{
			$this->addLine('DESCRIPTION', $this->escape($description));
		}

		/**
		 * This property defines the intended venue for the activity defined by a calendar component.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-1-7-location.html
		 */
		$this->addLine('LOCATION', $this->escape($config->get('restname')));

		/**
		 * This property defines whether or not an event is transparent to busy time searches.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-2-7-time-transparency.html
		 */
		$this->addLine('TRANSP', 'OPAQUE');

		// check if a reminder should be included
		$reminder = (int) $this->getOption('reminder');

		if ($reminder >= 0)
		{
			// create event alarm
			$this->createAlarm($event, $reminder);
		}

		/**
		 * Closes the event properties.
		 *
		 * @see BEGIN:VEVENT
		 */
		$this->addLine('END', 'VEVENT');
	}

	/**
	 * Creates an alarm for the specified event.
	 *
	 * @param 	JRegistry  $event     The event to bind.
	 * @param 	integer    $reminder  The reminder in minutes.
	 *
	 * @return 	void
	 */
	protected function createAlarm($event, $reminder = 0)
	{
		/**
		 * Provide a grouping of component properties that define an alarm.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-6-6-alarm-component.html
		 */
		$this->addLine('BEGIN', 'VALARM');

		/**
		 * This property specifies a positive duration of time.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-2-5-duration.html
		 */
		if ($reminder == 0)
		{
			// trigger alert at event time
			$duration = '-PT0S';
		}
		else if ($reminder < 60)
		{
			// trigger alert X minutes in advance
			$duration = '-PT' . $reminder . 'M';
		}
		else
		{
			// trigger alert X hours in advance
			$duration = '-PT' . floor($reminder / 60) . 'H' . ($reminder % 60) . 'M';
		}

		/**
		 * This property specifies when an alarm will trigger.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-6-3-trigger.html
		 */
		$this->addLine(array('TRIGGER', 'RELATED=START'), $duration);

		/**
		 * This property defines the action to be invoked when an alarm is triggered.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-6-1-action.html
		 */
		$this->addLine('ACTION', 'DISPLAY');

		/**
		 * In a DISPLAY alarm, the intended alarm effect is for the text value of
		 * the "DESCRIPTION" property to be displayed to the user.
		 *
		 * @link https://icalendar.org/iCalendar-RFC-5545/3-8-1-5-description.html
		 */
		$this->addLine('DESCRIPTION', JText::_('VRE_EXPORT_DRIVER_ICS_REMINDER_FIELD'));
		
		/**
		 * Closes the alarm properties.
		 *
		 * @see BEGIN:VALARM
		 */
		$this->addLine('END', 'VALARM');
	}

	/**
	 * Adds a line within the ICS buffer by caring of
	 * the iCalendar standards.
	 *
	 * @param 	mixed  $rule     Either the rule command or an array of commands to be concatenated (;).
	 * @param 	mixed  $content  Either the rule content or an array of contents to be concatenated (;).
	 *
	 * @return 	self   This object to support chaining.
	 */
	protected function addLine($rule, $content = null)
	{
		// concat rules in case of array
		if (is_array($rule))
		{
			// rule with multiple parts, use semi-colon
			$rule = implode(';', $rule);
		}

		// concat contents in case of array
		if (is_array($content))
		{
			// multi-contents list, use comma
			$content = implode(',', $content);
		}

		// create line
		if (is_null($content))
		{
			// we had the full line within the rule
			$line = $rule;
		}
		else
		{
			// merge rule and content
			$line = $rule . ':' . $content;
		}

		// split string every 73 characters (reserve 2 chars to include new line and space)
		$chunks = str_split($line, 73);

		// merge lines togheter by using indentation technique,
		// then add the line to the buffer
		$this->ics .= implode("\n ", $chunks) . "\n";

		return $this;
	}

	/**
	 * Converts a UNIX timestamp to a valid ICS date string.
	 *
	 * @param 	integer  $ts  The timestamp to convert.
	 *
	 * @return 	string 	 The formatted date.
	 */
	protected function tsToCal($ts)
	{
		return JDate::getInstance($ts)->format('Ymd\THis\Z');
	}

	/**
	 * Escapes a line value.
	 *
	 * @param 	string 	$str  The string to escape.
	 *
	 * @return 	string 	The escaped string.
	 */
	protected function escape($str)
	{
		// escape reserved characters
		return preg_replace('/([\,;])/','\\\$1', $str);
	}
}
