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

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

// ORDERING LINKS

foreach (array('id', 'application', 'username', 'last_login') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('apiusers', JText::_('VRMANAGEAPIUSER1'), 'id', $ordering['id'], 1, $filters, 'vrheadcolactive'.(($ordering['id'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('apiusers', JText::_('VRMANAGEAPIUSER2'), 'application', $ordering['application'], 1, $filters, 'vrheadcolactive'.(($ordering['application'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('apiusers', JText::_('VRMANAGEAPIUSER3'), 'username', $ordering['username'], 1, $filters, 'vrheadcolactive'.(($ordering['username'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('apiusers', JText::_('VRMANAGEAPIUSER7'), 'last_login', $ordering['last_login'], 1, $filters, 'vrheadcolactive'.(($ordering['last_login'] == 2) ? 1 : 2)),
);

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');

$has_filters = $this->hasFilters();

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
			<a href="index.php?option=com_vikrestaurants&amp;view=apibans" class="btn">
				<i class="fas fa-ban" style="margin-right: 4px;"></i>&nbsp;<?php echo JText::_('VRMANAGEAPIUSER16'); ?>
			</a>
			
			<a href="index.php?option=com_vikrestaurants&amp;view=apilogs" class="btn">
				<i class="fas fa-clipboard-list" style="margin-right: 4px;"></i>&nbsp;<?php echo JText::_('VRMANAGEAPIUSER12'); ?>
			</a>
		</div>
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<?php
		$options = array(
			JHtml::_('select.option', -1, 'JOPTION_SELECT_PUBLISHED'),
			JHtml::_('select.option', 1, 'JPUBLISHED'),
			JHtml::_('select.option', 0, 'JUNPUBLISHED'),
		);
		?>
		<div class="btn-group pull-left">
			<select name="active" id="vr-active-sel" class="<?php echo ($filters['active'] != -1 ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['active'], true); ?>
			</select>
		</div>

	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOAPIUSER'));
}
else
{
	?>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>">
		<?php echo $vik->openTableHead(); ?>
			<tr>
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="15%" style="text-align: left;"><?php echo $links[2]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGEAPIUSER5'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGEAPIUSER6'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGEAPIUSER11'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="12%" style="text-align: center;"><?php echo $links[3]; ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];

			$ips = (array) json_decode($row['ips']);

			$name = strlen($row['application']) ? $row['application'] : $row['username'];

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
						<a href="index.php?option=com_vikrestaurants&amp;task=apiuser.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $name; ?></a>
						<?php
					}
					else
					{
						echo $name;
					}
					?>
				</td>
				
				<td class="hidden-phone"><?php echo $row['username']; ?></td>
				
				<td style="text-align: center;" class="hidden-phone">
					<?php
					if ($ips)
					{
						?>
						<a href="javascript:void(0);">
							<i class="fas fa-globe-americas big hasTooltip" title="<?php echo implode('<br />', $ips); ?>"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<a class="disabled" disabled="disabled">
							<i class="fas fa-globe-americas big"></i>
						</a>
						<?php
					}
					?>
				</td>
				
				<td style="text-align: center;">
					<?php
					if ($canEditState)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=apiuser.activate&amp;cid[]=<?php echo $row['id']; ?>&amp;state=<?php echo $row['active'] == 1 ? 0 : 1; ?>" />
							<i class="fas fa-<?php echo $row['active'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<i class="fas fa-<?php echo $row['active'] ? 'check-circle ok' : 'dot-circle no'; ?> big"></i>
						<?php
					}
					?>
				</td>
				
				<td style="text-align: center;">
					<?php
					if ($row['log'] !== null)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;view=apilogs&amp;id_login=<?php echo $row['id']; ?>">
							<i class="fas fa-file-alt big"></i>
						</a>
						<?php
					}
					else
					{
						?>
						<a class="disabled" disabled="disabled">
							<i class="fas fa-file-alt big"></i>
						</a>
						<?php
					}
					?>
				</td>
				
				<td style="text-align: center;" class="hidden-phone">
					<span style="float: left;margin-left: 10px;">
						<?php
						if ($row['last_login'] > 0)
						{
							echo VikRestaurants::formatTimestamp(JText::_('DATE_FORMAT_LC2'), $row['last_login']);
						}
						else
						{
							echo JText::_('VRMANAGEAPIUSER10');
						}
						?>
					</span>

					<span style="float: right;margin-right: 10px;">
						<?php
						if ($row['log'] === null && $row['last_login'] <= 0)
						{
							// no activity
							$color = '999';
						}
						else if ($row['log'] === null || $row['log']['status'])
						{
							// success
							$color = '090';
						}
						else
						{
							// failure
							$color = '900';
						}
						?> 

						<i class="fas fa-circle big" style="color: #<?php echo $color; ?>;"></i>
					</span>
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
	<input type="hidden" name="view" value="apiusers" />
	
	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-active-sel').updateChosen(-1);
		
		document.adminForm.submit();
	}
	
</script>
