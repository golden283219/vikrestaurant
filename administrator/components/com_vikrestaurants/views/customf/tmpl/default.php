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

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

$multi_lang = VikRestaurants::isMultilanguage();

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
	OrderingManager::getLinkColumnOrder('customf', JText::_('VRMANAGEMENUSPRODUCT1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('customf', JText::_('VRMANAGECUSTOMF1'), 'name', $ordering['name'], 1, $filters, 'vrheadcolactive'.(($ordering['name'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('customf', '<i class="fas fa-sort"></i>', 'ordering', $ordering['ordering'], 1, $filters, 'vrheadcolactive'.(($ordering['ordering'] == 2) ? 1 : 2)),
);

$has_filters = $this->hasFilters();

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');
$canOrder     = $ordering['ordering'] > 0;

if ($canOrder && $canEditState)
{
	$saveOrderingUrl = 'index.php?option=com_vikrestaurants&task=customf.saveOrderAjax&tmpl=component';
	JHtml::_('vrehtml.scripts.sortablelist', 'customfieldsList', 'adminForm', $orderDir, $saveOrderingUrl, array('group' => $filters['group']));
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

		<div class="btn-group pull-right">
			<select name="group" id="vr-group-sel" onchange="document.adminForm.submit();">
				<?php
				echo JHtml::_('select.options', JHtml::_('vrehtml.admin.groups'), 'value', 'text', $filters['group'], true);
				?>
			</select>
		</div>
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<?php
		$options = array();
		$options[] = JHtml::_('select.option', '', 			'VRE_FILTER_SELECT_TYPE');
		$options[] = JHtml::_('select.option', 'text', 		'VRCUSTOMFTYPEOPTION1');
		$options[] = JHtml::_('select.option', 'textarea', 	'VRCUSTOMFTYPEOPTION2');
		// $options[] = JHtml::_('select.option', 'number', 	'VRCUSTOMFTYPEOPTION8');
		$options[] = JHtml::_('select.option', 'date', 		'VRCUSTOMFTYPEOPTION3');
		$options[] = JHtml::_('select.option', 'select', 	'VRCUSTOMFTYPEOPTION4');
		$options[] = JHtml::_('select.option', 'checkbox', 	'VRCUSTOMFTYPEOPTION5');
		$options[] = JHtml::_('select.option', 'separator', 'VRCUSTOMFTYPEOPTION6');
		?>
		<div class="btn-group pull-left">
			<select name="type" id="vr-type-sel" class="<?php echo (!empty($filters['type']) ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['type'], true); ?>
			</select>
		</div>

		<?php
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('VRE_FILTER_SELECT_RULE'));
		for ($i = 1; $i <= 11; $i++)
		{
			$options[] = JHtml::_('select.option', $i, JText::_('VRCUSTFIELDRULE' . $i));
		}
		?>
		<div class="btn-group pull-left">
			<select name="rule" id="vr-rule-sel" class="<?php echo ($filters['rule'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['rule']); ?>
			</select>
		</div>

		<?php
		$options = array(
			JHtml::_('select.option', -1, 'JOPTION_SELECT_PUBLISHED'),
			JHtml::_('select.option', 1, 'VRMANAGECUSTOMF3'),
			JHtml::_('select.option', 0, 'VRCONFIGCANCREASONOPT1'),
		);
		?>
		<div class="btn-group pull-left">
			<select name="status" id="vr-status-sel" class="<?php echo ($filters['status'] != -1 ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['status'], true); ?>
			</select>
		</div>

	</div>

<?php
if (count($this->rows) == 0)
{
	echo $vik->alert(JText::_('VRNOCUSTOMF'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>" id="customfieldsList">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo JText::_( 'VRMANAGECUSTOMF2' ); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left phone-center'); ?>" width="10%" style="text-align: left;"><?php echo JText::_( 'VRMANAGECUSTOMF11' ); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_( 'VRMANAGECUSTOMF3' ); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_( 'VRMANAGECUSTOMF7' ); ?></th>
				
				<?php
				if ($multi_lang)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGEMENU33');?></th>
					<?php
				}
				?>
				
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="1%" style="text-align: center;"><?php echo $links[2]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>
		
		<?php
		$k = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];	
			?>
			<tr class="row<?php echo $k; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>

				<td class="hidden-phone"><?php echo $row['id']; ?></td>

				<td>
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=customf.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo JText::_($row['name']); ?></a>
						<?php
					}
					else
					{
						echo JText::_($row['name']);
					}
					?>
				</td>

				<td><?php echo ucwords($row['type']); ?></td>

				<td style="text-align: left;" class="phone-center">
					<?php 
					$clazz = '';
					switch ($row['rule']) 
					{
						case VRCustomFields::NOMINATIVE:     $clazz = 'male';           break;
						case VRCustomFields::EMAIL:          $clazz = 'envelope';       break;
						case VRCustomFields::PHONE_NUMBER:   $clazz = 'phone';          break;
						case VRCustomFields::ADDRESS:        $clazz = 'road';           break;
						case VRCustomFields::DELIVERY:       $clazz = 'motorcycle';     break;
						case VRCustomFields::ZIP:            $clazz = 'map-marker-alt'; break;
						case VRCustomFields::CITY:           $clazz = 'map-marker-alt'; break;
						case VRCustomFields::STATE:          $clazz = 'map-marker-alt'; break;
						case VRCustomFields::PICKUP:         $clazz = 'shopping-bag';   break;
						case VRCustomFields::NOTES:          $clazz = 'sticky-note';    break;
						case VRCustomFields::DELIVERY_NOTES: $clazz = 'map-signs';      break;
					}

					if (!empty($clazz))
					{
						?>
						<i class="fas fa-<?php echo $clazz; ?> big" style="width:32px;"></i>
						<span class="hidden-phone"><?php echo JText::_('VRCUSTFIELDRULE' . $row['rule']); ?></span>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=customf.required&amp;cid[]=<?php echo $row['id']; ?>&amp;state=<?php echo $row['required'] == 1 ? 0 : 1; ?>" />
							<i class="fas fa-<?php echo $row['required'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-<?php echo $row['required'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						<?php
					}
					?>
				</td>

				<td style="text-align: center;" class="hidden-phone">
					<?php echo JText::_('VRCUSTOMFGROUPOPTION' . ($row['group'] + 1)); ?>
				</td>

				<?php
				if ($multi_lang)
				{
					?>
					<td style="text-align: center;">
						<a href="index.php?option=com_vikrestaurants&amp;view=langcustomf&amp;id_customf=<?php echo $row['id']; ?>">
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
				?>
				
				<td class="order nowrap center hidden-phone">
					<?php echo JHtml::_('vrehtml.admin.sorthandle', $row['ordering'], $canEditState, $canOrder); ?>
				</td>

			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		
	</table>
	<?php
}
?>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="customf" />
	
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-type-sel').updateChosen('');
		jQuery('#vr-rule-sel').updateChosen(0);
		jQuery('#vr-status-sel').updateChosen(-1);
		
		document.adminForm.submit();
	}
	
</script>
