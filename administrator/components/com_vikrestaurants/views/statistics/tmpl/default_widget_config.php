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

$layout = new JLayoutFile('form.fields');

?>

<div class="inspector-form" id="inspector-config-form">

	<?php
	// iterate all positions
	foreach ($this->dashboard as $widgets)
	{
		// iterate position widgets
		foreach ($widgets as $widget)
		{
			?>
			<div
				class="inspector-fieldset"
				data-id="<?php echo $widget->getID(); ?>"
				data-widget="<?php echo $widget->getName(); ?>"
				style="display: none;">

				<h3><?php echo $widget->getTitle(); ?></h3>

				<?php
				// get widget description
				$desc = $widget->getDescription();

				if ($desc)
				{
					// display description before configuration form
					echo $vik->alert($desc, 'info');
				}

				// prepare layout data
				$data = array(
					'fields' => $widget->getForm(),
					'params' => $widget->getParams(),
					'prefix' => $widget->getName() . '_' . $widget->getID() . '_',
				);

				// display widget configuration
				echo $layout->render($data);
				?>

			</div>
			<?php
		}
	}
	?>

</div>

<script>

	jQuery(document).ready(function() {

		VikRenderer.chosen('.inspector-form', '100%');

	});

	function setupWidgetConfig(id) {
		jQuery('.inspector-fieldset').hide();
		jQuery('.inspector-fieldset[data-id="' + id + '"]').show();
	}

	function getWidgetConfig(id) {
		var config = {};

		var widget = jQuery('.inspector-fieldset[data-id="' + id + '"]').data('widget');

		jQuery('.inspector-fieldset[data-id="' + id + '"]')
			.find('input,select')
				.filter('[name^="' + widget + '_"]')
					.each(function() {
						var name = jQuery(this).attr('name').replace(new RegExp('^' + widget + '_' + id + '_'), '');

						if (jQuery(this).is(':checkbox')) {
							config[name] = jQuery(this).is(':checked') ? 1 : 0;
						} else {
							config[name] = jQuery(this).val();
						}
					});

		return config;
	}

</script>
