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
JHtml::_('vrehtml.assets.contextmenu');

$config = VREFactory::getConfig();

$vik = VREApplication::getInstance();

// get all supported tables
$allTables = JHtml::_('vrehtml.admin.tables');
?>

<h3><?php echo JText::_('VRCONFIGFIELDSETRESERVATION'); ?></h3>

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
			if ($this->order->stay_time)
			{
				$checkout = JText::sprintf(
					'VRECHECKOUTEXT',
					date($config->get('timeformat'), $this->order->checkout),
					VikRestaurants::minutesToStr($this->order->stay_time)
				);

				?><i class="fas fa-stopwatch hasTooltip" title="<?php echo $this->escape($checkout); ?>" style="margin-left:4px;"></i><?php
			}
			?>
		</div>

	</div>

	<!-- People -->

	<div class="order-field">

		<label><?php echo JText::_('VRMANAGERESERVATION4'); ?></label>

		<div class="order-field-value">
			<b><?php echo $this->order->people; ?></b>&nbsp;
			<?php
			for ($p = 1; $p <= min(array(2, $this->order->people)); $p++)
			{
				?><i class="fas fa-male"></i><?php
			}
			?>
		</div>

	</div>

	<!-- Table -->

	<div class="order-field">

		<label><?php echo JText::_('VRMANAGERESERVATION5'); ?></label>

		<div class="order-field-value">
			<span class="badge badge-important"><?php echo $this->order->room_name; ?></span>

			<?php
			foreach ($this->order->tables as $table)
			{
				?>
				<span class="badge badge-info table-handle" data-order-id="<?php echo $table->id_order; ?>" data-table-id="<?php echo $table->id; ?>">
					<?php echo $table->name; ?>
				</span>
				<?php
			}
			?>

			<?php
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
	// check if there is at least an operator
	$operators = JHtml::_('vikrestaurants.operators', $group = 1);

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
						'index.php?option=com_vikrestaurants&task=reservation.assignoperatorajax&tmpl=component',
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

<script>

	<?php
	/**
	 * Implemented a quick and free way of assigning the
	 * reservation to a different table. It is mainly useful
	 * for those reservations that belong to a parent (cluster),
	 * since they are not editable.
	 *
	 * @since 1.8.3
	 */
	?>
	jQuery(function($) {
		// Helper function used to submit the selected table.
		var tableActionCallback = function(root, event) {
			// get table currently selected
			var prevId   = parseInt($(root).attr('data-table-id'))
			var prevText = $(root).text();

			// replace with selected ID
			$(root).attr('data-table-id', this.id);
			$(root).text(this.text);

			// make AJAX request
			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=reservation.changetableajax'); ?>',
				{
					id_table: this.id,
					id_order: $(root).data('order-id'),
				},
				function(resp) {
					// all fine
				},
				function(error) {
					// something went wrong, restore previous value and text
					$(root).attr('data-table-id', prevId).text(prevText);	

					console.error(error);
				}
			);
		};

		// Helper function used to check whether the table should
		// be selectable or not. Only the tables already selected
		// are marked as disabled.
		var tableDisabledCallback = function(root, config) {
			var tables = [];

			// retrieve list of selected tables
			$('.table-handle').each(function() {
				tables.push(parseInt($(this).attr('data-table-id')));
			});

			// make sure the table is not selected
			return tables.indexOf(this.id) !== -1;
		};

		// Helper function used to check whether the table should
		// be displayed or not. The tables that already own a
		// reservation for the current time will be excluded.
		var tableVisibleCallback = function(root, config) {
			// load tables occupancy
			var occupancy = <?php echo json_encode($this->occupiedTables); ?>;

			// display table only if not occupied
			return occupancy.indexOf(this.id) === -1;
		};

		$('.table-handle').vikContextMenu({
			clickable: true,
			class: 'tables-context-menu',
			buttons: [
				<?php
				foreach ($allTables as $room)
				{
					foreach ($room as $table)
					{
						?>
						{
							id: <?php echo $table->value; ?>,
							text: '<?php echo addslashes($table->text); ?>',
							separator: <?php echo end($room) === $table ? 'true' : 'false'; ?>,
							action: tableActionCallback,
							disabled: tableDisabledCallback,
							visible: tableVisibleCallback,
						},
						<?php
					}
				}
				?>
			],
		});

	});

</script>
