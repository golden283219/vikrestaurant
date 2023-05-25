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

JHtml::_('bootstrap.tooltip', '.hasTooltip');

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

$config = VREFactory::getConfig();

// ORDERING LINKS
foreach (array('l.createdon', 'l.status') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('apilogs', JText::_('VRMANAGEAPIUSER15'), 'l.createdon', $ordering['l.createdon'], 1, $filters, 'vrheadcolactive'.(($ordering['l.createdon'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('apilogs', JText::_('VRMANAGEAPIUSER13'), 'l.status', $ordering['l.status'], 1, $filters, 'vrheadcolactive'.(($ordering['l.status'] == 2) ? 1 : 2)),
);

$dt_format = $config->get('dateformat') . ' ' . $config->get('timeformat');

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
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>
	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOAPILOG'));
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
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="1%" style="text-align: left;"><?php echo JText::_('VRMANAGEAPIUSER1'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo $links[0]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="30%" style="text-align: left;"><?php echo JText::_('VRMANAGEAPIUSER14'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="15%" style="text-align: center;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGEAPIUSER17'); ?></th>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];
			?>
			<tr class="row<?php echo $kk; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>
				
				<td class="hidden-phone"><?php echo $row['id']; ?></td>
				
				<td>
					<span class="hasTooltip" title="<?php echo date($dt_format, $row['createdon']); ?>">
						<?php echo VikRestaurants::formatTimestamp(JText::_('DATE_FORMAT_LC2'), $row['createdon']); ?>
					</span>

					<div>
						<?php
						if ($row['application'] || $row['username'])
						{
							?>
							<span class="badge">
								<?php echo $row['application'] ? $row['application'] : $row['username']; ?>
							</span>
							<?php
						}
						?>
					</div>
				</td>

				<td style="text-align: left;" class="hidden-phone">
					<?php echo nl2br($row['content']); ?>
				</td>
				
				<td style="text-align: center;">
					<b style="text-transform:uppercase;color:#<?php echo ($row['status'] ? '090' : '900'); ?>">
						<?php echo JText::_($row['status'] ? 'VROK' : 'VRERROR'); ?>
					</b>
				</td>
				
				<td style="text-align: center;" class="hidden-phone">
					<?php echo $row['ip']; ?>
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
	<input type="hidden" name="view" value="apilogs" />
	<input type="hidden" name="id_login" value="<?php echo $filters['id_login']; ?>" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<?php
JText::script('VRSYSTEMCONFIRMATIONMSG');
?>

<script type="text/javascript">
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		
		document.adminForm.submit();
	}

	Joomla.submitbutton = function(task) {
		if (task == 'apilog.truncate') {
			if (confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'))) {
				Joomla.submitform(task, document.adminForm);
			}
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}
	
</script>
