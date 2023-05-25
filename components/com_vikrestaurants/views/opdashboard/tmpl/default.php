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

JHtml::_('behavior.keepalive');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.sitescripts.datepicker', '#vrdatefield:input');

$operator = $this->operator;

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

?>

<div class="vroversighthead">
	<h2><?php echo JText::sprintf('VRLOGINOPERATORHI', $operator->get('firstname')); ?></h2>

	<?php echo VikRestaurants::getToolbarLiveMap($operator); ?>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opdashboard' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="POST" name="opdashform">

	<div class="vrfront-manage-actionsdiv">
		
		<div class="vrfront-manage-btn">
			<div class="vre-calendar-wrapper">
				<input type="text" value="<?php echo $this->filters['date']; ?>" id="vrdatefield" class="vre-calendar" name="date" size="20" />
			</div>
		</div>

		<div class="vrfront-manage-btn">
			<?php
			/**
			 * Operators can change the minutes intervals.
			 *
			 * @since 1.8.1
			 */
			$intervals = array();
			$intervals[] = JHtml::_('select.option', '', '--');

			foreach (array(10, 15, 30, 60) as $int)
			{
				$intervals[] = JHtml::_('select.option', $int, JText::sprintf('VRE_STATS_WIDGET_OVERVIEW_INTERVALS_OPT', $int));
			}
			?>
			<div class="vre-select-wrapper">
				<select name="intervals" id="vr-intervals-sel" class="vre-select">
					<?php echo JHtml::_('select.options', $intervals, 'value', 'text', $this->filters['intervals']); ?>
				</select>
			</div>
		</div>

	</div>

	<?php
	// prepare widget layout data
	$data = array(
		'widget'   => 'overview',
		'group'    => 'restaurant',
		'config'   => $this->filters,
		'timer'    => 60,
		'itemid'   => $itemid,
	);

	// display widget by using an apposite layout
	echo JLayoutHelper::render('oversight.widget', $data);
	?>

	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="view" value="opdashboard" />

</form>

<script>

	jQuery(document).ready(function() {

		jQuery('#vrdatefield, #vr-intervals-sel').on('change', function() {
			document.opdashform.submit();
		});

	});

</script>
