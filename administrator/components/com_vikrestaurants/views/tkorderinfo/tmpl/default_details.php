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

JHtml::_('formbehavior.chosen');

$config = VREFactory::getConfig();

$vik = VREApplication::getInstance();

?>

<h3><?php echo JText::_('VRTKORDERCARTFIELDSET1'); ?></h3>

<div class="order-fields">

	<!-- Order ID -->

	<div class="order-field">

		<label>
			<?php echo JText::_('VRMANAGERESERVATION1'); ?>
		</label>

		<div class="order-field-value">
			<b><?php echo $this->order->id . '-' . $this->order->sid; ?></b>

			<?php
			if ($this->order->author)
			{
				$creation = JText::sprintf(
					'VRRESLISTCREATEDTIP',
					JHtml::_('date', JDate::getInstance($this->order->created_on), JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'), date_default_timezone_get()),
					$this->order->author->name
				);

				?><i class="fas fa-calendar-check hasTooltip" title="<?php echo $this->escape($creation); ?>" style="margin-left:4px;"></i><?php
			}
			?>
		</div>

	</div>

	<!-- Status -->

	<div class="order-field">

		<label><?php echo JText::_('VRMANAGERESERVATION12'); ?></label>

		<div class="order-field-value">
			<span class="vrreservationstatus<?php echo strtolower($this->order->status); ?>">
				<?php echo JText::_('VRRESERVATIONSTATUS' . $this->order->status); ?>
			</span>

			<?php
			// show remaining time available to accept the reservation
			if ($this->order->status == 'PENDING')
			{
				if ($this->order->locked_until > time())
				{
					$expires_in = JText::sprintf(
						'VRTKRESEXPIRESIN',
						VikRestaurants::formatTimestamp($config->get('timeformat'), $this->order->locked_until, $local = false)
					);

					?><i class="fas fa-question-circle hasTooltip" title="<?php echo $this->escape($expires_in); ?>" style="margin-left:4px;"></i><?php
				}
			}
			?>
		</div>

	</div>

	<!-- Check-in -->

	<div class="order-field">

		<label><?php echo JText::_('VRMANAGERESERVATION3'); ?></label>

		<div class="order-field-value">
			<b><?php echo JHtml::_('date', JDate::getInstance($this->order->checkin_ts), JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'), date_default_timezone_get()); ?></b>

			<?php
			if ($this->order->preparation_ts)
			{
				// subtract a time slot from the preparation time
				$this->order->preparation_ts = strtotime('-' . $config->get('tkminint') . ' minutes', $this->order->preparation_ts);
				// fetch preparation time hint
				$prepTip = JText::sprintf('VRE_TKRES_PREP_TIME_HINT', date($config->get('timeformat'), $this->order->preparation_ts));

				?>
				<div style="font-weight: normal;display: inline-block;">
					<i class="fas fa-info-circle hasTooltip" title="<?php echo $this->escape($prepTip); ?>" style="margin-left:4px;"></i>
				</div>
				<?php
			}
			?>
		</div>

	</div>

	<!-- Service -->

	<div class="order-field">

		<label><?php echo JText::_('VRMANAGETKRES13'); ?></label>

		<div class="order-field-value">
			<b><?php echo JText::_($this->order->delivery_service ? 'VRMANAGETKRES14' : 'VRMANAGETKRES15'); ?></b>

			<?php
			// in case of delivery service and route options, we might suggest here
			// the maximum time to start the delivery of the order
			if ($this->order->delivery_service && $this->order->route && !empty($this->order->route->origin))
			{
				// fetch route information
				$hint = JText::sprintf(
					'VRTK_ADDR_ROUTE_NOTES',
					$this->order->route->distancetext,
					$this->order->route->durationtext
				);

				// fetch delivery time
				$leave_at = strtotime('-' . $this->order->route->duration . ' seconds', $this->order->checkin_ts);
				// format delivery time
				$leave_at = date($config->get('timeformat'), $leave_at);

				$hint .= '<br /><br />' . JText::sprintf('VRTK_ADDR_ROUTE_START', $leave_at);
				?>
				<i class="fas fa-truck hasTooltip" title="<?php echo $this->escape($hint); ?>" style="margin-left:4px;"></i>
				<?php
			}

			if (strip_tags($this->order->notes))
			{
				$notes = $this->order->notes;
				// always obtain short description, if any
				$vik->onContentPrepare($notes, false);

				?><i class="fas fa-sticky-note hasTooltip" title="<?php echo $this->escape($notes->text); ?>" style="margin-left:4px;"></i><?php
			}
			?>
		</div>

	</div>

	<?php
	if ($this->order->delivery_service)
	{
		?>
		<!-- Address -->

		<div class="order-field">

			<label><?php echo JText::_('VRMANAGETKRES29'); ?></label>

			<div class="order-field-value">
				<b><?php echo $this->order->purchaser_address; ?></b>
			</div>

		</div>
		<?php
	}
	?>

	<?php
	// check if there is at least an operator
	$operators = JHtml::_('vikrestaurants.operators', $group = 2);

	if ($operators)
	{
		?>
		<!-- Operator -->

		<div class="order-field">

			<label><?php echo JText::_('VROPERATORFIELDSET1'); ?></label>

			<div class="order-field-value">
				<select id="operator-assign">
					<option value="0">--</option>
					<?php echo JHtml::_('select.options', $operators, 'value', 'text', $this->order->id_operator); ?>
				</select>
			</div>

		</div>

		<script>
			
			jQuery(document).ready(function() {

				VikRenderer.chosen('#operator-assign', 200);

				// take current operator
				var ID_OPERATOR = <?php echo (int) $this->order->id_operator; ?>;

				jQuery('#operator-assign').on('change', function() {
					// disable dropdown until the end of the request
					var _select = jQuery(this).disableChosen(true);

					UIAjax.do(
						'index.php?option=com_vikrestaurants&task=tkreservation.assignoperatorajax&tmpl=component',
						{
							id: <?php echo $this->order->id; ?>,
							id_operator: _select.val(),
						},
						function(resp) {
							// operator assigned successfully, unlock dropdown
							_select.disableChosen(false);
							// update cached operator
							ID_OPERATOR = _select.val();
						},
						function(err) {
							// an error occurred, unlock dropdown
							_select.disableChosen(false);
							// revert to previous operator
							_select.updateChosen(ID_OPERATOR);
						}
					);

				});

			});

		</script>
		<?php
	}
	?>

</div>
