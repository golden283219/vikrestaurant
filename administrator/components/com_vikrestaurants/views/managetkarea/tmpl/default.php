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

JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.googlemaps');

$area = $this->area;

$vik = VREApplication::getInstance();

$currency = VREFactory::getCurrency();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>

		<div class="row-fluid">

			<div class="span12">

				<div class="row-fluid">

					<!-- DETAILS FIELDSET -->

					<div class="span6">
						<?php echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET1')); ?>
							
							<!-- NAME - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGETKAREA1') . '*'); ?>
								<input type="text" name="name" class="required" value="<?php echo $this->escape($area->name); ?>" size="40" />
							<?php echo $vik->closeControl(); ?>

							<!-- TYPE - Dropdown -->
							<?php
							$elements = array(
								JHtml::_('select.option', '', ''),
							);

							for ($i = 1; $i <= 4; $i++)
							{
								$elements[] = JHtml::_('select.option', $i, JText::_('VRTKAREATYPE' . $i));
							}

							$help = $vik->createPopover(array(
								'title'   => JText::_('VRMANAGETKAREA2'),
								'content' => JText::_('VRMANAGETKAREA2_HELP'),
							));
							
							echo $vik->openControl(JText::_('VRMANAGETKAREA2') . '*' . $help); ?>
								<select name="type" id="vr-type-sel" class="required">
									<?php echo JHtml::_('select.options', $elements, 'value', 'text', $area->type); ?>
								</select>
							<?php echo $vik->closeControl(); ?>
							
							<!-- CHARGE - Number -->
							<?php
							$help = $vik->createPopover(array(
								'title'   => JText::_('VRMANAGETKAREA4'),
								'content' => JText::_('VRMANAGETKAREA4_HELP'),
							));

							echo $vik->openControl(JText::_('VRMANAGETKAREA4') . $help); ?>
								<div class="input-prepend currency-field">
									<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>
									
									<input type="number" name="charge" value="<?php echo $area->charge; ?>" size="6" min="-999999" max="999999" step="any" />
								</div>
							<?php echo $vik->closeControl(); ?>

							<!-- MIN COST - Number -->
							<?php
							$help = $vik->createPopover(array(
								'title'   => JText::_('VRMANAGETKAREA18'),
								'content' => JText::_('VRMANAGETKAREA18_HELP'),
							));

							echo $vik->openControl(JText::_('VRMANAGETKAREA18')); ?>
								<div class="input-prepend currency-field">
									<button type="button" class="btn"><?php echo $currency->getSymbol(); ?></button>
									
									<input type="number" name="min_cost" value="<?php echo $area->min_cost; ?>" size="6" min="-999999" max="999999" step="any" />
								</div>
							<?php echo $vik->closeControl(); ?>
							
							<!-- PUBLISHED - Radio Button -->
							<?php
							$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $area->published);
							$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$area->published);
							
							echo $vik->openControl(JText::_('VRMANAGETKAREA3'));
							echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
							echo $vik->closeControl();
							?>

							<!-- CUSTOM -->

							<?php
							/**
							 * Trigger event to display custom HTML.
							 * In case it is needed to include any additional fields,
							 * it is possible to create a plugin and attach it to an event
							 * called "onDisplayViewTkarea". The event method receives the
							 * view instance as argument.
							 *
							 * @since 1.8
							 */
							echo $this->onDisplayManageView();
							?>
						
						<?php echo $vik->closeFieldset(); ?>
					</div>

					<!-- ATTRIBUTES FIELDSET -->

					<div class="span6" id="vr-attributes-fieldset" style="<?php echo ($area->type == 1 || $area->type == 2 ? '' : 'display: none'); ?>">
						<?php echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET3')); ?>

							<!-- COLOR - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGETKAREA10')); ?>
								<div class="input-append">
									<input type="text" name="color" id= "vrattrcolor" value="<?php echo (isset($area->attributes->color) ? $area->attributes->color : '#FF0000'); ?>" class="vr-attribute-field" readonly />

									<button type="button" class="btn" id="vrcolorpicker">
										<i class="fas fa-eye-dropper"></i>
									</button>
								</div>
							<?php echo $vik->closeControl(); ?>

							<!-- STROKE COLOR - Text -->
							<?php echo $vik->openControl(JText::_('VRMANAGETKAREA14')); ?>
								<div class="input-append">
									<input type="text" name="strokecolor" id= "vrattrstrokecolor" value="<?php echo (isset($area->attributes->strokecolor) ? $area->attributes->strokecolor : '#FF0000'); ?>" class="vr-attribute-field" readonly />
								
									<button type="button" class="btn" id="vrstrokecolorpicker">
										<i class="fas fa-eye-dropper"></i>
									</button>
								</div>
							<?php echo $vik->closeControl(); ?>

							<!-- STROKE WEIGHT - Number -->
							<?php echo $vik->openControl(JText::_('VRMANAGETKAREA15')); ?>
								<input type="number" name="strokeweight" id= "vrattrstrokeweight" value="<?php echo (isset($area->attributes->strokeweight) ? $area->attributes->strokeweight : 2); ?>" class="vr-attribute-field" min="0" max="10" />
							<?php echo $vik->closeControl(); ?>

							<!-- DISPLAY SHAPES - Radio Button -->
							<?php
							// display all shapes only in case there are 2 or more existing delivery areas
							// or in case we are creating a new area and there is another existing record
							if (count($this->shapes) > 1 || (count($this->shapes) == 1 && !$area->id))
							{
								$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), false, 'onclick="fillMapShapes(true);"');
								$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), true, 'onclick="fillMapShapes(false);"');

								$help = $vik->createPopover(array(
									'title'   => JText::_('VRMANAGETKAREA12'),
									'content' => JText::_('VRMANAGETKAREA12_HELP'),
								));
								
								echo $vik->openControl(JText::_('VRMANAGETKAREA12') . $help);
								echo $vik->radioYesNo('display_shapes', $elem_yes, $elem_no, false);
								echo $vik->closeControl();
							}
							?>

						<?php echo $vik->closeFieldset(); ?>
					</div>

					<!-- ZIP CONTENTS FIELDSET -->

					<div class="span6 vr-delivery-contents" id="vr-contents-fieldset3" style="<?php echo ($area->type == 3 ? '' : 'display: none'); ?>">
						<?php
						echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET2'), 'form-horizontal');
						echo $this->loadTemplate('zip');
						echo $vik->closeFieldset();
						?>
					</div>

					<!-- CITY CONTENTS FIELDSET -->

					<div class="span6 vr-delivery-contents" id="vr-contents-fieldset4" style="<?php echo ($area->type == 4 ? '' : 'display: none'); ?>">
						<?php
						echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET2'), 'form-horizontal');
						echo $this->loadTemplate('city');
						echo $vik->closeFieldset();
						?>
					</div>

				</div>
			</div>

		</div>

		<div class="row-fluid">

			<div class="span12">
				<div class="row-fluid">

					<!-- MAP FIELDSET -->

					<div class="span6" id="vr-map-fieldset" style="<?php echo (($area->type == 1 || $area->type == 2) ? '' : 'display: none'); ?>">
						<?php echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET4'), 'form-horizontal'); ?>
							<div class="control-group">
								<div id="polygon-googlemap" style="width:100%;height:500px;<?php echo $area->type == 1 ? '' : 'display:none'; ?>"></div>
								<div id="circle-googlemap" style="width:100%;height:500px;<?php echo $area->type == 2 ? '' : 'display:none'; ?>"></div>
								<div id="google-auth-error" style="display:none;">
									<?php echo $vik->alert(JText::_('VRE_GOOGLE_API_KEY_ERROR')); ?>
								</div>
							</div>
						<?php echo $vik->closeFieldset(); ?>
					</div>

					<!-- POLYGON CONTENTS FIELDSET -->

					<div class="span6 vr-delivery-contents" id="vr-contents-fieldset1" style="<?php echo ($area->type == 1 ? '' : 'display: none'); ?>">
						<?php
						echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET2'), 'form-horizontal');
						echo $this->loadTemplate('polygon');
						echo $vik->closeFieldset();
						?>
					</div>

					<!-- CIRCLE CONTENTS FIELDSET -->

					<div class="span6 vr-delivery-contents" id="vr-contents-fieldset2" style="<?php echo ($area->type == 2 ? '' : 'display: none'); ?>">
						<?php
						echo $vik->openFieldset(JText::_('VRTKAREAFIELDSET2'), 'form-horizontal');
						echo $this->loadTemplate('circle');
						echo $vik->closeFieldset();
						?>
					</div>

				</div>
			</div>

		</div>

		<div class="row-fluid">

			<div class="span12" id="vr-polygon-legend" style="<?php echo ($area->type == 1 ? '' : 'display: none;'); ?>">
				<div class="vr-deliveryarea-legend">

					<span>
						<i class="fas fa-ellipsis-v big"></i> <?php echo JText::_('VRTKAREALEGEND1'); ?>
					</span>

					<span>
						<i class="fas fa-map-marker-alt big"></i> <?php echo JText::_('VRTKAREALEGEND2'); ?>
					</span>

					<span>
						<i class="fas fa-dot-circle big"></i> <?php echo JText::_('VRTKAREALEGEND3'); ?>
					</span>

					<span>
						<i class="fas fa-location-arrow big"></i> <?php echo JText::_('VRTKAREALEGEND4'); ?>
					</span>

					<span>
						<i class="fas fa-times big"></i> <?php echo JText::_('VRTKAREALEGEND5'); ?>
					</span>

				</div>
			</div>

		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id" value="<?php echo $area->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<!-- HIDDEN LINKS -->

<a href="index.php?option=com_vikrestaurants&view=editconfig#googleapikey" id="google-error-link" style="display:none;"></a>

<?php
JText::script('VRE_FILTER_SELECT_TYPE');
?>

<script type="text/javascript">

	var MAP_SHAPES = [];

	jQuery(document).ready(function(){

		jQuery('#vr-type-sel').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_TYPE'),
			allowClear: false,
			width: 300,
		});

		jQuery('#vr-type-sel').on('change', function() {
			var val = jQuery(this).val();

			if (val == 1 || val == 2) {
				jQuery('#vr-attributes-fieldset, #vr-map-fieldset').show();
			} else if (val == 3 || val == 4) {
				jQuery('#vr-attributes-fieldset, #vr-map-fieldset').hide();
			}

			if (val == 2) {
				jQuery('#circle-googlemap').show();

				validator.registerFields('.vr-circle-field');
			} else {
				jQuery('#circle-googlemap').hide();

				validator.unregisterFields('.vr-circle-field');
			}

			jQuery('.vr-delivery-contents').hide();
			jQuery('#vr-contents-fieldset' + val).show();

			if (val == 1) {
				jQuery('#polygon-googlemap').show();

				jQuery('#vr-polygon-legend').show();
			} else {
				jQuery('#polygon-googlemap').hide();

				jQuery('#vr-polygon-legend').hide();
			}
		});

		// colorpicker

		var COLOR_TMP = null;

		jQuery('#vrcolorpicker').ColorPicker({
			color: jQuery('#vrattrcolor').val(),
			onShow: function() {
				COLOR_TMP = jQuery('#vrattrcolor').val();
			},
			onChange: function (hsb, hex, rgb) {
				jQuery('#vrattrcolor').val('#' + hex.toUpperCase());
			},
			onHide: function() {
				if (jQuery('#vrattrstrokecolor').val() == COLOR_TMP) {
					jQuery('#vrattrstrokecolor').val(jQuery('#vrattrcolor').val());
				}

				jQuery('#vrattrcolor').trigger('change');
			},
		});

		jQuery('#vrstrokecolorpicker').ColorPicker({
			color: jQuery('#vrattrstrokecolor').val(),
			onChange: function (hsb, hex, rgb) {
				jQuery('#vrattrstrokecolor').val('#' + hex.toUpperCase());
			},
			onHide: function() {
				jQuery('#vrattrstrokecolor').trigger('change');
			},
		});

		// create map shapes
		var shapes = <?php echo json_encode($this->shapes); ?>;

		for (var i = 0; i < shapes.length; i++) {

			if (shapes[i].id != <?php echo intval($area->id); ?>) {

				if (shapes[i].type == 1 ) {

					var coords = [];
					for (var j = 0; j < shapes[i].content.length; j++) {
						coords.push({
							lat: parseFloat(shapes[i].content[j].latitude),
							lng: parseFloat(shapes[i].content[j].longitude),
						});
					}

					MAP_SHAPES.push(
						new google.maps.Polygon({
							paths: coords,
							strokeColor: shapes[i].attributes.strokecolor,
							strokeOpacity: 0.5,
							strokeWeight: shapes[i].attributes.strokeweight,
							fillColor: shapes[i].attributes.color,
							fillOpacity: 0.20,
							clickable: false,
						})
					);

				} else if (shapes[i].type == 2) {

					MAP_SHAPES.push( 
						new google.maps.Circle({
							strokeColor: shapes[i].attributes.strokecolor,
							strokeOpacity: 0.5,
							strokeWeight: shapes[i].attributes.strokeweight,
							fillColor: shapes[i].attributes.color,
							fillOpacity: 0.20,
							center: new google.maps.LatLng(shapes[i].content.center.latitude, shapes[i].content.center.longitude),
							radius: shapes[i].content.radius * 1000,
							clickable: false,
						})
					);

				}

			}

		}
	});

	// MAP UTILS

	jQuery(document).ready(function(){

		jQuery('input[type="number"]').on('mousewheel', function() {
			jQuery(this).blur();
		});

		// display error in case Google fails the authentication
		jQuery(window).on('google.autherror', function() {
			// hide maps (forced)
			jQuery('#polygon-googlemap, #circle-googlemap')
				.css('display', 'none')
				.css('width', '0px')
				.css('height', '0px');

			// display alert
			jQuery('#google-auth-error')
				.show()
				.css('cursor', 'pointer')	
				.on('click', function(event) {
					// Go to configuration page and focus the API Key setting.
					// Extract HREF from link in order to use the correct platform URL.
					window.parent.location.href = jQuery('#google-error-link').attr('href');
				});
		});

	});

	var DISPLAY_SHAPES = 0;

	function fillMapShapes(status) {
		var type = parseInt(jQuery('#vr-type-sel').val());
		var map  = null;

		if (status !== undefined) {
			DISPLAY_SHAPES = status;
		}

		if (DISPLAY_SHAPES) {
			if (type == 1) {
				map = POLYGON_MAP;
			} else if (type == 2) {
				map = CIRCLE_MAP;
			}
		}

		for (var i = 0; i < MAP_SHAPES.length; i++) {
			MAP_SHAPES[i].setMap(map);
		}
	}

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
