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

$card  = $this->creditCard;
$order = $this->order;

$vik = VREApplication::getInstance();

$config = VREFactory::getConfig();

?>

<div style="padding: 10px 20px;">

	<?php
	if (!$order)
	{
		// order not found
		echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'));
	}
	else if (!$card)
	{
		if (!empty($this->removed))
		{
			// the credit card details have been manually removed
			echo $vik->alert(JText::_('VRCREDITCARDREMOVED'), 'success');
		}
		else
		{
			// the order doesn't have credit card details
			echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'));
		}
	}
	else
	{
		?>
		<div class="btn-toolbar vr-btn-toolbar" style="height: 32px;">

			<div class="btn-group pull-left vr-toolbar-setfont">
				<strong>
					<?php
					$format = JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat');

					echo JText::sprintf(
						'VRCREDITCARDAUTODELMSG',
						JHtml::_('date', strtotime('+1 day', $order->checkin_ts), $format, date_default_timezone_get()) 
					);
					?>
				</strong>
			</div>

			<div class="btn-group pull-right">
				<button type="button" class="btn btn-danger" onclick="confirmCreditCardDelete();">
					<?php echo JText::_('VRDELETE'); ?>
				</button>
			</div>

		</div>

		<div class="row-fluid">

			<div class="span6">
				<?php echo $vik->openEmptyFieldset(); ?>

					<?php
					foreach ($card as $k => $v)
					{
						echo $vik->openControl($v->label.':');
						?>
							<input type="text" value="<?php echo $v->value; ?>" readonly size="32" />
							
							<?php
							if ($k == 'cardNumber')
							{
								?>
								<img src="<?php echo VREADMIN_URI . 'payments/off-cc/resources/icons/' . $card->brand->alias . '.png'; ?>" />
								<?php
							}

						echo $vik->closeControl();
					}
					?>

				<?php echo $vik->closeEmptyFieldset(); ?>
			</div>

		</div>

		<?php
		JText::script('VRSYSTEMCONFIRMATIONMSG');
		?>

		<script type="text/javascript">

			function confirmCreditCardDelete() {

				if (confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'))) {
					document.location.href = 'index.php?option=com_vikrestaurants&view=ccdetails&tmpl=component&id=<?php echo $this->id; ?>&tid=<?php echo $this->group; ?>&rmhash=<?php echo $this->rmHash; ?>';
				}

			}

		</script>
		<?php
	}
	?>

</div>
