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

$filters = $this->filters;

$vik = VREApplication::getInstance();

$config = VREFactory::getConfig();

$invoiceLayout = new JLayoutFile('blocks.invoice');

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

		<div class="btn-group pull-right">
			<select name="group" id="vr-group-sel" onchange="document.adminForm.submit();">
				<?php
				$options = JHtml::_('vrehtml.admin.groups', null, true);

				echo JHtml::_('select.options', $options, 'value', 'text', $filters['group'], true);
				?>
			</select>
		</div>

		<div class="btn-group pull-right">
			<button type="button" class="btn" onClick="selectAllInvoices(1);">
				<?php echo JText::_('VRINVSELECTALL'); ?>
			</button>

			<button type="button" class="btn" onClick="selectAllInvoices(0);">
				<?php echo JText::_('VRINVSELECTNONE'); ?>
			</button>
		</div>
	</div>

	<?php
	if (count($this->invoices) == 0 && !$this->tree)
	{
		// display alert outside the invoices pool in order
		// to have a full width (only if the tree is empty)
		echo $vik->alert(JText::_('VRNOINVOICESONARCHIVE'));
	}
	else
	{
		echo $vik->openEmptyFieldset();

		$closeFieldset = true;
	}
	?>

	<div class="vr-archive-main">

		<?php
		if ($this->tree)
		{
			?>
			<div class="vr-archive-filestree">
				<ul class="root">
					<?php 
					foreach ($this->tree as $year => $months)
					{
						?>
						<li class="year <?php echo ($this->seek['year'] == $year ? 'expanded' : 'wrapped' ); ?>">
							<div class="year-node">
								<?php
								if ($this->seek['year'] == $year)
								{
									?><i class="fas fa-chevron-down"></i><?php
								}
								else
								{
									?><i class="fas fa-chevron-right"></i><?php
								}

								echo ($year != -1 ? $year : JText::_('VRINVOICESOTHERS'));
								?>
							</div>
							
							<ul class="monthslist" style="<?php echo ($this->seek['year'] != $year ? 'display: none;' : '' ); ?>">
								<?php
								foreach ($months as $mon)
								{
									?>
									<li class="month <?php echo ($this->seek['year'] == $year && $this->seek['month'] == $mon ? 'picked' : '' ); ?>">
										<div class="month-node">
											<a href="javascript: void(0);" onClick="loadInvoiceOn(<?php echo $year; ?>,<?php echo $mon; ?>,this);">
												<?php echo ($mon != -1 ? JText::_('VRMONTH' . $mon) : JText::_('VRINVOICESOTHERSALL')); ?>
											</a>
										</div>
									</li>
									<?php 
									$first_month = false;
								}
								?>
							</ul>
						</li>
						<?php 
						$first_year = false;
					}
					?>
				</ul>
			</div>
			<?php
		}
		?>
	
		<div class="vr-archive-filespool">
			
			<?php
			if (count($this->invoices) == 0 && $this->tree)
			{
				// display alert next to the tree (only if not empty)
				echo $vik->alert(JText::_('VRNOINVOICESONARCHIVE'));
			}
			else
			{
				$dt_format = $config->get('dateformat') . ' ' . $config->get('timeformat');

				foreach ($this->invoices as $invoice)
				{
					$data = array(
						'id'     => $invoice['id'],
						'number' => $invoice['inv_number'],
						'file'   => $invoice['file'],
					);

					// render invoice block
					echo $invoiceLayout->render($data);
				}
			}
			?>
		</div>
		
	</div>

	<?php
	if (!empty($closeFieldset))
	{
		echo $vik->closeEmptyFieldset();
	}
	?>

	<!-- Check-all Toggle -->

	<div style="display:none;" id="invoices-check-all-toggle">
		<?php echo $vik->getAdminToggle(count($this->invoices)); ?>
	</div>
	
	<input type="hidden" name="year" value="<?php echo $this->seek['year']; ?>" id="vrseekyear" />
	<input type="hidden" name="month" value="<?php echo $this->seek['month']; ?>" id="vrseekmonth" />
	
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="invoices" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<div class="vr-archive-footer">
	<div class="vr-archive-loadbuttons" style="<?php echo ($this->loadedAll ? "display: none;" : ""); ?>">
		<button type="button" class="btn btn-success" onClick="loadMoreInvoices(<?php echo $this->limit; ?>);"><?php echo JText::_('VRLOADMOREINVOICES'); ?></button>
		<button type="button" class="btn btn-success" onClick="loadMoreInvoices(-1);"><?php echo JText::_('VRLOADALLINVOICES'); ?></button>
	</div>

	<div class="vr-archive-wait" style="display: none;">
		<img src="<?php echo VREASSETS_ADMIN_URI . 'images/loading.gif'; ?>" />
	</div>
</div>

<script>

	jQuery(document).ready(function() {

		jQuery('.year-node').on('click', function() {
			var mlist = jQuery(this).next();

			if (mlist.is(':visible')) {
				jQuery(this).parent()
					.removeClass('expanded')
					.addClass('wrapped')
					.find('i')
						.removeClass('fa-chevron-down')
						.addClass('fa-chevron-right');

				mlist.slideUp();
			} else {
				jQuery(this).parent()
					.removeClass('wrapped')
					.addClass('expanded')
					.find('i')
						.removeClass('fa-chevron-right')
						.addClass('fa-chevron-down');

				mlist.slideDown();
			}
		});
		
		registerFileAction();

		VikRenderer.chosen('.btn-toolbar');

	});

	function _registerFileAction() {
		var parent   = jQuery(this).parent();
		var checkbox = parent.find('input[name="cid[]"]');

		if (!parent.hasClass('selected')) {
			parent.addClass('selected');
			checkbox.prop('checked', true);
		} else {
			parent.removeClass('selected');
			checkbox.prop('checked', false);
		}

		checkbox.trigger('change');
	}

	function registerFileAction() {
		// turn off click event from existing elements
		jQuery('.vr-archive-fileicon').off('click', _registerFileAction);
		// register click event again
		jQuery('.vr-archive-fileicon').on('click', _registerFileAction);
	}

	function selectAllInvoices(is) {
		var toggle = jQuery('#invoices-check-all-toggle input[type="checkbox"]');

		if (is) {
			jQuery('.vr-archive-fileblock').addClass('selected');

			jQuery('#adminForm input[name="cid[]"]').each(function() {
				jQuery(this).prop('checked', true);
			});

			if (!toggle.is(':checked')) {
				toggle.trigger('click');
			}
		} else {
			jQuery('.vr-archive-fileblock').removeClass('selected');

			jQuery('#adminForm input[name="cid[]"]').each(function(){
				jQuery(this).prop('checked', false);
			});

			if (toggle.is(':checked')) {
				toggle.trigger('click');
			}
		}
	}
	
	var QUERY_ARGS = <?php echo json_encode($this->seek); ?>;
	var RUNNING    = false;
	
	function loadInvoiceOn(year, month, node) {
		if (RUNNING) {
			return;
		}

		// auto-select all invoices when loading from scratch
		selectAllInvoices(false);
		
		RUNNING = true;
		
		QUERY_ARGS['year']  = year;
		QUERY_ARGS['month'] = month;
		
		jQuery('#vrseekyear').val(year);
		jQuery('#vrseekmonth').val(month);
		
		START_LIMIT = 0;
		
		jQuery('.month').removeClass('picked');
		jQuery(node).parent().parent().addClass('picked');
		
		jQuery('.vr-archive-filespool').html('');
		
		loadMoreInvoices(LIMIT);
	}
	
	// LOAD MORE
	
	var START_LIMIT = <?php echo $this->limit; ?>;
	var LIMIT 		= START_LIMIT;
	var MAX_LIMIT 	= <?php echo $this->maxLimit; ?>
	
	function loadMoreInvoices(lim) {
		jQuery('.vr-archive-loadbuttons').hide();
		jQuery('.vr-archive-wait').show();
		
		if (lim <= 0) {
			lim = MAX_LIMIT;
		}
		
		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=invoice.ajaxload&tmpl=component',
			{
				year:        QUERY_ARGS['year'],
				month:       QUERY_ARGS['month'],
				start_limit: START_LIMIT,
				limit:       lim, 
				keysearch:   '<?php echo addslashes($filters['keysearch']); ?>',
				group:       '<?php echo $filters['group']; ?>',
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);
			
				if (obj[0]) {
					
					START_LIMIT = obj[1];
					
					for (var i = 0; i < obj[3].length; i++) {
						jQuery('.vr-archive-filespool').append(obj[3][i]);
					}
					
					registerFileAction();

					var toggle = jQuery('#invoices-check-all-toggle input[type="checkbox"]');

					// search for an unchecked box in order to trigger the
					// related event and auto-uncheck the "all" box
					if (toggle.is(':checked') && jQuery('.vr-archive-filespool input[name="cid[]"]').not(':checked').length) {
						toggle.prop('checked', false);
					}

				} else {
					alert(obj[1]);
				}
				
				jQuery('.vr-archive-wait').hide();
				
				if (obj[2]) {
					jQuery('.vr-archive-loadbuttons').show();
				}
				
				MAX_LIMIT = parseInt(obj[4]);
				
				RUNNING = false;
			},
			function(err) {
				RUNNING = false;
			}
		);
	}
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-group-sel').updateChosen('');

		document.adminForm.submit();
	}
	
</script>
