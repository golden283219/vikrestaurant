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

/**
 * Template file used to display the head part of
 * the take-away menus page, which contains the
 * front-end notes, the menus filter and the
 * input to change the check-in date.
 *
 * @since 1.8
 */

$config = VREFactory::getConfig();

// check whether the date selection is allowed
$is_date_allowed = $config->getBool('tkallowdate');

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

/**
 * Translate take-away notes according to the
 * current selected language.
 *
 * @since 1.8
 */
$notes = VikRestaurants::translateSetting('tknote');

if ($notes)
{
	// show take-away front notes (see configuration)
	?>
	<div class="vrtkstartnotediv">
		<?php echo $notes; ?>
	</div>
	<?php
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeaway' . ($this->filters['menu'] ? '&takeaway_menu=' . $this->filters['menu'] : '') . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="post" name="vrmenuform" id="vrmenuform">

	<div class="vrtk-menus-filter-head">
		<?php
		// get all selectable menus
		$menus = JHtml::_('vikrestaurants.takeawaymenus');

		// display dropdown only in case of 2 or more menus
		if (count($menus) > 1)
		{
			?>
			<div class="vrtkselectmenudiv vre-select-wrapper">
				<select name="takeaway_menu" id="vrtkselectmenu" class="vre-select">
					<option value="0"><?php echo JText::_('VRTAKEAWAYALLMENUS'); ?></option>
					<?php echo JHtml::_('select.options', $menus, 'value', 'text', $this->filters['menu']); ?>
				</select>
			</div>
			<?php
		}
		
		if ($config->getBool('tkshowtimes') && $this->times)
		{
			?>
			<div class="vrtk-menus-date-block vre-select-wrapper">
				<?php
				$attrs = array(
					'id'    => 'vrtk-menus-filter-time',
					'class' => 'vre-select',
				);

				// display times dropdown
				echo JHtml::_('vrehtml.site.timeselect', 'takeaway_time', $this->cart->getCheckinTime(), $this->times, $attrs);
				?>
			</div>
			<?php
		}
		?>

		<div class="vrtk-menus-date-block vre-calendar-wrapper">
			<?php 
			$checkin = date($config->get('dateformat'), $this->cart->getCheckinTimestamp());
			$today   = date($config->get('dateformat'));

			if ($checkin == $today)
			{
				$dt_value = JText::_('VRJQCALTODAY');
			}
			else
			{
				$dt_value = $checkin;
			}

			if ($is_date_allowed)
			{
				// add support for datepicker events
				JHtml::_('vrehtml.sitescripts.datepicker', '#vrtk-menus-filter-date:input', 'takeaway');

				?>
				<input type="hidden" name="takeaway_date" value="<?php echo $checkin; ?>" />
				<?php
			}
			?>

			<input type="text" class="vrtk-menus-filter-date<?php echo ($is_date_allowed ? ' enabled' : ''); ?> vre-calendar" id="vrtk-menus-filter-date" value="<?php echo $dt_value; ?>" size="10" readonly="readonly" />
		</div>

	</div>

	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="view" value="takeaway" />

</form>

<script>

	jQuery(document).ready(function() {

		var MENUS_ROUTE_LOOKUP = {};

		<?php
		$options = array_merge(
			array(JHtml::_('select.option', 0, '')),
			$menus
		);

		foreach ($options as $menu)
		{
			// fetch URL to access menu details
			$url = 'index.php?option=com_vikrestaurants&view=takeaway' . ($menu->value ? '&takeaway_menu=' . $menu->value : '') . ($itemid ? '&Itemid=' . $itemid : '');
			?>
			MENUS_ROUTE_LOOKUP['<?php echo $menu->value; ?>'] = '<?php echo JRoute::_($url, false); ?>';
			<?php
		}
		?>

		jQuery('#vrtkselectmenu').on('change', function() {
			var id = jQuery(this).val();

			if (MENUS_ROUTE_LOOKUP.hasOwnProperty(id)) {
				// change form URL for a better SEO
				jQuery(this).closest('form').attr('action', MENUS_ROUTE_LOOKUP[id]);
			}

			// submit form
			document.vrmenuform.submit();
		});

		jQuery('#vrtk-menus-filter-time').on('change', function() {
			document.vrmenuform.submit();
		});

		<?php
		if ($is_date_allowed)
		{
			?>
			jQuery('#vrtk-menus-filter-date:input').on('change', function() {
				jQuery('input[name="takeaway_date"]').val(jQuery(this).val());
				document.vrmenuform.submit();
			});
			<?php
		}
		?>

	});

</script>
