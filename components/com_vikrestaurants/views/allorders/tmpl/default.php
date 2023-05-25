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

JHtml::_('vrehtml.sitescripts.animate');

$input = JFactory::getApplication()->input;

$active_tab = $input->cookie->getUint('vre_allorders_activetab', 1);

$is_restaurant = VikRestaurants::isRestaurantEnabled();
$is_takeaway   = VikRestaurants::isTakeAwayEnabled();

if (!$is_restaurant)
{
	// auto select take-away in case restaurant is disabled
	$active_tab = 2;
}
else if (!$is_takeaway)
{
	// auto select restaurant in case take-away is disabled
	$active_tab = 1;
}

$itemid = $input->get('Itemid', null, 'uint');

?>
	
<div class="vr-allorders-userhead">

	<div class="vr-allorders-userleft">
		<h2><?php echo JText::sprintf('VRALLORDERSTITLE', $this->user->name); ?></h2>
	</div>

	<div class="vr-allorders-userright">
		<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=userlogout' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" class="vr-allorders-logout">
			<?php echo JText::_('VRLOGOUT'); ?>
		</a>
	</div>
</div>

<div class="vr-allorders-switch-tabs">
	<?php
	if ($is_restaurant)
	{
		?>
		<div class="switch-box <?php echo ($active_tab == 1 ? 'active' : ''); ?>">
			<a href="javascript: void(0);" onClick="switchOrderTab(this, 1);">
				<?php echo JText::_('VRALLORDERSRESTAURANTHEAD'); ?>
			</a>
		</div>
		<?php
	}

	if ($is_takeaway)
	{
		?>
		<div class="switch-box <?php echo ($active_tab == 2 ? 'active' : ''); ?>">
			<a href="javascript: void(0);" onClick="switchOrderTab(this, 2);">
				<?php echo JText::_('VRALLORDERSTAKEAWAYHEAD'); ?>
			</a>
		</div>
		<?php
	}
	?>
</div>

<?php
if ($is_restaurant)
{
	?>

	<div class="vr-allorders-wrapper" id="vrboxwrapper1" style="<?php echo ($active_tab == 1  ? '' : 'display:none;'); ?>">
		
		<?php
		// display restaurant reservations by using a sub-template
		echo $this->loadTemplate('restaurant');
		?>

	</div>

	<?php
}

if ($is_takeaway)
{
	?>

	<div class="vr-allorders-wrapper" id="vrboxwrapper2" style="<?php echo ($active_tab == 2 ? '' : 'display:none;'); ?>">
	
		<?php
		// display take-away orders by using a sub-template
		echo $this->loadTemplate('takeaway');
		?>

	</div>

	<?php
}
?>

<script type="text/javascript">

	function switchOrderTab(link, tab) {
		jQuery('.vr-allorders-switch-tabs .switch-box').removeClass('active');
		jQuery(link).parent().addClass('active');

		jQuery('.vr-allorders-wrapper').hide();
		jQuery('#vrboxwrapper' + tab).show();

		document.cookie = 'vre.allorders.activetab=' + tab + '; path=/';
	}

</script>
