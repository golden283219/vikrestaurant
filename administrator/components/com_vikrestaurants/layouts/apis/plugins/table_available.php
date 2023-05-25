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

$config = VREFactory::getConfig();

// create base URI
$uri = 'index.php?option=com_vikrestaurants&task=apis&event=' . $plugin->getName();
$uri = $vik->routeForExternalUse($uri);

?>

<p>Check if a table is available for a certain date, time and number of people.</p>

<h3>Usage</h3>

<pre>
<strong>End-Point URL</strong>
<?php echo $uri; ?>


<strong>Params</strong>
username    (string)    The username of the application.
password    (string)    The password of the application.
date        (string)    The date of the reservation in <?php echo $config->get('dateformat'); ?> format.
hourmin     (string)    The time of the reservation in H:m format.
people      (int)       The party size of the reservation between <?php echo $config->getUint('minimumpeople'); ?> and <?php echo $config->getUint('maximumpeople'); ?>.
id_table    (int)       The ID of the table to check. Leave empty to check the availability of all the published tables.
</pre>

<br />

<h3>Generate Table Availability URL</h3>

<div style="margin-bottom: 10px;" class="form-with-select">

	<select id="plg-login">
		<?php echo JHtml::_('select.options', JHtml::_('vrehtml.admin.apilogins', true)); ?>
	</select>

	<input type="text" id="plg-date" placeholder="Date (<?php echo $config->get('dateformat'); ?>)" size="16" style="margin-right: 5px;" />

	<input type="text" id="plg-time" placeholder="Time (H:m)" size="8" style="margin-right: 5px;" />

	<select id="plg-people" style="margin-right: 5px;">
		<?php echo JHtml::_('select.options', JHtml::_('vikrestaurants.people', false)); ?>
	</select>

	<?php
	$args = array(
		'group.items' => null,
		'list.attr'   => array('id' => 'plg-table'),
	);

	$tables = JHtml::_('vrehtml.admin.tables', true);

	echo JHtml::_('select.groupedList', $tables, '', $args);
	?>

</div>

<pre id="plgurl">

</pre>

<br />

<h3>Successful Response (JSON)</h3>

<p>Successful response in case the request specifies the ID of the table to check.</p>

<pre>
{
    status: 1, // table available
    table: 3, // the ID of the available table
    details: {
        // an object containing the table details
    }
}
</pre>

<p>Successful response in case the table ID is not provided.</p>

<pre>
{
    status: 1, // table available
    table: 3, // the ID of the FIRST available table
    list: [
        // a list containing the details of all the available tables
    ]
}
</pre>

<p>Response in case of no availability.</p>

<pre>
{
    status: 0, // table not available
    message: "A descriptive reason"
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
			clean += '&date=' + $('#plg-date').val();
			clean += '&hourmin=' + $('#plg-time').val();
			clean += '&people=' + $('#plg-people').val();

			var idt = parseInt($('#plg-table').val());

			if (!isNaN(idt) && idt > 0) {
				clean += '&id_table=' + $('#plg-table').val();
			}

			var url = encodeURI(clean);

			$("#plgurl").html('<a href="' + url + '" target="_blank">' + clean + '</a>');
		});

		$('#plg-login').trigger('change');
	});

</script>
