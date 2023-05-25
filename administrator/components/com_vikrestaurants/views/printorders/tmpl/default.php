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

<div class="vr-printer-layout">

	<?php
	if (strlen($this->text['header']))
	{
		?>
		<div class="vr-printer-header"><?php echo $this->text['header']; ?></div>
		<?php
	}
	?>

	<div class="vr-print-orders-container">
		<?php
		foreach ($this->rows as $i => $r)
		{	
			// save order as class property for being used in sub-layout
			$this->orderDetails = $r;

			if ($this->type == 1)
			{
				// display take-away order details
				echo $this->loadTemplate('takeaway');
			}
			else
			{
				// display restaurant reservation details
				echo $this->loadTemplate('restaurant');	
			}	
		}
		?>
	</div>

	<?php
	if (strlen($this->text['footer']))
	{
		?>
		<div class="vr-printer-footer"><?php echo $this->text['footer']; ?></div>
		<?php
	}
	?>

</div>

<script>
	
	jQuery(document).ready(function() {
		window.print();
	});
	
</script>
