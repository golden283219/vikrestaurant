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

$deal = $this->deal;

// fetch product options
$options = array();
$options[0] = array(JHtml::_('select.option', '', ''));

foreach ($this->menus as $menu)
{
	if (!isset($options[$menu->title]))
	{
		$options[$menu->title] = array();
	}

	foreach ($menu->products as $product)
	{
		$options[$menu->title][] = JHtml::_('select.option', $product->id . ':0', $product->name);

		foreach ($product->options as $option)
		{
			$options[$menu->title][] = JHtml::_('select.option', $product->id . ':' . $option->id, $product->name . ' - ' . $option->name);
		}
	}
}

// all products select
$args = array(
	'list.attr'   => array('class' => 'vrtk-allprod-select'),
	'group.items' => null,
);

$all_prod_select = JHtml::_('select.groupedList', $options, 'allprod', $args);

// all gifts select
$args = array(
	'list.attr'   => array('class' => 'vrtk-allgift-select'),
	'group.items' => null,
);

$all_gift_select = JHtml::_('select.groupedList', $options, 'allgift', $args);

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

?>

<div class="deal-rule-fieldset" style="<?php echo $deal->type ? 'display:none;' : ''; ?>">
	<?php echo $vik->alert(JText::_('VRTKDEALTYPEDESC0')); ?>
</div>

<?php
foreach ($this->deals as $d)
{
	?>
	<div class="type-help" id="type-help-<?php echo $d->getID(); ?>" style="<?php echo $deal->type == $d->getID() ? '' : 'display:none;'; ?>">
		<?php
		echo $vik->alert($d->getDescription(), 'info');
		?>
	</div>
	<?php
}
?>

<!-- DEAL TYPE #1 : ABOVE ALL -->
		
<div class="row-fluid deal-rule-fieldset" id="deal-rule-1" style="<?php echo $deal->type == 1 ? '' : 'display:none;'; ?>">
	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>
	
			<!-- AMOUNT / PERCENT OR TOTAL - Number -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, '%'),
				JHtml::_('select.option', 2, $currency->getSymbol()),
			);
			
			echo $vik->openControl(JText::_('VRMANAGETKDEAL10'), 'multi-field no-margin-last-2'); ?>
				<input type="number" data-name="amount" value="<?php echo $deal->amount; ?>" min="0" step="any" />

				<select data-name="percentot" class="vr-dropdown-short">
					<?php echo JHtml::_('select.options', $elements, 'value', 'text', $deal->percentot); ?>
				</select>
			<?php echo $vik->closeControl(); ?>
			
			<!-- MIN QUANTITY - Number -->
			<?php echo $vik->openControl(JText::_('VRMANAGETKDEAL17')); ?>
				<input type="number" data-name="min_quantity" value="<?php echo $deal->min_quantity; ?>" min="1" />
			<?php echo $vik->closeControl(); ?>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
		
	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>

			<!-- TARGET FOOD - Form -->
			<?php
			echo $vik->openControl(JText::_('VRMANAGETKDEAL14'));
			echo $all_prod_select;
			echo $vik->closeControl();
			?>

			<div class="vrtk-food-container vrtk-reqfood-container">
				<?php
				$elements = array(
					JHtml::_('select.option', 1, JText::_('VRTKDEALTARGETOPT1')),
					JHtml::_('select.option', 0, JText::_('VRTKDEALTARGETOPT2')),
				);

				foreach ($deal->products as $prod)
				{
					?>
					<div class="vrtk-dealfood-row vrtk-entry-var">

						<input type="text" readonly value="<?php echo $prod->product_name . (!empty($prod->option_name) ? ' - ' . $prod->option_name : ''); ?>" size="32" class="form-control" />

						<select data-name="deal_food[required][]" class="vr-dropdown-medium">
							<?php echo JHtml::_('select.options', $elements, 'value', 'text', $prod->required); ?>
						</select>

						<span>x</span>
						<input type="number" data-name="deal_food[quantity][]" value="<?php echo $prod->quantity; ?>" min="1" max="999" step="1" class="form-control vrtkdealfoodquantity" />

						<input type="hidden" data-name="deal_food[id_prod_option][]" value="<?php echo $prod->id_product . ':' . $prod->id_option; ?>" class="vrtkdealfoodid" />

						<input type="hidden" data-name="deal_food[id][]" value="<?php echo $prod->id; ?>" class="vrtkrealid" />
						
						<a href="javascript: void(0);" class="trash-button-link" onClick="removeSelectedDealFood(this);">
							<i class="fas fa-times"></i>
						</a>
					</div>
					<?php 
				}
				?>
			</div>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
</div>
	
<!-- DEAL TYPE #2 : DISCOUNT ITEM -->
		
<div class="row-fluid deal-rule-fieldset" id="deal-rule-2" style="<?php echo $deal->type == 2 ? '' : 'display:none;'; ?>">
	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>
	
			<!-- AMOUNT / PERCENT OR TOTAL - Number -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, '%'),
				JHtml::_('select.option', 2, $currency->getSymbol()),
			);
			
			echo $vik->openControl(JText::_('VRMANAGETKDEAL10'), 'multi-field no-margin-last-2'); ?>
				<input type="number" data-name="amount" value="<?php echo $deal->amount; ?>" min="0" step="any" />

				<select data-name="percentot" class="vr-dropdown-short">
					<?php echo JHtml::_('select.options', $elements, 'value', 'text', $deal->percentot); ?>
				</select>
			<?php echo $vik->closeControl(); ?>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>

	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>
			
			<!-- TARGET FOOD - Form -->
			<?php
			echo $vik->openControl(JText::_('VRMANAGETKDEAL14'));
			echo $all_prod_select;
			echo $vik->closeControl();
			?>

			<div class="vrtk-food-container vrtk-reqfood-container">
				<?php
				foreach ($deal->products as $prod)
				{
					?>
					<div class="vrtk-dealfood-row vrtk-entry-var">
						<input type="text" readonly value="<?php echo $prod->product_name . (!empty($prod->option_name) ? ' - ' . $prod->option_name : ''); ?>" size="32" class="form-control" />
						
						<input type="hidden" data-name="deal_food[required][]" value="0" />
						
						<span>x</span>
						<input type="number" data-name="deal_food[quantity][]" value="<?php echo $prod->quantity; ?>" min="1" max="999" step="1" class="vrtkdealfoodquantity" />
						
						<input type="hidden" data-name="deal_food[id_prod_option][]" value="<?php echo $prod->id_product . ':' . $prod->id_option; ?>" class="vrtkdealfoodid" />
						
						<input type="hidden" data-name="deal_food[id][]" value="<?php echo $prod->id; ?>" class="vrtkrealid" />
						
						<a href="javascript: void(0);" class="trash-button-link" onClick="removeSelectedDealFood(this);">
							<i class="fas fa-times"></i>
						</a>
					</div>
					<?php 
				}
				?>
			</div>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
</div>
	
<!-- DEAL TYPE #3 : FREE ITEM WITH COMBINATION -->
		
<div class="row-fluid deal-rule-fieldset" id="deal-rule-3" style="<?php echo $deal->type == 3 ? '' : 'display:none;'; ?>">
	<div class="span6">

		<div class="row-fluid">
			<div class="span12">
				<?php echo $vik->openEmptyFieldset(); ?>
			
					<!-- AUTO INSERT - Radio Button -->
					<?php
					$elem_yes = $vik->initRadioElement('', JText::_('JYES'), $deal->auto_insert, 'onclick="changeAutoInsert(1);"');
					$elem_no  = $vik->initRadioElement('', JText::_('JNO'), !$deal->auto_insert, 'onclick="changeAutoInsert(0);"');
					
					echo $vik->openControl(JText::_('VRMANAGETKDEAL12'));
					echo $vik->radioYesNo('auto_insert_3', $elem_yes, $elem_no, false);
					?><input type="hidden" data-name="auto_insert" value="<?php echo $deal->auto_insert; ?>" /><?php
					echo $vik->closeControl();
					?>
					
					<!-- MIN QUANTITY - Number -->
					<?php echo $vik->openControl(JText::_('VRMANAGETKDEAL17')); ?>
						<input type="number" data-name="min_quantity" value="<?php echo $deal->min_quantity; ?>" min="0" />
					<?php echo $vik->closeControl(); ?>

				<?php echo $vik->closeEmptyFieldset(); ?>
			</div>

			<div class="span12">
				<?php echo $vik->openEmptyFieldset(); ?>
					
					<!-- GIFT FOOD - Form -->
					<?php
					echo $vik->openControl(JText::_('VRMANAGETKDEAL15'));
					echo $all_gift_select;
					echo $vik->closeControl();
					?>

					<div class="vrtk-food-container vrtk-giftfood-container">
						<?php
						foreach ($deal->gifts as $prod)
						{
							?>
							<div class="vrtk-dealfood-row vrtk-entry-var">
								<input type="text" readonly value="<?php echo $prod->product_name . (!empty($prod->option_name) ? ' - ' . $prod->option_name : ''); ?>" size="32" class="form-control" />

								<span>x</span>
								<input type="number" data-name="free_food[quantity][]" value="<?php echo $prod->quantity; ?>" min="1" max="999" step="1" class="vrtkdealfoodquantity" />
								
								<input type="hidden" data-name="free_food[id_prod_option][]" value="<?php echo $prod->id_product . ':' . $prod->id_option; ?>" class="vrtkdealfoodid" />
								
								<input type="hidden" data-name="free_food[id][]" value="<?php echo $prod->id; ?>" class="vrtkrealid" />

								<a href="javascript: void(0);" class="trash-button-link" onClick="removeSelectedFreeFood(this);">
									<i class="fas fa-times"></i>
								</a>
							</div>
							<?php 
						}
						?>
					</div>

				<?php echo $vik->closeEmptyFieldset(); ?>
			</div>
		</div>

	</div>

	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>
			
			<!-- TARGET FOOD - Form -->
			<?php
			echo $vik->openControl(JText::_('VRMANAGETKDEAL14'));
			echo $all_prod_select;
			echo $vik->closeControl();
			?>

			<div class="vrtk-food-container vrtk-reqfood-container">
				<?php
				$elements = array(
					JHtml::_('select.option', 1, JText::_('VRTKDEALTARGETOPT1')),
					JHtml::_('select.option', 0, JText::_('VRTKDEALTARGETOPT2')),
				);

				foreach ($deal->products as $prod)
				{
					?>
					<div class="vrtk-dealfood-row vrtk-entry-var">
						<input type="text" readonly value="<?php echo $prod->product_name . (!empty($prod->option_name) ? ' - ' . $prod->option_name : ''); ?>" size="32" class="form-control" />

						<select data-name="deal_food[required][]" class="vr-dropdown-medium">
							<?php echo JHtml::_('select.options', $elements, 'value', 'text', $prod->required); ?>
						</select>

						<span>x</span>
						<input type="number" data-name="deal_food[quantity][]" value="<?php echo $prod->quantity; ?>" min="1" max="999" step="1" step="1" class="vrtkdealfoodquantity" />
						
						<input type="hidden" data-name="deal_food[id_prod_option][]" value="<?php echo $prod->id_product . ':' . $prod->id_option; ?>" class="vrtkdealfoodid" />
						
						<input type="hidden" data-name="deal_food[id][]" value="<?php echo $prod->id; ?>" class="vrtkrealid" />
						
						<a href="javascript: void(0);" class="trash-button-link" onClick="removeSelectedDealFood(this);">
							<i class="fas fa-times"></i>
						</a>
					</div>
					<?php 
				}
				?>
			</div>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
</div>
	
<!-- DEAL TYPE #4 : FREE ITEM WITH TOTAL COST -->
		
<div class="row-fluid deal-rule-fieldset" id="deal-rule-4" style="<?php echo $deal->type == 4 ? '' : 'display:none;'; ?>">
	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>
	
			<!-- TOTAL COST - Number -->
			<?php echo $vik->openControl(JText::_('VRMANAGETKDEAL16')); ?>
				<div class="input-prepend currency-field">
					<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

					<input type="number" data-name="amount" value="<?php echo $deal->amount; ?>" min="0" max="99999999" step="any" />
				</div>
			<?php echo $vik->closeControl(); ?>
			
			<!-- AUTO INSERT - Radio Button -->
			<?php
			$elem_yes = $vik->initRadioElement('', $elem_yes->label, $deal->auto_insert, 'onclick="changeAutoInsert(1);"');
			$elem_no  = $vik->initRadioElement('', $elem_no->label, !$deal->auto_insert, 'onclick="changeAutoInsert(0);"');
			
			echo $vik->openControl(JText::_('VRMANAGETKDEAL12'));
			echo $vik->radioYesNo('auto_insert_4', $elem_yes, $elem_no, false);
			?><input type="hidden" data-name="auto_insert" value="<?php echo $deal->auto_insert; ?>" /><?php
			echo $vik->closeControl();
			?>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>

	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>
			
			<!-- GIFT FOOD - Form -->
			<?php
			echo $vik->openControl(JText::_('VRMANAGETKDEAL15'));
			echo $all_gift_select; 
			echo $vik->closeControl();
			?>

			<div class="vrtk-food-container vrtk-giftfood-container">
				<?php
				foreach ($deal->gifts as $prod)
				{
					?>
					<div class="vrtk-dealfood-row vrtk-entry-var">
						<input type="text" readonly value="<?php echo $prod->product_name . (!empty($prod->option_name) ? ' - ' . $prod->option_name : ''); ?>" size="32" class="control" />
						
						<span>x</span>
						<input type="number" data-name="free_food[quantity][]" value="<?php echo $prod->quantity; ?>" min="1" max="999" step="1" class="vrtkdealfoodquantity" />
						
						<input type="hidden" data-name="free_food[id_prod_option][]" value="<?php echo $prod->id_product . ':' . $prod->id_option; ?>" class="vrtkdealfoodid" />
						
						<input type="hidden" data-name="free_food[id][]" value="<?php echo $prod->id; ?>" class="vrtkrealid" />
						
						<a href="javascript: void(0);" class="trash-button-link" onClick="removeSelectedFreeFood(this);">
							<i class="fas fa-times"></i>
						</a>
					</div>
					<?php 
				}
				?>
			</div>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
</div>

<!-- DEAL TYPE #5 : COUPON -->
		
<div class="row-fluid deal-rule-fieldset" id="deal-rule-5" style="<?php echo $deal->type == 5 ? '' : 'display:none;'; ?>">
	<!-- do not show anything -->
</div>
	
<!-- DEAL TYPE #6 : DISCOUNT WITH TOTAL COST -->
		
<div class="row-fluid deal-rule-fieldset" id="deal-rule-6" style="<?php echo $deal->type == 6 ? '' : 'display:none;'; ?>">
	<div class="span6">
		<?php echo $vik->openEmptyFieldset(); ?>

			<!-- AMOUNT / PERCENT OR TOTAL - Number -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, '%'),
				JHtml::_('select.option', 2, $currency->getSymbol()),
			);
			
			echo $vik->openControl(JText::_('VRMANAGETKDEAL10'), 'multi-field no-margin-last-2'); ?>
				<input type="number" data-name="amount" value="<?php echo $deal->amount; ?>" min="0" step="any" />

				<select data-name="percentot" class="vr-dropdown-short">
					<?php echo JHtml::_('select.options', $elements, 'value', 'text', $deal->percentot); ?>
				</select>
			<?php echo $vik->closeControl(); ?>

			<!-- TOTAL COST - Number -->
			<?php echo $vik->openControl(JText::_('VRMANAGETKDEAL16')); ?>
				<div class="input-prepend currency-field">
					<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

					<input type="number" data-name="cart_tcost" value="<?php echo $deal->cart_tcost; ?>" min="0" max="99999999" step="any" />
				</div>
			<?php echo $vik->closeControl(); ?>

		<?php echo $vik->closeEmptyFieldset(); ?>
	</div>
</div>

<?php
JText::script('VRTKCARTOPTION4');
JText::script('VRTKDEALTARGETOPT1');
JText::script('VRTKDEALTARGETOPT2');
?>

<script>

	jQuery(document).ready(function(){

		jQuery('.vr-dropdown-short').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 75
		});

		jQuery('.vr-dropdown-medium').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 150
		});
		
		jQuery('.vrtk-allprod-select, .vrtk-allgift-select').select2({
			placeholder: Joomla.JText._('VRTKCARTOPTION4'),
			allowClear: true,
			width: 350,
		});
		
		jQuery('.vrtk-allprod-select').on('change', function() {
			pushSelectedDealFood(this);
		});
		
		jQuery('.vrtk-allgift-select').on('change', function() {
			pushSelectedFreeFood(this);
		});

	});

	function pushSelectedDealFood(select) {
		var id   = jQuery(select).val();
		var type = parseInt(jQuery('#vrtk-type-select').val());

		if (id.length == 0) {
			return;
		}

		var parent = jQuery(select).closest('.deal-rule-fieldset');
		
		var text = jQuery(select).find('option:selected').text();
		
		var elem_found = null;

		jQuery(parent).find('.vrtk-reqfood-container .vrtkdealfoodid').each(function() {
			if (jQuery(this).val() == id) {
				elem_found = this;
				return false;
			}
		});

		var params;

		if (type == 1 || type == 3) {
			params = '<select name="deal_food[required][]" class="vr-dropdown-medium">\n'+
					'<option value="1">' + Joomla.JText._('VRTKDEALTARGETOPT1') + '</option>\n'+
					'<option value="0">' + Joomla.JText._('VRTKDEALTARGETOPT2') + '</option>\n'+
				'</select>\n';
		} else if (type == 2) {
			params = '<input type="hidden" name="deal_food[required][]" value="0" />\n';
		}
		
		if (elem_found === null) {
			jQuery(parent).find('.vrtk-reqfood-container').append(
				'<div class="vrtk-dealfood-row vrtk-entry-var">\n'+
					'<input type="text" readonly value="' + text + '" size="32" class="form-control" />\n'+
					params + 
					'<span>x</span>\n'+
					'<input type="number" name="deal_food[quantity][]" value="1" min="1" max="999" step="1" class="form-control vrtkdealfoodquantity" />\n'+
					'<input type="hidden" name="deal_food[id_prod_option][]" value="' + id + '" class="vrtkdealfoodid" />\n'+
					'<input type="hidden" name="deal_food[id][]" value="0" class="vrtkrealid" />\n'+
					'<a href="javascript: void(0);" class="trash-button-link" onClick="removeSelectedDealFood(this);">\n'+
						'<i class="fas fa-times"></i>\n'+
					'</a>\n'+
				'</div>\n'
			);

			jQuery(parent).find('.vrtk-reqfood-container').find('.vrtk-dealfood-row').last().find('select').select2({
				minimumResultsForSearch: -1,
				allowClear: false,
				width: 150,
			});
		} else {
			var q_elem = jQuery(elem_found).parent().find('.vrtkdealfoodquantity');

			q_elem.val(parseInt(q_elem.val()) + 1);
		}
	}

	function removeSelectedDealFood(link) {
		var parent  = jQuery(link).closest('.vrtk-dealfood-row');
		var real_id = parseInt(parent.find('.vrtkrealid').val());

		if (real_id > 0) {
			jQuery('#adminForm').append('<input type="hidden" name="delete_deal_food[]" value="' + real_id + '" />');
		}

		parent.remove();
	}

	function pushSelectedFreeFood(select) {
		var id   = jQuery(select).val();
		var type = parseInt(jQuery('#vrtk-type-select').val());

		if (id.length == 0) {
			return;
		}
		
		var parent = jQuery(select).closest('.deal-rule-fieldset');
		
		var text = jQuery(select).find('option:selected').text();
		
		var elem_found = null;

		jQuery(parent).find('.vrtk-giftfood-container .vrtkdealfoodid').each(function() {
			if (jQuery(this).val() == id) {
				elem_found = this;
				return false;
			}
		});
		
		if (elem_found === null) {
			jQuery(parent).find('.vrtk-giftfood-container').append(
				'<div class="vrtk-dealfood-row vrtk-entry-var">\n'+
					'<input type="text" readonly value="' + text + '" size="32" class="form-control" />\n'+
					'<span>x</span>\n'+
					'<input type="number" name="free_food[quantity][]" value="1" min="1" max="999" step="1" class="form-control vrtkdealfoodquantity" />\n'+
					'<input type="hidden" name="free_food[id_prod_option][]" value="' + id + '" class="vrtkdealfoodid" />\n'+
					'<input type="hidden" name="free_food[id][]" value="0" class="vrtkrealid" />\n'+
					'<a href="javascript: void(0);" class="trash-button-link" onClick="removeSelectedFreeFood(this);">\n'+
						'<i class="fas fa-times"></i>\n'+
					'</a>\n'+
				'</div>\n'
			);
		} else {
			var q_elem = jQuery(elem_found).parent().find('.vrtkdealfoodquantity');
			q_elem.val(parseInt(q_elem.val()) + 1);
		}
	}

	function removeSelectedFreeFood(link) {
		var parent  = jQuery(link).closest('.vrtk-dealfood-row');
		var real_id = parseInt(parent.find('.vrtkrealid').val());

		if (real_id > 0) {
			jQuery('#adminForm').append('<input type="hidden" name="delete_free_food[]" value="' + real_id + '" />');
		}

		parent.remove();
	}

	function changeAutoInsert(is) {
		jQuery('input[name="auto_insert"]').val(is);
	}
	
</script>
