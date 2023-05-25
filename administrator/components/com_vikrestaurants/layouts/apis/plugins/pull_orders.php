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

<p>Downloads a list containing all the take-away orders and the restaurant reservations that haven't been yet downloaded.</p>
<p>This plugin saves internally the latest fetched IDs for both the reservations and orders. This means that, at the next execution, the plugin won't load any records with ID lower than the saved ones.</p>

<h3>Usage</h3>

<pre>
<strong>End-Point URL</strong>
<?php echo $uri; ?>


<strong>Params</strong>
username    (string)    The username of the application.
password    (string)    The password of the application.
reset       (bool)      True to reset the orders to download. When passing this argument,
                        the plugin will always return a list containing all the existing
                        restaurant reservations and take-away orders.
</pre>

<br />

<h3>Generate Pull Orders URL</h3>

<div style="margin-bottom: 10px; display: flex; align-items: center;" class="form-with-select">

	<select id="plg-login">
		<?php echo JHtml::_('select.options', JHtml::_('vrehtml.admin.apilogins', true)); ?>
	</select>

	<label for="reset1" style="margin: 0 6px 0 20px;">Reset</label>

	<?php
	$yes = $vik->initRadioElement('', '', false);
	$no  = $vik->initRadioElement('', '', true);

	echo $vik->radioYesNo('reset', $yes, $no);
	?>

</div>

<pre id="plgurl">

</pre>

<br />

<h3>Successful Response (JSON)</h3>

<pre>
[
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

			if ($('#reset1').is(':checked')) {
				clean += '&reset=1';
			}

			var url = encodeURI(clean);

			$("#plgurl").html('<a href="' + url + '" target="_blank">' + clean + '</a>');
		});

		$('#plg-login').trigger('change');
	});

</script>
