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
 * VikRestaurants HTML site scripts helper.
 *
 * @since 1.8
 */
abstract class VREHtmlSitescripts
{
	/**
	 * Declares the function that will be used to update the working shifts
	 * dropdown when the related datepicker changes value.
	 *
	 * @param 	integer  $group     The component section (1 restaurant, 2 take-away).
	 * @param 	string   $funcname  The function name. If not specified, the default
	 * 								'vrUpdateWorkingShifts' function will be used.
	 *
	 * @return 	void
	 */
	public static function updateshifts($group = 1, $funcname = '')
	{
		$group = (int) $group;

		if (!$funcname)
		{
			$funcname = 'vrUpdateWorkingShifts';
		}

		if (VikRestaurants::isContinuosOpeningTime())
		{
			// We don't need to refresh the select in case of
			// contiguous opening time.
			// Create function placeholder just to avoid errors and
			// trigger success callback for global compatibility.
			$js = 
<<<JS
function {$funcname}(datepicker, selector, success, error) {
	if (typeof success === 'function') {
		success(true);
	}
}
JS
			;
		}
		else
		{
			$app = JFactory::getApplication();

			// plain URL for back-end
			$url = 'index.php?option=com_vikrestaurants&task=get_working_shifts&tmpl=component';

			if ($app->isClient('site'))
			{
				$itemid = $app->input->get('Itemid', null, 'uint');

				$vik = VREApplication::getInstance();

				// route URL for front-end
				$url = $vik->ajaxUrl($url . ($itemid ? '&Itemid=' . $itemid : ''), false);
			}

			$js = 
<<<JS
function {$funcname}(datepicker, selector, success, error) {
	jQuery(selector).prop('disabled', true);
	
	jQuery.noConflict();

	var hourmin = jQuery(selector).val();

	UIAjax.do(
		'{$url}',
		{
			date:    jQuery(datepicker).val(),
			hourmin: jQuery(selector).val(),
			group:   {$group},
		},
		function(obj) {
			try {
				obj = JSON.parse(obj);
			} catch (err) {
				console.warn(err);
				obj = [false];
			}

			jQuery(selector).prop('disabled', false);
		
			if (obj[0] && obj[1].length > 0)
			{
				jQuery(selector).html(obj[1]);

				if (jQuery.fn.select2) {
					// update select2 only in case the select owns that value
					if (jQuery(selector).find('option[value="' + hourmin + '"]').length) {
						jQuery(selector).select2('val', hourmin);
					}
				} else if (jQuery.fn.chosen) {
					// destroy chosen
					jQuery(selector).chosen('destroy');
					// render select again
					VikRenderer.chosen(selector);

					// update chosen only in case the select owns that value
					if (jQuery(selector).find('option[value="' + hourmin + '"]').length) {
						jQuery(selector).updateChosen(hourmin);
					}
				} else {
					// update standard select only in case it owns that value
					if (jQuery(selector).find('option[value="' + hourmin + '"]').length) {
						jQuery(selector).val(hourmin);
					}
				}
			}

			if (typeof success === 'function') {
				success(obj);
			}
		},
		function(error) {
			console.error(error);
			jQuery(selector).prop('disabled', false);

			if (typeof error === 'function') {
				error(obj);
			}
		}
	);
}
JS
			;
		}

		// add js to document head
		JFactory::getDocument()->addScriptDeclaration($js);
	}

	/**
	 * Declares the statement that will be used to initialize a datepicker.
	 *
	 * @param 	string 	$selector  The datepicker selector.
	 * @param 	string 	$group     The group to which the date belongs (restaurant or takeaway).
	 *
	 * @return 	void
	 */
	public static function datepicker($selector, $group = 'restaurant')
	{
		$config = VREFactory::getConfig();

		// get date format
		$date_format = $config->get('dateformat');

		// get closing days
		$closing_days = json_encode(VikRestaurants::getClosingDays());

		// instantiate special days manager
		$daysManager = new VRESpecialDaysManager($group);

		// obtain a list of matching special days
		$special_days = json_encode($daysManager->getList());

		/**
		 * Get minimum and maximum days to use for the calendar
		 * according to the configuration of the selected client.
		 *
		 * @since 1.8
		 */
		if ($group == 'restaurant')
		{
			$minDate = $config->getUint('mindate');
			$maxDate = $config->getUint('maxdate');
		}
		else
		{
			$minDate = $config->getUint('tkmindate');
			$maxDate = $config->getUint('tkmaxdate');
		}

		// check if the user is an operator
		$is_operator = VikRestaurants::getOperator() ? 1 : 0;

		// attach regional jQuery datepicker first
		VREApplication::getInstance()->attachDatepickerRegional();

		$js = 
<<<JS
jQuery(document).ready(function() {

	// check if we have a mobile or a tablet
	if (window.matchMedia && window.matchMedia("only screen and (max-width: 760px)").matches) {
		// prevent keyboard easing, by blurring the field every time it gets focused
		jQuery('{$selector}')
			.attr('autocomplete', 'off')
			.attr('onfocus', 'this.blur()');
	}

	var closingDays = {$closing_days};
	var specialDays = {$special_days};

	var format    = "{$date_format}";
	var separator = format[1];

	// strip any separators from date format
	format = format.replace(/[^a-z]/gi, '');

	switch (format) {
		case 'Ymd':
			format = 'yy' + separator + 'mm' + separator + 'dd';
			break;

		case 'mdY':
			format = 'mm' + separator + 'dd' + separator + 'yy';
			break;

		default:
			format = 'dd' + separator + 'mm' + separator + 'yy';
	}

	var minDate = new Date();
	var maxDate = null;

	if ($minDate) {
		// adjust min date according to configuration
		minDate.setDate(minDate.getDate() + $minDate);
	}

	if ($maxDate) {
		// set max date according to configuration
		maxDate = new Date();
		maxDate.setDate(maxDate.getDate() + $maxDate);
	}

	if ($is_operator) {
		// reset restrictions in case of operator
		minDate = maxDate = null;
	}

	var today_midnight = new Date();
	today_midnight.setHours(0, 0, 0, 0);

	jQuery('{$selector}').datepicker({
		minDate:       minDate,
		maxDate:       maxDate,
		dateFormat:    format,
		beforeShowDay: function(date) {
			var found     = false;
			var clazz     = '';
			var ignore_cd = false;
			
			if (today_midnight.valueOf() > date.valueOf()) {
				if ($is_operator) {
					return [true, ''];
				}

				// date in the past
				return [false, ''];
			}
		
			for (var i = 0; i < specialDays.length && !found; i++) {
				var sd = specialDays[i];

				if (checkSpecialDay(date, sd)) {
					if (sd.markoncal) {
						clazz = 'vrtdspecialday';
					}

					if (sd.ignoreClosingDays) {
						ignore_cd = true;
					}

					// stop iterating
					found = true;
				}
			}
			
			if (ignore_cd == false) {
				for (var i = 0; i < closingDays.length; i++) {
					if (checkClosingDay(date, closingDays[i])) {
						// closing day
						return [false, ''];
					}
				}
			}
			
			return [true, clazz];
		},
	});

	function checkSpecialDay(date, specialDay) {
		if (specialDay.startDate && specialDay.endDate) {
			// special day has publishing dates
			var start = getDateFromFormat(specialDay.startDate);
			var end   = getDateFromFormat(specialDay.endDate);
			end.setHours(23, 59, 59);

			if (start.valueOf() > date.valueOf() || end.valueOf() < date.valueOf()) {
				// day out of publishing dates
				return false;
			}
		}

		if (specialDay.days.length) {
			// special day has week days filters
			if (specialDay.days.indexOf(date.getDay()) == -1) {
				// week day not supported
				return false;
			}
		}

		// special day is compatible
		return true;
	}

	function checkClosingDay(date, closingDay) {
		var cd = getDateFromFormat(closingDay.date);
					
		if (closingDay.freq == 0) {
			// no recurrence, date must be the same
			if (cd.getDate() == date.getDate() && cd.getMonth() == date.getMonth() && cd.getFullYear() == date.getFullYear()) {
				return true;
			}
		} else if (closingDay.freq == 1) {
			// weekly recurrence, week day must be the same
			if (cd.getDay() == date.getDay()) {
				return true;
			}
		} else if (closingDay.freq == 2) {
			// monthly recurrence, day of month must be the same
			if (cd.getDate() == date.getDate()) {
				return true;
			} 
		} else if (closingDay.freq == 3) {
			// yearly recurrence, day and month must be the same
			if (cd.getDate() == date.getDate() && cd.getMonth() == date.getMonth()) {
				return true;
			} 
		}

		// not a closing day
		return false;
	}

	function getDateFromFormat(day) {
		var wildcards = format.split(separator);
		var chunks    = day.split(separator);
		
		var _args = {};
		for (var i = 0; i < wildcards.length; i++) {
			_args[wildcards[i]] = parseInt(chunks[i]);
		}
		
		return new Date(_args['yy'], _args['mm'] - 1, _args['dd']);
	}

});
JS
		;

		// add js to document head
		JFactory::getDocument()->addScriptDeclaration($js);
	}

	/**
	 * Declares the statement that will be used to initialize a standard datepicker.
	 *
	 * @param 	string 	$selector  The datepicker selector.
	 *
	 * @return 	void
	 */
	public static function calendar($selector)
	{
		$config = VREFactory::getConfig();

		// get date format
		$date_format = $config->get('dateformat');

		// attach regional jQuery datepicker first
		VREApplication::getInstance()->attachDatepickerRegional();

		$js = 
<<<JS
jQuery(document).ready(function() {

	// check if we have a mobile or a tablet
	if (window.matchMedia && window.matchMedia("only screen and (max-width: 760px)").matches) {
		// prevent keyboard easing, by blurring the field every time it gets focused
		jQuery('{$selector}')
			.attr('autocomplete', 'off')
			.attr('onfocus', 'this.blur()');
	}

	var format    = "{$date_format}";
	var separator = format[1];

	// strip any separators from date format
	format = format.replace(/[^a-z]/gi, '');

	switch (format) {
		case 'Ymd':
			format = 'yy' + separator + 'mm' + separator + 'dd';
			break;

		case 'mdY':
			format = 'mm' + separator + 'dd' + separator + 'yy';
			break;

		default:
			format = 'dd' + separator + 'mm' + separator + 'yy';
	}

	jQuery('{$selector}').datepicker({
		dateFormat:    format,
	});

});
JS
		;

		// add js to document head
		JFactory::getDocument()->addScriptDeclaration($js);
	}

	/**
	 * Registers a callback to trigger every time the "MORE"
	 * option button gets clicked from the people dropdown.
	 *
	 * @param 	string 	$selector  The people input selector.
	 *
	 * @return 	void
	 */
	public static function morepeople($selector)
	{
		$config = VREFactory::getConfig();

		// make sure large party label is enabled
		if (!$config->getBool('largepartylbl'))
		{
			// more people option is disabled
			return false;
		}

		// get URL from config
		$url = VikRestaurants::translateSetting('largepartyurl');

		if (!$url || $url == 'index.php' || $url == '/')
		{
			// use base uri
			$url = JUri::root();
		}
		else
		{
			// route large party URL
			$url = JRoute::_($url, false);
		}

		$js = 
<<<JS
jQuery(document).ready(function() {

	jQuery('{$selector}').on('change', function() {
		if (jQuery(this).val() == -1)
		{
			document.location.href = '{$url}';
		}
	});

});
JS
		;

		// add js to document head
		JFactory::getDocument()->addScriptDeclaration($js);
	}

	/**
	 * Returns the JS instantiation of a date according
	 * to dates formatted using the system format.
	 *
	 * @param 	string   $date  The formatted date.
	 * @param 	integer  $hour  An optional hour.
	 * @param 	integer  $min   An optional minute.
	 *
	 * @return 	string 	 The JS code.
	 */
	public static function jsdate($date, $hour = 0, $min = 0)
	{
		if (!preg_match("/^\d+$/", $date))
		{
			// create timestamp
			$ts = VikRestaurants::createTimestamp($date, $hour, $min);
		}
		else
		{
			// timestamp already passed
			$ts = (int) $date;
		}

		// create chunks
		$year = (int) date('Y', $ts);
		$mon  = (int) date('n', $ts) - 1;
		$day  = (int) date('j', $ts);

		$hour = (int) date('G', $ts);
		$min  = (int) date('i', $ts);
		$sec  = (int) date('s', $ts);
		
		return "new Date($year, $mon, $day, $hour, $min, $sec)";
	}

	/**
	 * Animates the document in case the specified selector
	 * is currently not visible within the screen.
	 *
	 * @param 	mixed 	 $selector  The page will be animated as long as the
	 * 							    specified element is not on top of the page.
	 * @param 	integer  $maring    An optional margin to use as threshold.
	 *
	 * @return 	void
	 *
	 * @since 	1.8.1
	 */
	public static function animate($selector = null, $margin = 20)
	{
		/**
		 * Check whether the pages animation has been disabled.
		 * It is possible safely disable the animation of the
		 * pages by inserting a new record within the configuration
		 * database table of VikRestaurants.
		 *
		 * INSERT INTO `#__vikrestaurants_config` (`param`, `setting`)
		 * VALUES ('animatepages', 0);
		 */
		$disabled = VREFactory::getConfig()->getBool('animatepages', true);

		if (!$disabled)
		{
			// pages animation has been disabled globally
			return;
		}

		if (!$selector)
		{
			// use default "main" container, which seems to be the
			// default identifier for both Joomla and WP platforms
			$selector = '#main';
		}

		// use a valid margin
		$margin = (int) $margin;

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(function() {
	// flag used to check whether the page
	// has been scrolled before executing the
	// animation
	var hasScrolled = false;

	var scrollDetector = function() {
		hasScrolled = true;

		// self turn off scroll detector
		jQuery(window).off('scroll', scrollDetector);
	}

	jQuery(window).on('scroll', scrollDetector);

	onDocumentReady().then(function() {
		// get element
		var elem = jQuery('$selector');

		if (elem.length == 0 && '$selector' == '#main') {
			// the template is not using the default notation,
			// try to observe the beginning of VikRestaurants
			elem = jQuery('.vikrestaurants-start-body');
		}

		// make sure the page hasn't been already scrolled in order to 
		// avoid debounces, then make sure the element exists and it is not visible
		if (!hasScrolled && elem.length && isBoxOutOfMonitor(elem, $margin)) {
			jQuery('html, body').animate({
				scrollTop: elem.offset().top - $margin,
			});
		}
	});
});
JS
		);
	}
}
