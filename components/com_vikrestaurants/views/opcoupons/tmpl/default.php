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

JHtml::_('behavior.core');
JHtml::_('vrehtml.assets.fontawesome');

$operator = $this->operator;

$itemid = JFactory::getApplication()->input->get('Itemid', 0, 'uint');

$config   = VREFactory::getConfig();
$currency = VREFactory::getCurrency();

?>

<div class="vrfront-manage-titlediv">
	<h2><?php echo JText::_('VROVERSIGHTMENUITEM4'); ?></h2>
	<?php echo VikRestaurants::getToolbarLiveMap($operator); ?>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=opcoupons' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" method="POST" name="opcouponsform" id="adminForm">

	<div class="vrfront-list-wrapper">

		<div class="vrfront-manage-headerdiv">
			<div class="vrfront-manage-actionsdiv">
				<?php
				if ($operator->canManage('coupon'))
				{
					?>
					<div class="vrfront-manage-btn">
						<button type="button" onClick="vrNewCoupon();" id="vrfront-manage-btncreate" class="vrfront-manage-button">
							<?php echo JText::_('VRNEW'); ?>
						</button>
					</div>
					<?php
				}
				?>
			</div>
		</div>

		<?php
		if (count($this->coupons) == 0)
		{
			echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');
		}
		else
		{
			?>
			<div class="vr-allorders-list">

				<div class="vr-allorders-singlerow vr-allorders-head vr-allorders-row">
					<span class="vr-allorders-column" style="width: 5%; text-align: left;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGELANG1', 'id', $this->orderingDir, $this->ordering); ?>
					</span>

					<span class="vr-allorders-column" style="width: 30%; text-align: left;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGECOUPON1', 'code', $this->orderingDir, $this->ordering); ?>
					</span>

					<span class="vr-allorders-column" style="width: 20%; text-align: left;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGECOUPON2', 'type', $this->orderingDir, $this->ordering); ?>
					</span>
					
					<span class="vr-allorders-column" style="width: 15%;text-align: center;">
						<?php echo JHtml::_('vrehtml.site.sort', 'VRMANAGECOUPON4', 'value', $this->orderingDir, $this->ordering); ?>
					</span>
					
					<span class="vr-allorders-column" style="width: 30%;text-align: center;">
						<?php echo JText::_('VRMANAGECOUPON11'); ?>
					</span>
				</div>

				<?php 
				$kk = 1;
				foreach ($this->coupons as $row)
				{ 
					$date_valid = explode('-', $row['datevalid']);
					?>
					<div class="vr-allorders-singlerow vr-allorders-row<?php echo $kk; ?>">
						<span class="vr-allorders-column" style="width: 5%; text-align: left;">
							<?php echo $row['id']; ?>
						</span>

						<span class="vr-allorders-column" style="width: 30%; text-align: left;">
							<?php
							if ($operator->canManage('coupon'))
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&task=opcoupon.edit&cid[]=' . $row['id'] . ($itemid ? '&Itemid=' . $itemid : '')); ?>">
									<?php echo $row['code']; ?>
								</a>
								<?php
							}
							else
							{ 
								echo $row['code'];
							}
							?>

							<?php
							if ($operator->get('group') == 0)
							{
								?>
								<i class="fas fa-<?php echo ($row['group'] == 0 ? 'utensils' : 'shopping-basket'); ?> vr-icon-idgroup"></i>
								<?php
							}
							?>
						</span>

						<span class="vr-allorders-column" style="width: 20%; text-align: left;">
							<?php echo JText::_('VRCOUPONTYPEOPTION' . $row['type']); ?>
						</span>

						<span class="vr-allorders-column" style="width: 15%; text-align: center;">
							<?php
							if ($row['percentot'] == 1)
							{
								echo $row['value'] . '%';
							}
							else
							{
								echo $currency->format($row['value']);
							}
							?>
						</span>

						<span class="vr-allorders-column" style="width: 30%; text-align: center;">
							<?php
							if (count($date_valid) == 2)
							{
								echo date($config->get('dateformat'), $date_valid[0])
									. ' - ' . date($config->get('dateformat'), $date_valid[1]);
							}
							else
							{
								echo '--';
							}
							?>
						</span>
					</div>
					<?php
				}
				?>
			</div>

			<?php echo JHtml::_('form.token'); ?>

			<div class="vr-list-pagination"><?php echo $this->navbut; ?></div>

			<?php
		}
		?>

	</div>

	<input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderingDir; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="opcoupons" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="Itemid" value="<?php echo $itemid; ?>" />
</form>

<script>

	function vrNewCoupon() {
		Joomla.submitform('opcoupon.add', document.opcouponsform);
	}

</script>
