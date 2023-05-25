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

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.fontawesome');

$filters = $this->filters;

$vik = VREApplication::getInstance();

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

	<div style="padding: 10px 5px;">
	
		<?php echo $vik->bootStartTabSet('orderhistory', array('active' => 'orderhistory_operator')); ?>

			<!-- OPERATOR -->
				
			<?php
			echo $vik->bootAddTab('orderhistory', 'orderhistory_operator', JText::_('VRMANAGEOPLOG1'));
			echo $this->loadTemplate('operator');
			echo $vik->bootEndTab();
			?>

			<!-- PAYMENT LOGS -->

			<?php
			if ($this->payLog)
			{
				echo $vik->bootAddTab('orderhistory', 'orderhistory_payment', JText::_('VRMANAGERESERVATION20'));
				echo $this->loadTemplate('payment');
				echo $vik->bootEndTab();
			}
			?>

		<?php echo $vik->bootEndTabSet(); ?>

	</div>
	
	<input type="hidden" name="id" value="<?php echo $filters['id']; ?>" />
	<input type="hidden" name="group" value="<?php echo $filters['group']; ?>" />
	
	<input type="hidden" name="view" value="orderhistory" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>
