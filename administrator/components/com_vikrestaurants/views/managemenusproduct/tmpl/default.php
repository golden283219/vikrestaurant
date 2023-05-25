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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.fancybox');

$product = $this->product;

$currency = VREFactory::getCurrency();

$vik = VREApplication::getInstance();

$editor = $vik->getEditor();

?>

<form name="adminForm" action="index.php" method="post" enctype="multipart/form-data" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">

			<div class="row-fluid">
			
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRMENUPRODFIELDSET1')); ?>
						
						<!-- NAME - Text -->
						<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT2') . '*'); ?>
							<input type="text" name="name" class="required" value="<?php echo $this->escape($product->name); ?>" size="40" />
						<?php echo $vik->closeControl(); ?>
				
						<!-- PRICE - Number -->
						<?php echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT4')); ?>
							<div class="input-prepend currency-field">
								<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>

								<input type="number" name="price" value="<?php echo $product->price; ?>" min="0" max="99999999" step="any" />
							</div>
						<?php echo $vik->closeControl(); ?>
						
						<?php if ($product->hidden == 0) { ?>

							<!-- PUBLISHED - Radio Button -->
							<?php
							$elem_yes = $vik->initRadioElement('', JText::_('JYES'), $product->published == 1);
							$elem_no = $vik->initRadioElement('', JText::_('JNO'), $product->published == 0);
							
							echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT6'));
							echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
							echo $vik->closeControl();
							?>
							
							<!-- IMAGE - File -->
							<?php
							echo $vik->openControl(JText::_('VRMANAGEMENUSPRODUCT5'));
							echo JHtml::_('vrehtml.mediamanager.field', 'image', $product->image);
							echo $vik->closeControl();
							?>

						<?php } ?>

						<!-- TAGS - Select -->
						<?php
						$tags = JHtml::_('vikrestaurants.tags', 'products');

						echo $vik->openControl(JText::_('VRTAGS')); ?>
							<select name="tags[]" id="vr-tags-select" multiple>
								<?php echo JHtml::_('select.options', $tags, 'name', 'name', $product->tags); ?>
							</select>
						<?php echo $vik->closeControl(); ?>
						
					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<?php
			/**
			 * Trigger event to display custom HTML.
			 * In case it is needed to include any additional fields,
			 * it is possible to create a plugin and attach it to an event
			 * called "onDisplayViewMenusproduct". The event method receives the
			 * view instance as argument.
			 *
			 * @since 1.8
			 */
			$custom = $this->onDisplayManageView();

			if ($custom)
			{
				?>
				<div class="row-fluid">
					<div class="span12">
						<?php
						echo $vik->openFieldset(JText::_('VRE_CUSTOM_FIELDSET'));
						echo $custom;
						echo $vik->closeFieldset();
						?>
					</div>
				</div>
				<?php
			}
			?>

			<div class="row-fluid">
				<?php echo $this->loadTemplate('variations'); ?>
			</div>
			
		</div>
		
		<div class="span6">
			<?php
			echo $vik->openFieldset(JText::_('VRMANAGEMENUSPRODUCT3'));
			echo $editor->display('description', $product->description, 400, 200, 70, 20);
			echo $vik->closeFieldset();
			?>
		</div>

	<?php echo $vik->closeCard(); ?>

	<?php if ($product->hidden == 1) { ?>
		<input type="hidden" name="hidden" value="1" />
	<?php } ?>
	
	<input type="hidden" name="id" value="<?php echo $product->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
$footer  = '<button type="button" class="btn btn-success" id="product-option-save">' . JText::_('JAPPLY') . '</button>';
$footer .= '<button type="button" class="btn btn-danger" id="product-option-delete" style="float:right;">' . JText::_('VRDELETE') . '</button>';

// render inspector to manage product variations
echo JHtml::_(
	'vrehtml.inspector.render',
	'product-option-inspector',
	array(
		'title'       => JText::_('VRE_ADD_VARIATION'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => $footer,
		'width'       => 400,
	),
	$this->loadTemplate('variation_modal')
);

JText::script('VRMANAGETKMENU4');
JText::script('VRE_PRODUCT_INC_PRICE_SHORT');
?>

<script>

	jQuery(document).ready(function() {

		jQuery('#vr-tags-select').select2({
			allowClear: true,
			width: '100%',
		});

	});

	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate()) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

</script>
