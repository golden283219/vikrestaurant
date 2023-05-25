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

$vik = VREApplication::getInstance();

$currency = VREFactory::getCurrency();

?>

<div class="inspector-form" id="inspector-group-form">

	<?php echo $vik->bootStartTabSet('tkgroup', array('active' => 'tkgroup_details')); ?>

		<?php echo $vik->bootAddTab('tkgroup', 'tkgroup_details', JText::_('VRMAPDETAILSBUTTON')); ?>

			<div class="inspector-fieldset">
			
				<!-- GROUP TITLE - Text -->
				<?php
				$help = $vik->createPopover(array(
					'title'   => JText::_('VRTKMANAGEENTRYGROUP1'),
					'content' => JText::_('VRTKMANAGEENTRYGROUPTITLE_HELP'),
				));

				echo $vik->openControl(JText::_('VRTKMANAGEENTRYGROUP1') . '*' . $help); ?>
					<input type="text" id="group_title" value="" class="field required" size="40" />
				<?php echo $vik->closeControl(); ?>

				<!-- GROUP DESCRIPTION - Textarea -->
				<?php
				$help = $vik->createPopover(array(
					'title'   => JText::_('VRMANAGETKMENU2'),
					'content' => JText::_('VRTKMANAGEENTRYGROUPDESC_HELP'),
				));

				echo $vik->openControl(JText::_('VRMANAGETKMENU2') . $help); ?>
					<textarea id="group_description" class="field" maxlength="128" style="height: 60px; resize: none;"></textarea>
				<?php echo $vik->closeControl(); ?>

				<!-- GROUP VARIATION - Select -->
				<?php
				$variations = array(
					JHtml::_('select.option', '', ''),
				);

				foreach ($this->entry->variations as $var)
				{
					$variations[] = JHtml::_('select.option', $var->id, $var->name);
				}

				echo $vik->openControl(JText::_('VRTKMANAGEENTRYGROUP5')); ?>
					<select id="group_variation" class="field">
						<?php echo JHtml::_('select.options', $variations); ?>
					</select>
				<?php echo $vik->closeControl(); ?>

				<!-- GROUP MULTIPLE - Checkbox -->
				<?php
				$yes = $vik->initRadioElement('', JText::_('JYES'), true);
				$no  = $vik->initRadioElement('', JText::_('JNO'), false);

				echo $vik->openControl(JText::_('VRTKMANAGEENTRYGROUP2'));
				echo $vik->radioYesNo('group_multiple', $yes, $no, false);
				echo $vik->closeControl();
				?>

				<!-- GROUP MINIMUM - Number -->
				<?php echo $vik->openControl(JText::_('VRTKMANAGEENTRYGROUP3')); ?>
					<input type="number" id="group_min_toppings" class="field" value="0" min="0" max="9999" step="1" />
				<?php echo $vik->closeControl(); ?>

				<!-- GROUP MAXIMUM - Number -->
				<?php echo $vik->openControl(JText::_('VRTKMANAGEENTRYGROUP4')); ?>
					<input type="number" id="group_max_toppings" class="field" value="1" min="1" max="9999" step="1" />
				<?php echo $vik->closeControl(); ?>

				<!-- GROUP USE QUANTITY - Checkbox -->
				<?php
				$yes = $vik->initRadioElement('', JText::_('JYES'), true);
				$no  = $vik->initRadioElement('', JText::_('JNO'), false);

				$help = $vik->createPopover(array(
					'title'   => JText::_('VRTKMANAGEENTRYGROUP6'),
					'content' => JText::_('VRTKMANAGEENTRYGROUP6_DESC'),
				));

				echo $vik->openControl(JText::_('VRTKMANAGEENTRYGROUP6') . $help, 'show-with-multiple');
				echo $vik->radioYesNo('group_use_quantity', $yes, $no, false);
				echo $vik->closeControl();
				?>
			</div>

		<?php echo $vik->bootEndTab(); ?>

		<?php echo $vik->bootAddTab('tkgroup', 'tkgroup_toppings', JText::_('VRMENUTAKEAWAYTOPPINGS')); ?>

			<div class="inspector-fieldset">

				<!-- TOPPINGS SELECTION - Select -->
				<div class="control-group">
					<div class="vrtk-toppings-list">
						<?php
						foreach ($this->toppings as $separator)
						{
							JHtml::_('vrehtml.scripts.sortablelist', 'separatorList' . $separator->id, 'adminForm');

							?>
							<table class="inspector-selection-table" id="separatorList<?php echo $separator->id; ?>">

								<thead>
									<tr>
										<th width="8%" style="text-align: left;">
											<input type="checkbox" class="toppings-group-checkbox" id="separator-topping-checkbox-<?php echo $separator->id; ?>" />
										</th>
										
										<th width="52%" style="text-align: left;">
											<label for="separator-topping-checkbox-<?php echo $separator->id; ?>">
												<strong><?php echo $separator->title; ?></strong>
											</label>
										</th>
										
										<th width="40%" style="text-align: left;">
											<?php echo JText::_('VRMANAGETKTOPPING2'); ?>
										</th>
									</tr>
								</thead>

								<tbody>
									<?php
									foreach ($separator->toppings as $topping)
									{
										?>
										<tr data-id="<?php echo $topping->id; ?>" data-ordering="<?php echo $topping->ordering; ?>">
											<td>
												<input type="checkbox" value="<?php echo $topping->id; ?>" class="topping-checkbox" id="topping-check-<?php echo $topping->id; ?>" />
												<input type="hidden" value="0" class="topping-assoc" />
											</td>

											<td>
												<label for="topping-check-<?php echo $topping->id; ?>">
													<strong><?php echo $topping->name; ?></strong>
												</label>
											</td>

											<td>
												<div class="input-prepend currency-field">
													<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>
													<input type="number" value="0.0" size="6" min="-999999" max="999999" step="any" readonly class="topping-rate" />
												</div>

												<input type="hidden" value="<?php echo $topping->price; ?>" class="topping-default-rate" />
											</td>
										</tr>
										<?php
									}
									?>
								</tbody>

							</table>
							<?php
						}
						?>
					</div>
				</div>
			</div>

		<?php echo $vik->bootEndTab(); ?>

	<?php echo $vik->bootEndTabSet(); ?>

	<input type="hidden" id="group_id" class="field" value="" />

</div>

<?php
JText::script('VRTKGROUPVARPLACEHOLDER');
JText::script('VRTKTOPPINGSPLACEHOLDER');
?>

<script>

	var groupValidator = new VikFormValidator('#inspector-group-form');

	var NAVIGATION_POOL = {};

	jQuery(document).ready(function() {

		jQuery('#group_variation').select2({
			placeholder: Joomla.JText._('VRTKGROUPVARPLACEHOLDER'),
			allowClear: true,
			width: '100%',
		});

		jQuery('input[name="group_multiple"]').on('change', function() {
			if (jQuery(this).is(':checked')) {
				jQuery('#group_min_toppings, #group_max_toppings')
					.prop('readonly', false);

				jQuery('.show-with-multiple').show();
			} else {
				jQuery('#group_min_toppings, #group_max_toppings')
					.val(1)
					.prop('readonly', true);

				jQuery('.show-with-multiple').hide();
			}
		});

		jQuery('.toppings-group-checkbox').on('change', function() {
			// fetch new status
			var checked = jQuery(this).is(':checked');

			var table = jQuery(this).closest('.inspector-selection-table');

			// toggle all table checkboxes
			table.find('.topping-checkbox')
				.prop('checked', checked);

			// toggle readonly for inputs
			table.find('tbody input[type="number"]')
				.prop('readonly', checked ? false : true);
		});

		jQuery('.topping-checkbox').on('change', function() {
			// fetch new status
			var checked = jQuery(this).is(':checked');

			// toggle readonly for inputs
			jQuery(this).closest('tr')
				.find('input[type="number"]')
					.prop('readonly', checked ? false : true);

			var table = jQuery(this).closest('.inspector-selection-table');

			// check if there are any unchecked fields
			var has_unchecked = table.find('.topping-checkbox:not(:checked)').length ? true : false;

			// toggle group checkbox status
			table.find('.toppings-group-checkbox')
				.prop('checked', has_unchecked ? false : true);
		});

		// validate min/max toppings
		groupValidator.addCallback(function() {
			// get inputs
			var minInput = jQuery('#group_min_toppings');
			var maxInput = jQuery('#group_max_toppings');

			groupValidator.unsetInvalid(minInput);
			groupValidator.unsetInvalid(maxInput);

			// get minimum
			var min = parseInt(minInput.val());

			// make sure the amount is a number
			if (isNaN(min) || min < 0) {
				groupValidator.setInvalid(minInput);

				return false;
			}

			// get maximum
			var max = parseInt(maxInput.val());

			// make sure the amount is a number
			if (isNaN(max) || max < 1) {
				groupValidator.setInvalid(maxInput);

				return false;
			}

			// make sure the minimum is not higher than the maximum
			if (min > max) {
				groupValidator.setInvalid(minInput);
				groupValidator.setInvalid(maxInput);

				return false;
			}

			return true;
		});

		// keep track of active tabs
		jQuery('#tkgroupTabs a[href^="#tkgroup_"]').on('click', function() {
			var id = parseInt(jQuery('#group_id').val());

			if (id) {
				var href = jQuery(this).attr('href');

				NAVIGATION_POOL[id] = href;
			}
		});

	});

	function fillEntryGroupForm(data) {
		// update title
		if (data.title === undefined) {
			data.title = '';
		}

		jQuery('#group_title').val(data.title);

		groupValidator.unsetInvalid(jQuery('#group_title'));

		// update description
		if (data.description === undefined) {
			data.description = '';
		}

		jQuery('#group_description').val(data.description);

		// update variation
		if (!data.id_variation) {
			data.id_variation = '';
		}

		jQuery('#group_variation').select2('val', data.id_variation);

		// update multiple
		var multipleInput = jQuery('input[name="group_multiple"]');

		if (data.multiple === undefined) {
			data.multiple = true;
		} else if (('' + data.multiple).match(/^[\d]+$/)) {
			data.multiple = parseInt(data.multiple);
		}

		if (multipleInput.attr('type') == 'checkbox') {
			multipleInput.prop('checked', data.multiple ? true : false);
		} else {
			multipleInput.val(data.multiple ? 1 : 0);
		}

		// update minimum toppings
		if (data.min_toppings === undefined) {
			data.min_toppings = 0;
		}

		jQuery('#group_min_toppings')
			.val(data.multiple ? data.min_toppings : 1)
			.prop('readonly', !data.multiple);

		// update maximum toppings
		if (data.max_toppings === undefined) {
			data.max_toppings = 1;
		}

		jQuery('#group_max_toppings')
			.val(data.multiple ? data.max_toppings : 1)
			.prop('readonly', !data.multiple);

		// update use quantity
		if (data.use_quantity === undefined) {
			data.use_quantity = 0;
		} else {
			data.use_quantity = parseInt(data.use_quantity);
		}

		var useQuantityInput = jQuery('input[name="group_use_quantity"]');

		if (useQuantityInput.attr('type') == 'checkbox') {
			useQuantityInput.prop('checked', data.use_quantity ? true : false);
		} else {
			useQuantityInput.val(data.use_quantity ? 1 : 0);
		}

		if (data.multiple) {
			jQuery('.show-with-multiple').show();
		} else {
			jQuery('.show-with-multiple').hide();
		}

		// update ID
		if (data.id === undefined) {
			data.id = 0;
		}

		jQuery('#group_id').val(data.id);

		// uncheck all toppings
		jQuery('.toppings-group-checkbox').prop('checked', false).trigger('change');
		// unset all associations
		jQuery('.topping-assoc').val(0);
		// restore all rates
		jQuery('.topping-default-rate').each(function() {
			// jQuery(this).prev('.topping-rate').val(jQuery(this).val());
			jQuery(this).closest('tr').find('.topping-rate').val(jQuery(this).val());
		});

		// update toppings
		if (data.toppings === undefined) {
			data.toppings = [];
		}

		for (var i = 0; i < data.toppings.length; i++) {
			var topping = data.toppings[i];

			// find topping row
			var tr = jQuery('tr[data-id="' + topping.id + '"]');

			// set association ID
			tr.find('.topping-assoc').val(topping.id_assoc);

			// check topping
			tr.find('.topping-checkbox').prop('checked', true).trigger('change');

			// update rate
			tr.find('.topping-rate').val(topping.rate);
		}

		// set active tab
		if (data.id && NAVIGATION_POOL.hasOwnProperty(data.id)) {
			jQuery('a[href="' + NAVIGATION_POOL[data.id] + '"]').trigger('click');
		} else {
			// fallback to default details tab
			jQuery('a[href="#tkgroup_details"]').trigger('click');
		}

		restoreToppingsOrdering(data.toppings);
	}

	function getEntryGroupData() {
		var data = {};

		// set ID
		data.id = jQuery('#group_id').val();

		// set title
		data.title = jQuery('#group_title').val();

		// set description
		data.description = jQuery('#group_description').val();

		// set variation
		data.id_variation = jQuery('#group_variation').val();

		if (!data.id_variation) {
			data.id_variation = 0;
		}

		// set multiple
		if (jQuery('input[name="group_multiple"]').attr('type') == 'checkbox') {
			data.multiple = jQuery('input[name="group_multiple"]').is(':checked') ? 1 : 0;
		} else {
			data.multiple = parseInt(jQuery('input[name="group_multiple"]').val());
		}

		if (data.multiple) {
			// set minimum toppings
			data.min_toppings = parseInt(jQuery('#group_min_toppings').val());

			// set maximum toppings
			data.max_toppings = parseInt(jQuery('#group_max_toppings').val());

			// set use quantity
			if (jQuery('input[name="group_use_quantity"]').attr('type') == 'checkbox') {
				data.use_quantity = jQuery('input[name="group_use_quantity"]').is(':checked') ? 1 : 0;
			} else {
				data.use_quantity = parseInt(jQuery('input[name="group_use_quantity"]').val());
			}
		} else {
			data.min_toppings = 1;
			data.max_toppings = 1;
			data.use_quantity = 0;
		}

		// set toppings
		data.toppings = [];

		var usedToppings = [];

		jQuery('.topping-checkbox:checked').each(function() {
			// get parent row
			var row = jQuery(this).closest('tr');

			var id_top = parseInt(jQuery(this).val());

			// Do not take topping if it was already added.
			// Duplicate toppings might occur when clicking the
			// save button quickly after rearranging the records.
			if (usedToppings.indexOf(id_top) == -1) {
				// register topping
				data.toppings.push({
					id:       id_top,
					id_assoc: row.find('.topping-assoc').val(),
					rate:     row.find('.topping-rate').val(),
				});

				// mark topping as already used
				usedToppings.push(id_top);
			}
		});

		return data;
	}

	function restoreToppingsOrdering(toppings) {
		var lookup = {};

		// create toppings ordering lookup
		for (var i = 0; i < toppings.length; i++) {
			lookup[toppings[i].id] = i + 1;
		}

		jQuery('#inspector-group-form').find('.inspector-selection-table').each(function() {

			var tableBody = jQuery(this).find('tbody');

			jQuery(tableBody).children().detach().sort(function(a, b) {
				// get IDs
				var aID = jQuery(a).data('id');
				var bID = jQuery(b).data('id');

				var x, y;

				// get values to compare
				if (lookup.hasOwnProperty(aID) && lookup.hasOwnProperty(bID)) {
					// compare assoc ordering
					x = lookup[aID];
					y = lookup[bID];
				} else if (!lookup.hasOwnProperty(aID) && !lookup.hasOwnProperty(bID)) {
					// compare toppings ordering
					x = parseInt(jQuery(a).data('ordering'));
					y = parseInt(jQuery(b).data('ordering'));
				} else {
					// push unchecked toppings down (higher value)
					x = lookup.hasOwnProperty(aID) ? 0 : 1;
					y = lookup.hasOwnProperty(bID) ? 0 : 1;
				}

				if (x < y) {
					// A is lower than B
					return -1;
				} else if (x > y) {
					// A is higher than B
					return 1;
				}

				return 0;
			}).appendTo(tableBody);

		});
	}

</script>
