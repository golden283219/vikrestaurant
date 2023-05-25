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

?>

<div class="inspector-form" id="inspector-widget-form">

	<div class="inspector-fieldset">

		<!-- WIDGET NAME - Text -->

		<?php
		$help = $vik->createPopover(array(
			'title'   => JText::_('VRE_WIDGET_NAME'),
			'content' => JText::_('VRE_WIDGET_NAME_DESC'),
		));

		echo $vik->openControl(JText::_('VRE_WIDGET_NAME') . $help); ?>
			<input type="text" name="widget_name" value="" placeholder="" class="field" />
		<?php echo $vik->closeControl(); ?>

		<!-- WIDGET CLASS - Select -->

		<?php
		$options = array(
			JHtml::_('select.option', '', ''),
		);

		foreach ($this->supported as $widget)
		{
			$options[] = JHtml::_('select.option', $widget->getName(), $widget->getTitle());
		}

		$help = $vik->createPopover(array(
			'title'   => JText::_('VRE_WIDGET_CLASS'),
			'content' => JText::_('VRE_WIDGET_CLASS_DESC'),
		));

		echo $vik->openControl(JText::_('VRE_WIDGET_CLASS') . '*' . $help); ?>
			<select name="widget_class" class="field required">
				<?php echo JHtml::_('select.options', $options); ?>
			</select>
		<?php echo $vik->closeControl(); ?>

		<!-- WIDGET POSITION - Select -->

		<?php
		$options = array(
			JHtml::_('select.option', '', ''),
		);

		foreach ($this->positions as $position)
		{
			$options[] = JHtml::_('select.option', $position, $position);
		}

		$help = $vik->createPopover(array(
			'title'   => JText::_('VRE_WIDGET_POSITION'),
			'content' => JText::_('VRE_WIDGET_POSITION_DESC'),
		));

		echo $vik->openControl(JText::_('VRE_WIDGET_POSITION') . '*' . $help); ?>
			<select name="widget_position" class="field required">
				<?php echo JHtml::_('select.options', $options); ?>
			</select>
		<?php echo $vik->closeControl(); ?>

		<!-- WIDGET SIZE - Select -->

		<?php
		$options = array(
			JHtml::_('select.option', '', ''),
			JHtml::_('select.option', 'extra-small', JText::_('VRE_WIDGET_SIZE_OPT_EXTRA_SMALL')),
			JHtml::_('select.option', 'small', JText::_('VRE_WIDGET_SIZE_OPT_SMALL')),
			JHtml::_('select.option', 'normal', JText::_('VRE_WIDGET_SIZE_OPT_NORMAL')),
			JHtml::_('select.option', 'large', JText::_('VRE_WIDGET_SIZE_OPT_LARGE')),
			JHtml::_('select.option', 'extra-large', JText::_('VRE_WIDGET_SIZE_OPT_EXTRA_LARGE')),
		);

		$help = $vik->createPopover(array(
			'title'   => JText::_('VRE_WIDGET_SIZE'),
			'content' => JText::_('VRE_WIDGET_SIZE_DESC'),
		));

		echo $vik->openControl(JText::_('VRE_WIDGET_SIZE') . $help); ?>
			<select name="widget_size" class="field">
				<?php echo JHtml::_('select.options', $options); ?>
			</select>
		<?php echo $vik->closeControl(); ?>

	</div>

	<?php
	foreach ($this->supported as $widget)
	{
		?>
		<div 
			class="inspector-fieldset widget-desc"
			data-name="<?php echo $widget->getName(); ?>"
			data-title="<?php echo $this->escape($widget->getTitle()); ?>"
			style="display:none;"
		>
			<?php
			// show widget description, if any
			$desc = $widget->getDescription();

			if ($desc)
			{
				echo $vik->alert($desc, 'info');
			}
			?>
		</div>
		<?php
	}
	?>

	<input type="hidden" name="widget_id" value="0" />

</div>

<?php
JText::script('VRE_WIDGET_SELECT_CLASS');
JText::script('VRE_WIDGET_SELECT_POSITION');
JText::script('VRE_WIDGET_SIZE_OPT_DEFAULT');
?>

<script>

	var widgetValidator = new VikFormValidator('#inspector-widget-form');

	jQuery(document).ready(function() {

		jQuery('#inspector-widget-form select[name="widget_class"]').select2({
			placeholder: Joomla.JText._('VRE_WIDGET_SELECT_CLASS'),
			allowClear: false,
		});

		jQuery('#inspector-widget-form select[name="widget_position"]').select2({
			placeholder: Joomla.JText._('VRE_WIDGET_SELECT_POSITION'),
			allowClear: false,
		});

		jQuery('#inspector-widget-form select[name="widget_size"]').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRE_WIDGET_SIZE_OPT_DEFAULT'),
			allowClear: true,
		});

		jQuery('#inspector-widget-form select[name="widget_class"]').on('change', function() {
			// hide all descriptions
			jQuery('#inspector-widget-form .widget-desc').hide();

			// get selected widget
			var widget = jQuery('#inspector-widget-form .widget-desc[data-name="' + jQuery(this).val() + '"]');

			// get name input
			var nameInput = jQuery('#inspector-widget-form input[name="widget_name"]');

			// set up placeholder
			nameInput.attr('placeholder', widget.data('title'));

			if (nameInput.val() == widget.data('title')) {
				// specified title is equals to the default one, unset it
				nameInput.val('');
			}

			// show description of selected widget
			widget.show();
		});

	});

	function setupWidgetData(data) {
		// fill ID
		jQuery('#inspector-widget-form input[name="widget_id"]').val(data.id ? data.id : 0);

		// fill name
		jQuery('#inspector-widget-form input[name="widget_name"]').val(data.name);

		// fill widget class
		data.widget = data.widget || data.class;

		jQuery('#inspector-widget-form select[name="widget_class"]').select2('val', data.widget ? data.widget : '').trigger('change');

		// fill widget position
		jQuery('#inspector-widget-form select[name="widget_position"]').select2('val', data.position ? data.position : '');

		// fill widget size
		jQuery('#inspector-widget-form select[name="widget_size"]').select2('val', data.size ? data.size : '');
	}

	function getWidgetData() {
		var data = {};

		// extract widget data
		jQuery('#inspector-widget-form')
			.find('input,select')
				.filter('[name^="widget_"]')
					.each(function() {
						var name  = jQuery(this).attr('name').replace(/^widget_/, '');
						var value = jQuery(this).val();

						data[name] = value;
					});

		// replicate CLASS in WIDGET property
		data.widget = data.class;

		return data;
	}

	function getDefaultWidget(widget) {
		// get widget
		var widget = jQuery('#inspector-widget-form .widget-desc[data-name="' + widget + '"]');

		var data = {
			name: widget.data('name'),
			title: widget.data('title'),
			description: widget.html(),
		};

		return data;
	}

	function addPositionOption(position) {
		jQuery('#inspector-widget-form select[name="widget_position"]').append('<option value="' + position + '">' + position + '</option>');
	}

</script>
