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

JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen');
JHtml::_('bootstrap.tooltip', '.hasTooltip');

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

// ORDERING LINKS

$orderDir = null;

foreach (array('id', 'name', 'ordering') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
	else
	{
		$orderDir = $ordering[$c] == 2 ? 'asc' : 'desc';
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('menusproducts', JText::_('VRMANAGEMENUSPRODUCT1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('menusproducts', JText::_('VRMANAGEMENUSPRODUCT2'), 'name', $ordering['name'], 1, $filters, 'vrheadcolactive'.(($ordering['name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('menusproducts', '<i class="fas fa-sort"></i>', 'ordering', $ordering['ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['ordering'] == 2) ? 1 : 2)),
);

$vik = VREApplication::getInstance();

$has_filters = $this->hasFilters();

$multi_lang = VikRestaurants::isMultilanguage();

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=menusproduct.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'productsList', 'adminForm', $orderDir, $saveOrderingUrl);
}

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">
	
	<div class="btn-toolbar vr-btn-toolbar" style="height:32px;">
		
		<div class="btn-group pull-left input-append">
			<input type="text" name="keysearch" id="vrkeysearch" size="32" 
				value="<?php echo $filters['keysearch']; ?>" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" />

			<button type="submit" class="btn">
				<i class="icon-search"></i>
			</button>
		</div>

		<div class="btn-group pull-left hidden-phone">
			<button type="button" class="btn <?php echo ($has_filters ? 'btn-primary' : ''); ?>" onclick="vrToggleSearchToolsButton(this);">
				<?php echo JText::_('JSEARCH_TOOLS'); ?>&nbsp;<i class="fas fa-caret-<?php echo ($has_filters ? 'up' : 'down'); ?>" id="vr-tools-caret"></i>
			</button>
		</div>
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>

		<div class="btn-group pull-right hidden-phone">
			<a href="index.php?option=com_vikrestaurants&amp;view=tags&amp;group=products" class="btn">
				<?php echo JText::_('VRGOTOTAGS'); ?>
			</a>
		</div>
	
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<?php
		$options = array(
			JHtml::_('select.option', 0, 'JOPTION_SELECT_PUBLISHED'),
			JHtml::_('select.option', 1, 'VRSYSPUBLISHED1'),
			JHtml::_('select.option', 2, 'VRSYSPUBLISHED0'),
			JHtml::_('select.option', 3, 'VRSYSHIDDEN'),
		);
		?>
		<div class="btn-group pull-left">
			<select name="status" id="vr-status-select" class="<?php echo ($filters['status'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['status'], true); ?>
			</select>
		</div>

		<?php
		$tags = JHtml::_('vikrestaurants.tags', 'products');

		if ($tags)
		{
			?>
			<div class="btn-group pull-left">
				<select name="tag" id="vr-tag-select" class="<?php echo ($filters['tag'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
					<option value=""><?php echo JText::_('VRE_FILTER_SELECT_TAG'); ?></option>
					<?php echo JHtml::_('select.options', $tags, 'name', 'name', $filters['tag']); ?>
				</select>
			</div>
			<?php
		}
		?>

		<?php
		$options = array(
			JHtml::_('select.option', 0, JText::_('VRFILTERSELECTMENU')),
		);

		$options = array_merge($options, $this->menus);
		?>
		<div class="btn-group pull-left">
			<select name="id_menu" id="vr-menu-select" class="<?php echo ($filters['id_menu'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();" <?php echo $filters['status'] == 3 ? 'disabled="disabled"' : ''; ?>>
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['id_menu']); ?>
			</select>
		</div>

	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOMENUSPRODUCT'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="productsList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="30%" style="text-align: left;"><?php echo JText::_('VRMANAGEMENUSPRODUCT3'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENUSPRODUCT4'); ?></th>
				
				<?php
				if ($filters['status'] != 3)
				{
					// not hidden products
					?>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENUSPRODUCT6'); ?></th>
					<?php
				}

				if ($multi_lang)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU33');?></th>
					<?php
				}

				if ($filters['status'] != 3)
				{
					// not hidden products
					?>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENUSPRODUCT5'); ?></th>
					<?php
				}
				?>

				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="1%" style="text-align: center;"><?php echo $links[2]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			
			$icon_type = 1;

			if (empty($row['image']))
			{
				// image not uploaded
				$icon_type = 2;
			}
			
			if (!file_exists(VREMEDIA . DIRECTORY_SEPARATOR . $row['image']))
			{
				// image not found
				$icon_type = 0;
			}
			
			$img_title = JText::_('VRIMAGESTATUS' . $icon_type);

			$desc = strip_tags($row['description']);
			
			if (strlen($desc) > 150)
			{
				$desc = mb_substr($desc, 0, 128, 'UTF-8') . "...";
			} 
			?>
			<tr class="row<?php echo $kk; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>

				<td class="hidden-phone"><?php echo $row['id']; ?></td>
				
				<td>
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=menusproduct.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a>
						<?php
					}
					else
					{
						echo $row['name'];
					}
					?>
				</td>

				<td class="hidden-phone"><?php echo $desc; ?></td>

				<td style="text-align: center;"><?php echo VikRestaurants::printPriceCurrencySymb($row['price']); ?></td>

				<?php
				// only if not 'hidden'
				if ($filters['status'] != 3)
				{
					?>
					<td style="text-align: center;">
						<?php
						if ($canEditState)
						{
							?>
							<a href="index.php?option=com_vikrestaurants&amp;task=menusproduct.<?php echo ($row['published'] ? 'un' : ''); ?>publish&amp;cid[]=<?php echo $row['id']; ?>">
								<i class="fas fa-<?php echo $row['published'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
							</a>
							<?php
						}
						else
						{
							?>
							<i class="fas fa-<?php echo $row['published'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
							<?php
						}
						?>
					</td>
					<?php
				}
					
				if ($multi_lang)
				{
					?>
					<td style="text-align: center;">
						<a href="index.php?option=com_vikrestaurants&amp;view=langmenusproducts&amp;id_product=<?php echo $row['id']; ?>">
							<?php
							foreach ($row['languages'] as $lang)
							{
								echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
							}
							?>
						</a>
					</td>
					<?php
				}

				// only if not 'hidden'
				if ($filters['status'] != 3)
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php
						if ($icon_type == 1)
						{
							?>
							<a href="<?php echo VREMEDIA_URI . $row['image']; ?>" class="modal" target="_blank">
								<i class="fas fa-image ok big-2x" title="<?php echo $img_title ?>"></i>
							</a>
							<?php
						}
						else if ($icon_type == 0)
						{
							?>
							<i class="fas fa-eye-slash no big-2x" title="<?php echo $img_title ?>"></i>
							<?php
						}
						else
						{
							?>
							<i class="fas fa-image no big-2x" title="<?php echo $img_title ?>"></i>
							<?php
						}
						?>
					</td>
					<?php
				}
				?>

				<td class="order nowrap center hidden-phone">
					<?php echo JHtml::_('vrehtml.admin.sorthandle', $row['ordering'], $canEditState, $canOrder); ?>
				</td>

			</tr>
			<?php
			$kk = 1 - $kk;
		}		
		?>
	</table>
	<?php
}
?>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="menusproducts" />
	
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-status-select').updateChosen(0);
		jQuery('#vr-menu-select').updateChosen(0);
		jQuery('#vr-tag-select').updateChosen('');

		// remove disabled attr to corectly POST id_menu filter
		jQuery('#vr-menu-select').attr('disabled', false);

		document.adminForm.submit();
	}

</script>
