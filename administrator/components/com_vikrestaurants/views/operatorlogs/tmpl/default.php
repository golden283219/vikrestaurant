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

$vik = VREApplication::getInstance();

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');

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
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<div class="btn-group pull-left">
			<select name="group" id="vr-group-sel" class="<?php echo ($filters['group'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php
				$options = JHtml::_('vrehtml.admin.groups', array(1, 2), true);

				echo JHtml::_('select.options', $options, 'value', 'text', $filters['group'], true);
				?>
			</select>
		</div>

		<?php
		$arr = getdate();

		$date_filters = array(
			mktime(0, 0, 0, $arr['mon'], $arr['mday'], $arr['year']),
			mktime(0, 0, 0, $arr['mon'], $arr['mday']-7, $arr['year']),
			mktime(0, 0, 0, $arr['mon']-1, $arr['mday'], $arr['year']),
			mktime(0, 0, 0, $arr['mon']-3, $arr['mday'], $arr['year']),
		);

		$options = array(
			JHtml::_('select.option', '', 'VRE_FILTER_SELECT_DATE'),
		);

		foreach ($date_filters as $i => $ts)
		{
			$options[] = JHtml::_('select.option', $ts, 'VROPLOGDATEFILTER' . ($i + 1));
		}
		?>
		<div class="btn-group pull-left">
			<select name="date" id="vr-date-sel" class="<?php echo ($filters['date'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['date'], true); ?>
			</select>
		</div>

		<div class="btn-group pull-left vr-toolbar-setfont" style="<?php echo $filters['date'] ? 'display:none;' : ''; ?>">
			<?php
			$attr = array();
			$attr['class']    = 'vr-day-cal';
			$attr['onChange'] = "document.adminForm.submit();";

			echo $vik->calendar($filters['day'], 'day', 'vr-day-cal', null, $attr);
			?>
		</div>

	</div>
	
<?php
if (count($rows) == 0)
{
	echo $vik->alert(JText::_('VRNOOPERATORLOG'));
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
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="50%" style="text-align: left;"><?php echo JText::_('VRMANAGEOPLOG1'); ?></th>
				<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;">&nbsp;</th>
				<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGEOPLOG2'); ?></th>
				<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_('VRMANAGEOPLOG3'); ?></th>
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
				
				<td>
					<?php
					if (preg_match("/^[A-Z][A-Z0-9]*$/", $row['log']))
					{
						// we probably have to pass the language key to JText
						echo JText::_($row['log']);
					}
					else
					{
						echo $row['log'];
					}
					?>
				</td>

				<td style="text-align: center;">
					<a href="javascript:void(0);" onclick="vrOpenJModal('loginfo<?php echo $row['id']; ?>', null, true); return false;">
						<i class="fas fa-file-alt big"></i>
					</a>
				</td>
				
				<td>
					<?php echo VikRestaurants::formatTimestamp(JText::_('DATE_FORMAT_LC2'), $row['createdon']); ?>
				</td>
				
				<td style="text-align: center;" class="hidden-phone">
					<?php echo JText::_('VROPLOGTYPE' . $row['group']); ?>
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
	
	<input type="hidden" name="id" value="<?php echo $filters['id_operator']; ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="operatorlogs" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<div id="trash-confirm-html" style="display:none;">
	<div><?php echo JText::_('VRE_OPERATOR_LOGS_TRASH_MSG'); ?></div>

	<div style="margin-top:5px;">
		<select id="trash-date">
			<option value="1 week"><?php echo JText::plural('VRE_N_WEEKS', 1); ?></option>
			<option value="1 month"><?php echo JText::plural('VRE_N_MONTHS', 1); ?></option>
			<option value="3 months"><?php echo JText::plural('VRE_N_MONTHS', 3); ?></option>
			<option value="6 months"><?php echo JText::plural('VRE_N_MONTHS', 6); ?></option>
			<option value="1 year"><?php echo JText::plural('VRE_N_YEARS', 1); ?></option>
		</select>
	</div>
</div>

<?php
// create log details layout
$layout = new JLayoutFile('blocks.operatorlog');

foreach ($rows as $row)
{
	// set current log for being used in sub-layout
	$data = array(
		'log'      => $row,
		'operator' => false, // hide operator badge
	);

	// order details modal
	echo JHtml::_(
		'bootstrap.renderModal',
		'jmodal-loginfo' . $row['id'],
		array(
			'title'       => JText::_('VRMANAGEOPLOG1') . ' #' . $row['id'],
			'closeButton' => true,
			'keyboard'    => true, 
			'bodyHeight'  => 80,
		),
		$layout->render($data)
	);
}

JText::script('JTOOLBAR_TRASH');
JText::script('JCANCEL');
?>

<script type="text/javascript">

	var trashConfirm;

	jQuery(document).ready(function() {

		VikRenderer.chosen('.btn-toolbar');

		// create trash confirmation prompt
		trashConfirm = new VikConfirmDialog('#trash-confirm-html');

		// confirm resulting record
		trashConfirm.addButton(Joomla.JText._('JTOOLBAR_TRASH'), function(input, event) {
			// append selected date threshold to form
			jQuery('#adminForm').append('<input type="hidden" name="datelimit" value="' + jQuery(input).val() + '" />');
			// submit form
			Joomla.submitform('operator.trashlogs', document.adminForm);
		});

		// discard result
		trashConfirm.addButton(Joomla.JText._('JCANCEL'));

		// pre build dialog in order to attach some events
		trashConfirm.build();

		// register event to render select before showing the dialog
		VikRenderer.chosen('#' + trashConfirm.id);

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-group-sel').updateChosen('');
		jQuery('#vr-date-sel').updateChosen('');
		jQuery('#vr-day-cal').val('');
		
		document.adminForm.submit();
	}

	// JQUERY MODAL
	
	function vrOpenJModal(id, url, jqmodal) {
		<?php echo $vik->bootOpenModalJS(); ?>
	}

	Joomla.submitbutton = function(task) {
		if (task == 'operator.trashlogs') {
			trashConfirm.show('#trash-date');
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}
	
</script>
