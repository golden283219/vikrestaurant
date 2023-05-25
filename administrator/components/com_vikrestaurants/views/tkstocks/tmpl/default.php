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

JHtml::_('formbehavior.chosen');
JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.fontawesome');

$vik = VREApplication::getInstance();

$filters = $this->filters;

$ordering = $this->ordering;

foreach (array('ename', 'remaining') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

// ORDERING LINKS

$links = array(
	OrderingManager::getLinkColumnOrder('tkstocks', JText::_('VRMANAGETKSTOCK1'), 'ename', $ordering['ename'], 1, $filters, 'vrheadcolactive'.(($ordering['ename'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tkstocks', JText::_('VRMANAGETKSTOCK7'), 'remaining', $ordering['remaining'], 1, $filters, 'vrheadcolactive'.(($ordering['remaining'] == 2) ? 1 : 2)),
);

?>

<style>

	i.chained-option {
		cursor: pointer;
	}

	i.chained-option:not(.chain-hover) {
		opacity: 0.4;
	}

</style>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

	<div class="btn-toolbar vr-btn-toolbar" style="height:32px;">
		
		<div class="btn-group pull-left input-append">
			<input type="text" name="keysearch" id="vrkeysearch" size="32" 
				value="<?php echo $filters['keysearch']; ?>" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />

			<button type="submit" class="btn">
				<i class="icon-search"></i>
			</button>
		</div>
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>

		<div class="btn-group pull-right hidden-phone">
			<button type="button" class="btn" onclick="refillAll();"><?php echo JText::_('VRREFILLALL'); ?></button>
		</div>

		<?php
		$menus = array_merge(
			array(JHtml::_('select.option', 0, JText::_('VRFILTERSELECTMENU'))),
			$this->menus
		);
		?>
		<div class="btn-group pull-right hidden-phone">
			<select name="id_menu" onChange="document.adminForm.submit();" id="vr-menu-select" class="<?php echo $filters['id_menu'] ? 'active' : ''; ?>">
				<?php echo JHtml::_('select.options', $menus, 'value', 'text', $filters['id_menu']); ?>
			</select>
		</div>
	
	</div>

<?php
if (count($this->rows) == 0)
{
	echo $vik->alert(JText::_('VRNOTKPRODUCT'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;">&nbsp;</th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGETKSTOCK10'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('right hidden-phone'); ?>" width="5%" style="text-align: right;">&nbsp;</th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		$i = 0;
		foreach ($this->rows as $r)
		{
			$badge = '';
			$title = '';

			if ($r['products_used'] > 0)
			{
				$badge = 'badge-success';

				if ($r['products_in_stock'] - $r['products_used'] <= $r['product_notify_below'])
				{
					// equals or lower than threshold
					$badge = 'badge-important';
				}
				else if ($r['products_in_stock'] - $r['products_used'] <= $r['product_notify_below'] * 3)
				{
					// close to the threshold
					$badge = 'badge-warning';
				}

				$title = JText::sprintf('VRSTOCKITEMUSED', $r['products_used'], $r['products_in_stock']);
			}
			else
			{
				$title = JText::_('VRSTOCKITEMNOUSED');
			}

			$available_stock = $r['products_in_stock'] - $r['products_used'];

			$identifier = intval($r['eid']) . '-' . intval($r['oid']);
			?>

			<tr class="row<?php echo $kk; ?>">

				<td><?php echo $r['ename']; ?></td>

				<td>
					<?php
					if (!empty($r['oname']))
					{
						?><span class="badge badge-info"><?php echo $r['oname']; ?></span><?php

						if (!$r['stock_enabled'])
						{
							// display icon to let the user understand this option might
							// share the same stock of other variations
							?>
							<i
								class="fas fa-link fa-flip-horizontal medium hasTooltip chained-option pull-right"
								title="<?php echo $this->escape(JText::_('VRTKSTOCK_OVERRIDE_CHAIN')); ?>"
								data-parent="<?php echo $r['eid']; ?>"
							></i>
							<?php
						}
					}
					?>
				</td>

				<td style="text-align: center;">
					<span class="badge <?php echo $badge; ?> hasTooltip" title="<?php echo $title; ?>">
						<?php echo $available_stock; ?>
					</span>
				</td>

				<td>
					<input type="hidden" name="original_stock[]" value="<?php echo $r['product_original_stock']; ?>" />
					<input type="hidden" name="id_product[]" value="<?php echo $r['eid']; ?>" />
					<input type="hidden" name="id_option[]" value="<?php echo $r['stock_enabled'] ? $r['oid'] : 0; ?>" />
					
					<select name="stock_factor[]" class="vr-stockfactor-sel" id="vr-stockfactor-sel<?php echo $identifier; ?>">
						<option value="1" selected="selected">+</option>
						<option value="-1">-</option>
					</select>

					<input type="number" name="stock_override[]" value="0" min="0" max="999999" step="1" id="vr-stock-override<?php echo $identifier; ?>" />
				</td>

				<td style="text-align: right;" class="hidden-phone">
					<?php
					if ($available_stock < $r['product_original_stock'])
					{
						?>
						<button type="button" class="btn vr-refill-btn" onclick="refillStock('<?php echo $identifier; ?>', <?php echo $available_stock; ?>, <?php echo $r['product_original_stock']; ?>);">
							<?php echo JText::_('VRREFILL'); ?>
						</button>
						<?php
					}
					?>
				</td>

			</tr>
			
			<?php
			$kk = ($kk + 1) % 2;
		}
		?>
	</table>
	<?php
}
?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="tkstocks" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script>

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');
		VikRenderer.chosen('.vr-stockfactor-sel', '60px', {disable_search: true});

		jQuery('input[type="number"]').keypress(function(e) {
			if ((e.keyCode || e.which) == 13) {
				Joomla.submitbutton('tkstock.save');
			}
		});

		jQuery('.chained-option').hover(function() {
			var id = jQuery(this).data('parent');

			jQuery('.chained-option[data-parent="' + id + '"]').addClass('chain-hover');
		}, function() {
			var id = jQuery(this).data('parent');

			jQuery('.chained-option[data-parent="' + id + '"]').removeClass('chain-hover');
		});

	});

	function refillStock(id, available_stock, default_stock) {
		var refill = parseInt(default_stock) - parseInt(available_stock);

		jQuery('#vr-stockfactor-sel' + id).updateChosen(1);
		jQuery('#vr-stock-override' + id).val(refill);

	}

	function refillAll() {
		jQuery('.vr-refill-btn').trigger('click');
	}
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-menu-select').updateChosen(0);

		document.adminForm.submit();
	}

</script>
