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

JHtml::_('vrehtml.assets.select2');

$review = $this->review;

$vik = VREApplication::getInstance();

$languages = VikRestaurants::getKnownLanguages();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">
	
	<?php echo $vik->openCard(); ?>

		<div class="span6">
			<?php echo $vik->openFieldset(JText::_('VRE_REVIEW_CARD_TITLE')); ?>
				
				<!-- TITLE - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGEREVIEW2') . '*'); ?>
					<input type="text" name="title" class="required" value="<?php echo $this->escape($review->title); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- USER - Dropdown -->
				<?php echo $vik->openControl(JText::_('VRMANAGEREVIEW10')); ?>
					<input type="hidden" name="jid" class="vr-users-select" value="<?php echo $review->jid ? $review->jid : ''; ?>" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- USER NAME - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGEREVIEW3') . '*'); ?>
					<input class="required" id="user-name" type="text" name="name" value="<?php echo $this->escape($review->name); ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- USER EMAIL - Text -->
				<?php echo $vik->openControl(JText::_('VRMANAGEREVIEW11') . '*'); ?>
					<input class="required" id="user-mail" type="text" name="email" value="<?php echo $review->email; ?>" size="40" />
				<?php echo $vik->closeControl(); ?>
				
				<!-- DATE - Date -->
				<?php
				echo $vik->openControl(JText::_('VRMANAGEREVIEW4'));

				$attr = array();
				$attr['showTime'] = true;

				echo $vik->calendar($review->timestamp, 'timestamp', 'timestamp', null, $attr);
				echo $vik->closeControl();
				?>
				
				<!-- RATING - Dropdown -->
				<?php 
				$options = array();

				for ($i = 5; $i >= 1; $i--)
				{
					$options[] = JHtml::_('select.option', $i, $i . ' ' . JText::_($i > 1 ? 'VRSTARS' : 'VRSTAR'));
				}
				
				echo $vik->openControl(JText::_('VRMANAGEREVIEW5') . '*'); ?>
					<select name="rating" id="vr-rating-sel" class="medium">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $review->rating); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
				<!-- PUBLISHED - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', JText::_('VRYES'), $review->published);
				$elem_no  = $vik->initRadioElement('', JText::_('VRNO'), !$review->published);
				
				echo $vik->openControl(JText::_('VRMANAGEREVIEW7'));
				echo $vik->radioYesNo('published', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>

				<!-- VERIFIED - Radio Button -->
				<?php
				$elem_yes = $vik->initRadioElement('', $elem_yes->label, $review->verified);
				$elem_no  = $vik->initRadioElement('', $elem_no->label, !$review->verified);
				
				echo $vik->openControl(JText::_('VRMANAGEREVIEW12'));
				echo $vik->radioYesNo('verified', $elem_yes, $elem_no, false);
				echo $vik->closeControl();
				?>
				
				<!-- PRODUCT - Dropdown -->
				<?php
				$options = array();
				$options[0] = array(JHtml::_('select.option', '', ''));

				foreach ($this->menus as $menu)
				{
					$options[$menu->title] = array();

					foreach ($menu->items as $item)
					{
						$options[$menu->title][] = JHtml::_('select.option', $item->id, $item->name);
					}
				}

				$args = array(
					'id' 			=> 'vr-products-sel',
					'list.attr' 	=> array('class' => 'required'),
					'group.items' 	=> null,
					'list.select'	=> $review->id_takeaway_product,
				);
				
				echo $vik->openControl(JText::_('VRMANAGEREVIEW6') . '*');
				echo JHtml::_('select.groupedList', $options, 'id_takeaway_product', $args);
				echo $vik->closeControl();
				?>
				
				<!-- LANGUAGE - Dropdown -->
				<?php
				$options = array();
				foreach ($languages as $lang)
				{
					$options[] = JHtml::_('select.option', $lang, $lang);
				}
				
				echo $vik->openControl(JText::_('VRMANAGEREVIEW8')); ?>
					<select name="langtag" id="vr-langtag-sel" class="medium">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $review->langtag); ?>
					</select>
				<?php echo $vik->closeControl(); ?>
				
			<?php echo $vik->closeFieldset(); ?>
		</div>

		<div class="span6">
			
			<div class="row-fluid">
				
				<div class="span12">
					<?php echo $vik->openFieldset(JText::_('VRMANAGEREVIEW9')); ?>

					<div class="control-group">
						<textarea name="comment" style="width:calc(100% - 14px);height:180px;resize:vertical;"><?php echo $review->comment; ?></textarea>
					</div>

					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<?php
			/**
			 * Trigger event to display custom HTML.
			 * In case it is needed to include any additional fields,
			 * it is possible to create a plugin and attach it to an event
			 * called "onDisplayViewReview". The event method receives the
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

		</div>

	<?php echo $vik->closeCard(); ?>

	<input type="hidden" name="id" value="<?php echo $review->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRMANAGECUSTOMER15');
JText::script('VRE_FILTER_SELECT_PRODUCT');
?>

<script type="text/javascript">

	var USERS_POOL = [];

	jQuery(document).ready(function(){

		jQuery('select.medium').select2({
			minimumResultsForSearch: -1,
			allowClear: false,
			width: 150
		});

		jQuery('#vr-products-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_PRODUCT'),
			allowClear: true,
			width: 300
		});

		jQuery('.vr-users-select').select2({
			placeholder: Joomla.JText._('VRMANAGECUSTOMER15'),
			allowClear: true,
			width: 300,
			minimumInputLength: 2,
			ajax: {
				url: 'index.php?option=com_vikrestaurants&task=search_jusers&tmpl=component&id=<?php echo $review->jid; ?>',
				dataType: 'json',
				type: 'POST',
				quietMillis: 50,
				data: function(term) {
					return {
						term: term,
					};
				},
				results: function(data) {
					return {
						results: jQuery.map(data, function (item) {
							if (jQuery.isEmptyObject(USERS_POOL[item.id])) {
								USERS_POOL[item.id] = item;
							}

							return {
								text: item.name,
								id:   item.id,
							}
						})
					};
				},
			},
			initSelection: function(element, callback) {
				// the input tag has a value attribute preloaded that points to a preselected repository's id
				// this function resolves that id attribute to an object that select2 can render
				// using its formatResult renderer - that way the repository name is shown preselected
				
				if (jQuery(element).val().length) {
					callback({name: '<?php echo (empty($this->juser->name) ? '' : addslashes($this->juser->name)); ?>'});
				}
			},
			formatSelection: function(data) {
				if (jQuery.isEmptyObject(data.name)) {
					// display data retured from ajax parsing
					return data.text;
				}
				// display pre-selected value
				return data.name;
			},
			dropdownCssClass: 'bigdrop',
		});

		jQuery('.vr-users-select').on('change', function(){
			var id = jQuery(this).val();

			if (!jQuery.isEmptyObject(USERS_POOL[id])) {
				jQuery('#user-name').val(USERS_POOL[id].name);
				jQuery('#user-mail').val(USERS_POOL[id].email);
			}
		});

	});

	// validate

	var validator = new VikFormValidator('#adminForm');

	Joomla.submitbutton = function(task) {
		if (task.indexOf('save') !== -1) {
			if (validator.validate(validateMailField)) {
				Joomla.submitform(task, document.adminForm);	
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}

	function validateMailField() {
		var email = jQuery('#user-mail');

		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		
		if (!re.test(email.val())) {
			validator.setInvalid(email);
			return false;
		}

		validator.unsetInvalid(email);
		return true;
	}

</script>
