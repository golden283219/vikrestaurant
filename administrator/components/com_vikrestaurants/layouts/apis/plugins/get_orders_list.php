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
 * @var  EventAPIs   $plugin  The instance of the event.
 */
extract($displayData);

JHtml::_('formbehavior.chosen');

$vik = VREApplication::getInstance();

// create base URI
$uri = 'index.php?option=com_vikrestaurants&task=apis&event=' . $plugin->getName();
$uri = $vik->routeForExternalUse($uri);

?>

<p>Download a list containing all the take-away orders and the restaurant reservations.</p>

<h3>Usage</h3>

<pre>
<strong>End-Point URL</strong>
<?php echo $uri; ?>


<strong>Params</strong>
username    (string)    The username of the application.
password    (string)    The password of the application.
last_id     (int[])     Specify an ID for both the sections to download all the orders greater than those IDs. 
                        The first index of the array represents the initial ID (excluded) for the restaurant reservations. 
                        The second index of the array represents the initial ID (excluded) for the take-away orders. 
                        The example query string "&last_id[]=10&last_id[]=16" means that you will download all the restaurant 
                        reservations with ID higher than 10 and all the take-away orders with ID higher than 16.
</pre>

<br />

<h3>Generate Orders List URL</h3>

<div style="margin-bottom: 10px;" class="form-with-select">

	<select id="plg-login">
        <?php echo JHtml::_('select.options', JHtml::_('vrehtml.admin.apilogins', true)); ?>
    </select>

	<input type="text" id="plg-lastid-0" placeholder="Restaurant ID" size="12" style="margin-right: 5px;" />
	
	<input type="text" id="plg-lastid-1" placeholder="Take-Away ID" size="12" style="margin-right: 5px;" />

</div>

<pre id="plgurl">

</pre>

<br />

<h3>Successful Response (JSON)</h3>

<pre>
{
    status: 1,
    orders: [
        {
            id: 25,
            sid: "GD83GDK83HSMZ0H9",
            purchaser_nominative: "John Smith",
            purchaser_mail: "mail.one@domain.com",
            created_on: <?php echo strtotime('-' . rand(1, 999) . ' minutes'); ?>, // UNIX timestamp
            checkin_ts: <?php echo mktime(rand(0, 23), rand(0, 23), rand(0, 59), rand(1, 12), rand(1, 31), (int) date('Y')); ?>, // UNIX timestamp
            group: 0 // restaurant reservation
        },
        {
            id: 39,
            sid: "LAJS92YSBW0SHW05",
            purchaser_nominative: "Barney Black",
            purchaser_mail: "mail.two@domain.com",
            created_on: <?php echo strtotime('-' . rand(999, 9999) . ' minutes'); ?>, // UNIX timestamp
            checkin_ts: <?php echo mktime(rand(0, 23), rand(0, 23), rand(0, 59), rand(1, 12), rand(1, 31), (int) date('Y')); ?>, // UNIX timestamp
            group: 1 // take-away order
        }
    ]
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
        VikRenderer.chosen('.form-with-select');
		
		$('.form-with-select').find('input,select').on('change', function() {
			var clean = '<?php echo $uri; ?>';

			var login = $('#plg-login').val().split(/;/);

            clean += '&username=' + login[0];
            clean += '&password=' + (login[1] ? login[1] : '');
			clean += '&last_id[]=' + $('#plg-lastid-0').val();
			clean += '&last_id[]=' + $('#plg-lastid-1').val();

			var url = encodeURI(clean);

			$("#plgurl").html('<a href="' + url + '" target="_blank">' + clean + '</a>');
		});

		$('#plg-login').trigger('change');
	});

</script>
