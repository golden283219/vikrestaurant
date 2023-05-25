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

$rows = $this->rows;

$filters = $this->filters;

$ordering = $this->ordering;

$vik = VREApplication::getInstance();

foreach (array('u.id', 'u.billing_name', 'rescount', 'ordcount') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('customers', JText::_('VRMANAGECUSTOMER1'), 'u.id', $ordering['u.id'], 1, $filters, 'vrheadcolactive'.($ordering['u.id'] == 2 ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('customers', JText::_('VRMANAGECUSTOMER2'), 'u.billing_name', $ordering['u.billing_name'], 1, $filters, 'vrheadcolactive'.($ordering['u.billing_name'] == 2 ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('customers', JText::_('VRMANAGECUSTOMER18'), 'rescount', $ordering['rescount'], 1, $filters, 'vrheadcolactive'.($ordering['rescount'] == 2 ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('customers', JText::_('VRMANAGECUSTOMER21'), 'ordcount', $ordering['ordcount'], 1, $filters, 'vrheadcolactive'.($ordering['ordcount'] == 2 ? 1 : 2)),
);

$is_restaurant_enabled = VikRestaurants::isRestaurantEnabled();
$is_takeaway_enabled   = VikRestaurants::isTakeAwayEnabled();

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');

$sms_default_text = $this->isSms ? VikRestaurants::getSmsDefaultCustomersText() : '';

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
	echo $vik->alert(JText::_('VRNOCUSTOMER'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo $links[1]; ?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGECUSTOMER3');?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGECUSTOMER4');?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGECUSTOMER5');?></th>
				<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGECUSTOMER8');?></th>
				<?php
				if ($is_restaurant_enabled)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo $links[2]; ?></th>
					<?php
				}
				if ($is_takeaway_enabled)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo $links[3]; ?></th>
					<?php
				}
				?>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRINFO');?></th>
				<?php
				if ($this->isSms)
				{
					?>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRSENDSMS');?></th>
					<?php
				}
				?>
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
				
				<td class="hidden-phone">
					<?php echo $row['id']; ?>
				</td>
				
				<td style="text-align: left;">
					<?php
					if ($canEdit)
					{
						?>
						<a href="index.php?option=com_vikrestaurants&amp;task=customer.edit&amp;cid[]=<?php echo $row['id']; ?>"><?php echo $row['billing_name']; ?></a>
						<?php
					}
					else
					{
						echo $row['billing_name'];
					}
					?>

					<input type="hidden" id="billing_name_<?php echo $row['id']; ?>" value="<?php echo $this->escape($row['billing_name']); ?>" />

					<div class="td-secondary mobile-only">
						<?php echo $row['billing_mail']; ?>
					</div>
				</td>
				
				<td class="hidden-phone"><?php echo $row['billing_mail']; ?></td>
				
				<td style="text-align: center;" class="hidden-phone"><?php echo $row['billing_phone']; ?></td>
				
				<td style="text-align: center;">
					<?php
					if (!empty($row['country_code']))
					{
						?>
						<img src="<?php echo VREASSETS_URI . 'css/flags/' . strtolower($row['country_code']) . '.png'; ?>" />
						<?php
					}
					?>
				</td>
				
				<td class="hidden-phone">
					<?php
					$parts = array(
						$row['billing_city'],
						$row['billing_address'],
						$row['billing_zip'],
					);
					
					echo implode(', ', array_filter($parts));
					?>
				</td>
				
				<?php
				if ($is_restaurant_enabled)
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php echo $row['rescount']; ?>
					</td>
					<?php
				}
				
				if ($is_takeaway_enabled)
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php echo $row['ordcount']; ?>
					</td>
					<?php
				}
				?>

				<td style="text-align: center;">
					<a href="javascript: void(0);" onclick="vrOpenJModal('custinfo.<?php echo $row['id']; ?>', null, true); return false;">
						<i class="fas fa-archive big"></i>
					</a>
				</td>
				
				<?php
				if ($this->isSms)
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php
						if (!empty($row['billing_phone']))
						{
							?>
							<a href="javascript: void(0);" onClick="openSmsDialog(<?php echo $row['id']; ?>);">
								<i class="fas fa-comment big"></i>
							</a>
							<?php
						}
						else
						{
							?>
							<a href="javascript: void(0);" class="disabled">
								<i class="fas fa-comment big"></i>
							</a>
							<?php
						}
						?>
					</td>
					<?php
				}
				?>

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
	<input type="hidden" name="view" value="customers" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<?php
// customer details modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-custinfo',
	array(
		'title'       => JText::_('VRMANAGERESERVATION17'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
	)
);
?>

<div id="dialog-confirm" style="display: none;">
	
	<h4 id="sms-dialog-title" style="margin-top:0;"></h4>

	<p><?php echo JText::_('VRSMSDIALOGMESSAGE'); ?></p>

	<div>
		<textarea id="sms_message" class="full-width" style="height: 120px;max-height:50vh;resize: vertical;" maxlength="160"><?php echo $sms_default_text; ?></textarea>
	</div>

	<div>
		<input type="checkbox" name="smskeepdef" value="1" id="sms_keep_def" />
		<label for="sms_keep_def" style="display: inline-block;"><?php echo JText::_('VRKEEPSMSTEXTDEF'); ?></label>
	</div>
	
</div>

<?php
JText::script('VRSENDSMS');
JText::script('JCANCEL');
JText::script('VRSMSDIALOGTITLE');
?>

<script>

	// create SMS confirmation dialog
	var dialog = new VikConfirmDialog('#dialog-confirm');

	// add confirm button
	dialog.addButton(Joomla.JText._('VRSENDSMS'), function(args, event) {
		// uncheck any records that might have been previously selected
		jQuery('input[name="cid[]"]').prop('checked', false);
		// check selected record
		jQuery('input[name="cid[]"][value="' + args.id + '"]').prop('checked', true);

		// add hidden fields to form
		jQuery('#adminForm').append(
			'<input type="hidden" name="sms_message" value="" />\n' + 
			'<input type="hidden" name="sms_keep_def" value="0" />\n'
		);

		// inject message value
		jQuery('#adminForm input[name="sms_message"]').val(args.message.val());
		// inject keep default if checked
		if (args.keepdef.is(':checked')) {
			jQuery('#adminForm input[name="sms_keep_def"]').val(1);
		}
		
		// submit the form
		Joomla.submitform('customer.sendsms');
	});

	// add cancel button
	dialog.addButton(Joomla.JText._('JCANCEL'));

	// pre-build dialog
	dialog.build();

	function openSmsDialog(id) {
		// extract billing name from record
		var billing_name = jQuery('#billing_name_' + id).val();
		// fetch dialog title
		var title = Joomla.JText._('VRSMSDIALOGTITLE').replace(/%s/, billing_name);

		// set dialog title with customer name
		jQuery('#sms-dialog-title').html(title);

		var args = {
			id:      id,
			message: jQuery('#sms_message'),
			keepdef: jQuery('#sms_keep_def'),
		};

		// show dialog by passing some arguments
		dialog.show(args, {submit: false});
	}    
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		
		document.adminForm.submit();
	}

	// JQUERY MODAL
	
	function vrOpenJModal(id, url, jqmodal) {
		var match = id.match(/^(custinfo)\.(\d+)/);
		if (match.length) {
			id  = match[1];
			url = 'index.php?option=com_vikrestaurants&view=customerinfo&tmpl=component&id=' + match[2];
		}

		<?php echo $vik->bootOpenModalJS(); ?>
	}
	
</script>
