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

?>

<style>

	.no-printable {
		display: none;
	}

</style>

<div class="vr-operator-takeaway-print-orders">

	<?php
	foreach ($this->orders as $i => $order)
	{
		if ($i > 0)
		{
			?><div class="separator"></div><?php
		}

		echo $order->templateHTML;
	}
	?>

</div>

<script>
	
	jQuery(document).ready(function() {
		window.print();
	});
	
</script>
