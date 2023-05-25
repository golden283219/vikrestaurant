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
 * VikRestaurants HTML admin scripts helper.
 *
 * @since 1.8
 */
abstract class VREHtmlScripts
{
	/**
	 * Registers a script that will be used to handle the dismiss
	 * button of the alerts. If dismissed, they won't appear anymore
	 * until the expiration date.
	 *
	 * @param 	string 	$selector  The alert HTML selector.
	 *
	 * @return 	void
	 */
	public static function cookiealert($selector = '.alert')
	{
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {

	jQuery('{$selector}').find('button[data-signature]').on('click', function() {
		var cookie = [];
		cookie.push('alert_dismiss_' + jQuery(this).data('signature') + '=1');

		if (jQuery(this).data('expdate')) {
			var date = new Date(jQuery(this).data('expdate'));
			cookie.push('expires=' + date.toUTCString());
		}

		cookie.push('path=/');

		document.cookie = cookie.join('; ');
	});

});
JS
		);
	}

	/**
	 * Returns the script used to store the selected view tab
	 * within the user state as a cookie.
	 *
	 * @param 	string   $tab   The tab group name.
	 * @param 	string   $key   The cookie name.
	 * @param 	integer  $days  The number of days for which the cookie should exist.
	 *
	 * @return 	string   The script (without <script> delimiters).
	 */
	public static function tabhandler($tab, $key, $days = null)
	{
		$days = (int) $days;

		return
<<<JS
jQuery(document).ready(function() {
		
	jQuery('a[href^="#{$tab}_"]').on('click', function() {
		var href = jQuery(this).attr('href').substr(1);

		if ({$days} > 0) {
			var date = new Date();
			date.setDate(date.getDate() + {$days});
			
			document.cookie = '{$key}=' + href + '; expires=' + date.toUTCString() + '; path=/';
		} else {
			// keep only for current session
			document.cookie = '{$key}=' + href + '; path=/';
		}
	});

});
JS
		;
	}

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
		// use front-end helper method
		JHtml::_('vrehtml.sitescripts.updateshifts', $group, $funcname);
	}

	/**
	 * Renders the flag image within the options of the dropdowns
	 * matching the specified selector.
	 *
	 * @param 	string 	$selector  The DOM selector.
	 *
	 * @return 	void
	 */
	public static function selectflags($selector = '.vre-flag-sel')
	{
		// make sure select2 is loaded
		JHtml::_('vrehtml.assets.select2');

		$uri = VREASSETS_URI . 'css/flags/';

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('{$selector}').select2({
		allowClear: false,
		width: 200,
		minimumResultsForSearch: -1,
		formatResult: vreFormatFlags,
		formatSelection: vreFormatFlags,
		escapeMarkup: function(m) { return m; },
	});

	function vreFormatFlags(opt) {
		if (!opt.id) {
			// optgroup
			return opt.text;
		}

		var tag = opt.id;

		if (opt.id.match(/^[a-z]{2,3}-[a-z]{2,2}$/i)) {
			// we have a langtag
			tag = tag.split('-').pop();
		}

		return '<img class="vr-opt-flag" src="{$uri}' + tag.toLowerCase() + '.png" />' + opt.text;
	}
});
JS
		);
	}

	/**
	 * Method to make the specified table listable.
	 *
	 * @param   string   $tableId  DOM id of the table.
	 * @param   string   $formId   DOM id of the form.
	 * @param   string   $sortDir  Sort direction.
	 * @param   string   $saveUrl  Save ordering url, ajax-load after an item is dropped.
	 * @param 	mixed 	 $filters  A list of filters to use when rearranging the records.
	 *
	 * @return  void
	 */
	public static function sortablelist($tableId, $formId = 'adminForm', $sortDir = 'asc', $saveOrderingUrl = null, $filters = array())
	{
		// load sortable list script
		VREApplication::getInstance()->addScript(VREASSETS_ADMIN_URI . 'js/sortablelist.js');

		// create JSON data
		$data = array(
			'form'          => '#' . $formId,
			'direction'     => strtolower($sortDir),
			'saveUrl'       => $saveOrderingUrl,
			'inputSelector' => 'input[name="order[]"]',
		);

		if ($filters)
		{
			// inject filters if specified
			$data['filters'] = $filters;
		}

		$data = json_encode($data);

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('#{$tableId} tbody').viksortablelist({$data});
});
JS
		);
	}

	/**
	 * Replaces the default behavior.modal function provided by Joomla,
	 * by supporting our Fancybox jQuery plugin.
	 *
	 * @return  void
	 *
	 * @since   1.8.2
	 */
	public static function modal($selector = 'a.modal', $params = array())
	{
		static $loaded = 0;

		if ($loaded)
		{
			return;
		}

		$loaded = 1;

		// load fancybox scripts
		JHtml::_('vrehtml.assets.fancybox');

		// add script to support modal
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('{$selector}').on('click', function(e) {
		// get link HREF
		var href = jQuery(this).attr('href');

		// prevent default link action
		e.preventDefault();

		// extract href from link
		var href = jQuery(this).attr('href');

		// check if we have an image
		if (href.match(/\.(png|jpe?g|gif|bmp)$/i)) {
			// open fancybox containing image preview
			vreOpenModalImage(href);
		} else {
			// otherwise fallback to default browser opening
			vreOpenPopup(href);
		}

		return false;
	}).removeClass('modal').removeAttr('target');
});
JS
		);
	}
}
