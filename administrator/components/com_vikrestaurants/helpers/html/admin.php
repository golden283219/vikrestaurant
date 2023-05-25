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
 * VikRestaurants HTML admin helper.
 *
 * @since 1.8
 */
abstract class VREHtmlAdmin
{
	/**
	 * Returns an array of working shifts.
	 *
	 * @param 	mixed 	$shifts  Either a list of shifts or the parent group.
	 * 							 If empty, all the groups will be considered.
	 * @param 	string  $pk      The value to use in the option ('id' or 'interval').
	 *
	 * @return 	array 	A list of shifts.
	 */
	public static function shifts($shifts = null, $pk = 'id')
	{
		if (!is_array($shifts))
		{
			// check if we passed the shifts group
			if (preg_match("/^[\d]$/", $shifts))
			{
				// obtain working shifts by group
				$shifts = VikRestaurants::getWorkingShifts($shifts);
			}
			else if (is_null($shifts))
			{
				// recover working shifts for default group if not specified
				$shifts = VikRestaurants::getWorkingShifts();
			}
		}

		$options = array();

		// iterate working shifts
		foreach ($shifts as $shift)
		{
			// always treat the record as an object
			$shift = (object) $shift;

			if ($pk == 'id')
			{
				// use the working shift record ID
				$value = $shift->id;
			}
			else
			{
				// use the opening interval as option value
				$value = $shift->from . '-' . $shift->to;
			}

			// add option value
			$options[] = JHtml::_('select.option', $value, $shift->name);
		}

		return $options;
	}

	/**
	 * Returns an array of working shifts for the given day.
	 *
	 * @param 	integer  $group  The group to which the shifts belong.
	 * @param 	mixed 	 $day    The day to look for. If not specified,
	 * 					 	     the current day will be used.
	 * @param 	string   $pk     The value to use in the option ('id' or 'interval').
	 *
	 * @return 	array 	 A list of shifts.
	 */
	public static function dayshifts($group, $day = null, $pk = 'id')
	{
		// extract timestamp from day
		if (is_null($day))
		{
			// use current date
			$day = VikRestaurants::now();
		}
		
		// convert to date string if UNIX timestamp
		if (is_numeric($day))
		{
			$day = date(VREFactory::getConfig()->get('dateformat'), $day);
		}

		// get shifts for specified day
		$shifts = JHtml::_('vikrestaurants.shifts', $group, $day, false);

		foreach ($shifts as &$shift)
		{
			// cast shift to array
			$shift = (array) $shift;

			// use label as option text if specified
			if ($shift['showlabel'])
			{
				$shift['name'] = $shift['label'];
			}
		}

		// create a list of working shifts
		return self::shifts($shifts, $pk);
	}

	/**
	 * Returns a list of supported groups.
	 *
	 * @param 	array    $values       A list of supported values.
	 * @param 	boolean  $allowClear   True to include an empty option.
	 * @param 	string   $placeholder  A specific text to use for the empty option.
	 *
	 * @return 	array    A list of dropdown options.
	 */
	public static function groups($values = null, $allowClear = false, $placeholder = null)
	{
		if ($placeholder === null)
		{
			$placeholder = 'VRE_FILTER_SELECT_GROUP';
		}

		if ($values === null || !is_array($values) || count($values) != 2)
		{
			$values = array(0, 1);
		}

		$options = array();

		if ($allowClear)
		{
			$options[] = JHtml::_('select.option', '', $placeholder);
		}

		$rs_enabled = VikRestaurants::isRestaurantEnabled();
		$tk_enabled = VikRestaurants::isTakeAwayEnabled();

		if (!$rs_enabled && !$tk_enabled)
		{
			// do not proceed in case both the sections are turned off
			return $options;
		}

		if ($rs_enabled)
		{
			// append restaurant option, if enabled
			$options[] = JHtml::_('select.option', $values[0], 'VRMANAGECONFIGTITLE1');
		}

		if ($tk_enabled)
		{
			// append take-away option, if enabled
			$options[] = JHtml::_('select.option', $values[1], 'VRMANAGECONFIGTITLE2');
		}

		return $options;
	}

	/**
	 * Returns the default group in case the specified on is not supported.
	 *
	 * @param 	mixed 	 $group       The group value to check. 
	 * @param 	array    $values      A list of supported values.
	 * @param 	boolean  $allowClear  True to include an empty option.
	 *
	 * @return 	mixed    The group identifier.
	 */
	public static function getgroup($group = null, $values = null, $allowClear = false)
	{
		if ((is_null($group) || $group == '') && $allowClear)
		{
			// return null in case no group was specified and the
			// dropdown supports empty values
			return null;
		}

		if ($values === null || !is_array($values))
		{
			$values = array(0, 1);
		}

		if (!VikRestaurants::isRestaurantEnabled())
		{
			// remove restaurant value from list (first)
			array_shift($values);
		}

		if (!VikRestaurants::isTakeAwayEnabled())
		{
			// remove take-away value from list (last)
			array_pop($values);
		}

		if (in_array($group, $values))
		{
			// the group is supported, return it directly
			return $group;
		}

		if (!$allowClear)
		{
			// return the first available group in case it
			// is mandatory to have an active value
			return array_shift($values);
		}

		// fallback to empty placeholder
		return null;
	}

	/**
	 * Returns a list of countries.
	 *
	 * @return 	array 	 The countries list.
	 */
	public static function countries()
	{
		static $countries = null;

		// load countries only once
		if (!$countries)
		{
			$dbo = JFactory::getDbo();

			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_countries'))
				->where($dbo->qn('published') . ' = 1')
				->order($dbo->qn('country_name') . ' ASC');

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$countries = $dbo->loadObjectList();
			}
			else
			{
				$countries = array();
			}
		}

		$options = array();

		foreach ($countries as $country)
		{
			$options[] = JHtml::_('select.option', $country->country_2_code, $country->country_name);
		}

		return $options;
	}

	/**
	 * Returns a list of published tables, grouped by room.
	 *
	 * @param 	boolean  $blank  True to include an empty option.
	 *
	 * @return 	array 	 A list of tables.
	 */
	public static function tables($blank = false)
	{
		$options = array();

		if ($blank)
		{
			$options[0] = array(JHtml::_('select.option', '', JText::_('VRE_FILTER_SELECT_TABLE')));
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('t.id'))
			->select($dbo->qn('t.name'))
			->select($dbo->qn('r.name', 'rname'))
			->from($dbo->qn('#__vikrestaurants_table', 't'))
			->leftjoin($dbo->qn('#__vikrestaurants_room', 'r') . ' ON ' . $dbo->qn('r.id') . ' = ' . $dbo->qn('t.id_room'))
			->where($dbo->qn('r.published') . ' = 1')
			->where($dbo->qn('t.published') . ' = 1')
			->order($dbo->qn('r.ordering') . ' ASC')
			->order($dbo->qn('t.name') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $t)
			{
				if (!isset($options[$t->rname]))
				{
					$options[$t->rname] = array();
				}

				$options[$t->rname][] = JHtml::_('select.option', $t->id, $t->name);
			}
		}

		return $options;
	}

	/**
	 * Returns a list of supported reservation codes rules..
	 *
	 * @param 	mixed  $blank  True to include an empty option. In case of a
	 * 						   string, it will be used as placeholder.
	 *
	 * @return 	array  A list of rules.
	 */
	public static function rescodesrules($blank = false)
	{
		VRELoader::import('library.rescodes.handler');

		$options = array();

		if ($blank !== false)
		{
			if ($blank === true)
			{
				// use default placeholder
				$blank = JText::_('VRE_FILTER_SELECT_RULE');
			}

			// include empty option
			$options[0] = array(JHtml::_('select.option', '', $blank));
		}

		// define optgroup/driver section lookup
		$lookup = array(
			'restaurant',
			'takeaway',
			'food',
		);

		foreach ($lookup as $group)
		{
			// get rules supported globally
			$drivers = ResCodesHandler::getSupportedRules($group);

			if ($drivers)
			{
				// add global drivers
				$options[$group] = array();

				// iterate drivers to build dropdown options
				foreach ($drivers as $d)
				{
					$options[$group][] = JHtml::_('select.option', $d->getID(), $d->getName());
				}
			}
		}

		return $options;
	}

	/**
	 * Returns a list of supported payment gateways.
	 *
	 * @param 	boolean  $blank  True to include an empty option.
	 *
	 * @return 	array 	 A list of drivers.
	 */
	public static function paymentdrivers($blank = false)
	{
		// get payment drivers
		$files = VREApplication::getInstance()->getPaymentDrivers();

		$options = array();

		if ($blank)
		{
			$options[] = JHtml::_('select.option', '', JText::_('VRE_FILTER_SELECT_DRIVER'));
		}

		foreach ($files as $file)
		{
			// get file name
			$value = basename($file);
			// strip file extension
			$text = preg_replace("/\.php$/", '', $value);

			$options[] = JHtml::_('select.option', $value, $text);
		}

		return $options;
	}

	/**
	 * Returns a list of supported SMS providers.
	 *
	 * @param 	boolean  $blank  True to include an empty option.
	 *
	 * @return 	array 	 A list of drivers.
	 */
	public static function smsdrivers($blank = false)
	{
		// get SMS drivers
		$files = VREApplication::getInstance()->getSmsDrivers();

		$options = array();

		if ($blank)
		{
			$options[] = JHtml::_('select.option', '', JText::_('VRE_FILTER_SELECT_DRIVER'));
		}

		foreach ($files as $file)
		{
			// get file name
			$value = basename($file);
			// strip file extension
			$text = preg_replace("/\.php$/", '', $value);

			$options[] = JHtml::_('select.option', $value, $text);
		}

		return $options;
	}

	/**
	 * Returns a list of supported API logins.
	 *
	 * @param 	boolean  $blank  True to include an empty option.
	 *
	 * @return 	array 	 A list of logins.
	 */
	public static function apilogins($blank = false)
	{
		$options = array();

		if ($blank)
		{
			$options[] = JHtml::_('select.option', '', JText::_('VRE_FILTER_SELECT_APPLICATION'));
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('l.application'))
			->select($dbo->qn('l.username'))
			->select($dbo->qn('l.password'))
			->from($dbo->qn('#__vikrestaurants_api_login', 'l'))
			->where($dbo->qn('l.active') . ' = 1');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $r)
			{
				if ($r->application)
				{
					$text = $r->application . ' : ' . $r->username;
				}
				else
				{
					$text = $r->username;
				}

				$value = $r->username . ';' . $r->password;

				$options[] = JHtml::_('select.option', $value, $text);
			}
		}

		return $options;
	}

	/**
	 * Returns the HTML of the handle used to rearrange the table rows.
	 * FontAwesome is required in order to display the handle icon.
	 *
	 * @param 	integer   $ordering  The ordering value.
	 * @param 	boolean   $canEdit   True if the user is allowed to edit the ordering.
	 * @param 	boolean   $canOrder  True if the table is currently sorted by "ordering" column.
	 * @param 	boolean   $input     True if the ordering input should be included in the body.
	 *
	 * @return 	string 	  The HTML of the handle.
	 */
	public static function sorthandle($ordering, $canEdit = true, $canOrder = true, $input = true)
	{
		$icon_class = $icon_title = '';

		if (!$canEdit)
		{
			$icon_class = ' inactive';
		}
		else if (!$canOrder)
		{
			$icon_class = ' inactive tip-top hasTooltip';
			$icon_title = JText::_('JORDERINGDISABLED');
		}

		$html = '<span class="sortable-handler' . $icon_class . '" title="' . $icon_title . '">
			<i class="fas fa-ellipsis-v medium-big" aria-hidden="true"></i>
		</span>';

		if ($canEdit && $canOrder && $input)
		{
			$html .= '<input type="hidden" name="order[]" value="' . $ordering . '" />';
		}

		return $html;
	}
}
