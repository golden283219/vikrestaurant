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
 * Layout variables
 * -----------------
 * @var  EventAPIs   $plugin   The instance of the event.
 * @var  array       $layouts  A list of supported temmplate layouts.
 */
extract($displayData);

JHtml::_('formbehavior.chosen');

$vik = VREApplication::getInstance();

// create base URI
$uri = 'index.php?option=com_vikrestaurants&task=apis&event=' . $plugin->getName();
$uri = $vik->routeForExternalUse($uri);

?>

<p>Retrieve the details of a given restaurant reservation or take-away order.</p>

<h3>Usage</h3>

<pre>
<strong>End-Point URL</strong>
<?php echo $uri; ?>


<strong>Params</strong>
username    (string)          The username of the application.
password    (string)          The password of the application.
id          (int)             The ID of the order/reservation.
type        (int)             Specify 1 for take-away orders, otherwise 0 for restaurant reservations.
layout      (array|string)    The layout (or a list) to use to render the contents of the reservation/order.
                              When specified, the template property of the response will be an object containing
                              all the requested types of layouts.
langtag     (string)          An optional lang tag to load the contents according to the specified language.
                              When missing, the contents will refer to the default language of the website.
</pre>

<br />

<h3>Generate Order Details URL</h3>

<div style="margin-bottom: 10px;" class="form-with-select">

	<select id="plg-login">
		<?php echo JHtml::_('select.options', JHtml::_('vrehtml.admin.apilogins', true)); ?>
	</select>

	<input type="text" id="plg-oid" placeholder="Order ID" size="8" style="margin-right: 5px;" />

	<select id="plg-type">
		<?php echo JHtml::_('select.options', JHtml::_('vrehtml.admin.groups'), 'value', 'text', null, true); ?>
	</select>

	<select id="plg-langtag">
		<option value="">- Select Language -</option>
		<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing'), 'value', 'text', null, true); ?>
	</select>

	<select id="plg-layout" multiple="multiple">
		<?php echo JHtml::_('select.options', $layouts, 'value', 'text', null, true); ?>
	</select>

</div>

<pre id="plgurl">

</pre>

<br />

<h3>Successful Response (JSON)</h3>

<pre>
{
    status: 1,
    orderDetails: {
        id: 90,
        sid: "ABCD1234EFGH5678",
        purchaser_mail: "mail@domain.com",
        /* many other fields... */
        template: "the HTML template of the reservation/order",
        /* or an object in case of requested layout */
        template: {
            "html": "the HTML template of the reservation/order",
            "json": "the JSON representation of the reservation/order"
        }
    }
}
</pre>

<br />

<h3>Failure Response (JSON)</h3>

<pre>
{
    errcode: 500,
    error: "The reason of the error"
}
</pre>

<script type="text/javascript">

	jQuery(function($) {
		VikRenderer.chosen('.form-with-select:not(#plg-layout)');
		VikRenderer.chosen('#plg-layout', 'auto');
		
		$('.form-with-select').find('input,select').on('change', function() {
			var clean = '<?php echo $uri; ?>';

			var login = $('#plg-login').val().split(/;/);

			clean += '&username=' + login[0];
			clean += '&password=' + (login[1] ? login[1] : '');
			clean += '&id=' + $('#plg-oid').val();
			clean += '&type=' + $('#plg-type').val();

			var langtag = $('#plg-langtag').val();

			if (langtag) {
				clean += '&langtag=' + langtag;
			}

			var layouts = $('#plg-layout').val();

			if (layouts) {
				layouts.forEach((l) => {
					clean += '&layout[]=' + l;
				});
			}

			var url = encodeURI(clean);

			$("#plgurl").html('<a href="' + url + '" target="_blank">' + clean + '</a>');
		});

		$('#plg-login').trigger('change');
	});

</script>
