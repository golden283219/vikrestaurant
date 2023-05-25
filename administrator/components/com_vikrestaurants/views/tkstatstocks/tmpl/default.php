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
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.chartjs');

$filters = $this->filters;

$ordering = $this->ordering;

foreach (array('ename', 'products_used') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

// ORDERING LINKS

$links = array(
	OrderingManager::getLinkColumnOrder('tkstatstocks', JText::_('VRMANAGETKSTOCK1'), 'ename', $ordering['ename'], 1, $filters, 'vrheadcolactive'.(($ordering['ename'] == 2) ? 1 : 2)),
	OrderingManager::getLinkColumnOrder('tkstatstocks', JText::_('VRMANAGETKSTOCK8'), 'products_used', $ordering['products_used'], 1, $filters, 'vrheadcolactive'.(($ordering['products_used'] == 2) ? 1 : 2)),
);

$vik = VREApplication::getInstance();

$date = JDate::getInstance();

$months_labels = array();

// iterate months
for ($month = 1; $month <= 12; $month++)
{
	// use JDate to extract the month name
	$months_labels[] = $date->monthToString($month);
}

$weekdays_labels = array();

// iterate week days
for ($day = 0; $day < 7; $day++)
{
	// use JDate to extract the day name
	$weekdays_labels[] = $date->dayToString($day);
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
		
		<div class="btn-group pull-left">
			<button type="button" class="btn" onclick="clearFilters();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>

		<div class="btn-group pull-right vr-toolbar-setfont">
			<?php
			$attr = array();
			$attr['class'] 		= 'vrdatefilter';
			$attr['onChange'] 	= 'document.adminForm.submit();';
			echo $vik->calendar($filters['end_day'], 'end_day', 'vrdatefilterend', null, $attr);
			?>
		</div>

		<div class="btn-group pull-right vr-toolbar-setfont">
			<?php
			$attr = array();
			$attr['class'] 		= 'vrdatefilter';
			$attr['onChange'] 	= 'document.adminForm.submit();';
			echo $vik->calendar($filters['start_day'], 'start_day', 'vrdatefilterstart', null, $attr);
			?>
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
	<div class="row-fluid">

		<div class="span7">
			<?php echo $vik->openEmptyFieldset(); ?>

				<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>">
					<?php echo $vik->openTableHead(); ?>
						<tr>
							<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="20%" style="text-align: left;"><?php echo $links[0]; ?></th>
							<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;">&nbsp;</th>
							<th class="<?php echo $vik->getAdminThClass(); ?>" width="10%" style="text-align: center;"><?php echo $links[1]; ?></th>
							<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGETKSTOCK9'); ?></th>
						</tr>
					<?php echo $vik->closeTableHead(); ?>

					<?php
					$kk = 0;
					foreach ($this->rows as $r)
					{ 
						?>
						<tr class="row<?php echo $kk; ?>">

							<td><?php echo $r['ename']; ?></td>

							<td>
								<?php
								if (!empty($r['oname']))
								{
									?><span class="badge badge-info"><?php echo $r['oname']; ?></span><?php
								}
								?>
							</td>

							<td style="text-align: center;">
								<?php echo $r['products_used']; ?>
							</td>

							<td style="text-align: center;">
								<a href="javascript: void(0);" onClick="loadChartsRequest(<?php echo intval($r['eid']); ?>, <?php echo intval($r['oid']); ?>);">
									<i class="fas fa-chart-bar medium-big"></i>
								</a>
							</td>

						</tr>
						<?php
						$kk = ($kk + 1) % 2;
					}
					?>
				</table>

				<?php echo $this->navbut; ?>

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

		<div class="span5">
			<?php echo $vik->openEmptyFieldset(); ?>

			<div class="vr-stocks-report-content">

				<div class="vr-stocks-report-content-tabs">
					<div class="vr-tab-button">
						<a href="javascript: void(0);" onClick="vrSwitchSection('weekdays', this);" class="active"><?php echo JText::_('VRTKSTATSTOCKSCHARTWEEKDAYS'); ?></a>
					</div>
					<div class="vr-tab-button">
						<a href="javascript: void(0);" onClick="vrSwitchSection('months', this);"><?php echo JText::_('VRTKSTATSTOCKSCHARTMONTHS'); ?></a>
					</div>
					<div class="vr-tab-button">
						<a href="javascript: void(0);" onClick="vrSwitchSection('years', this);"><?php echo JText::_('VRTKSTATSTOCKSCHARTYEARS'); ?></a>
					</div>
				</div>

				<div class="vr-stocks-report-charts">

					<div id="vr-chart-weekdays-box" class="vr-chart-box">
						<?php echo $vik->alert(JText::_('VRTKSTATSTOCKSNOITEMSEL')); ?>
					</div>

					<div id="vr-chart-months-box" class="vr-chart-box" style="display: none;">
						<?php echo $vik->alert(JText::_('VRTKSTATSTOCKSNOITEMSEL')); ?>
					</div>

					<div id="vr-chart-years-box" class="vr-chart-box" style="display: none;">
						<?php echo $vik->alert(JText::_('VRTKSTATSTOCKSNOITEMSEL')); ?>
					</div>

				</div>

			</div>

			<?php echo $vik->closeEmptyFieldset(); ?>
		</div>

	</div>

	<?php
}
?>

	<input type="hidden" name="view" value="tkstatstocks" />
	<input type="hidden" name="task" value="" />

	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRE_N_PRODUCTS_SOLD');
JText::script('VRE_N_PRODUCTS_SOLD_1');
?>

<script>

	jQuery(document).ready(function(){

		VikRenderer.chosen('.btn-toolbar');

	});
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vr-menu-select').updateChosen(0);
		
		document.adminForm.submit();
	}

	var _SECTION_ACTIVE_ = 'weekdays';

	function vrSwitchSection(section, link) {
		if (_SECTION_ACTIVE_ == section) {
			return;
		}

		jQuery('.vr-chart-box').hide();
		jQuery('#vr-chart-' + section + '-box').show();

		jQuery('.vr-tab-button a').removeClass('active');
		jQuery(link).addClass('active');

		_SECTION_ACTIVE_ = section;

		buildCharts(null);
	}

	//////// CHARTS ////////

	function loadChartsRequest(eid, oid) {

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=tkstatstocks.getchartdata&tmpl=component',
			{
				id_product: eid,
				id_option:  oid,
				start:      jQuery('#vrdatefilterstart').val(),
				end:        jQuery('#vrdatefilterend').val(),
			},
			function(resp) {
				buildCharts(jQuery.parseJSON(resp));
			},
			function(error) {
				if (!error.responseText) {
					// use default connection lost error
					error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
				}

				// raise error
				alert(error.responseText);
			}
		);

	}

	var _TREE_ = null;

	function buildCharts(tree) {
		if (tree === null) {
			// no specified data
			if (_TREE_ !== null) {
				// use current data
				tree = _TREE_;
			} else {
				// nothing else to show, do not go ahead
				return;
			}
		} else {
			// update current data
			_TREE_ = tree;
		}

		// fetch week days chart
		if (_SECTION_ACTIVE_ == 'weekdays') {

			for (var i = 0; i < 7; i++) {
				if (!tree.weekdays.hasOwnProperty(i)) {
					tree.weekdays[i] = 0;
				}
			}

			buildWeekdaysChart(tree.weekdays);

		}
		// fetch months chart
		else if (_SECTION_ACTIVE_ == 'months') {

			for (var i = 1; i <= 12; i++) {
				if (!tree.months.hasOwnProperty(i)) {
					tree.months[i] = 0;
				}
			}

			buildMonthsChart(tree.months);

		}
		// fetch years chart
		else if (_SECTION_ACTIVE_ == 'years') {

			buildYearsChart(tree.years);

		}
	}

	function buildYearsChart(arr) {
		buildBarsChart(
			'#vr-chart-years-box',
			Object.keys(arr),
			Object.values(arr)
		);
	} 

	function buildMonthsChart(arr) {
		buildBarsChart(
			'#vr-chart-months-box',
			<?php echo json_encode($months_labels); ?>,
			Object.values(arr)
		);
	} 

	function buildWeekdaysChart(arr) {
		buildBarsChart(
			'#vr-chart-weekdays-box',
			<?php echo json_encode($weekdays_labels); ?>,
			Object.values(arr)
		);
	}

	function buildBarsChart(id, labels, records) {
		jQuery('.vr-chart-box').hide();
		jQuery(id).html('<canvas></canvas>').show();

		var data = {
			labels: labels,
			datasets: [],
		};
		
		var c = 0;

		data.datasets.push({
			// the label string that appears when hovering the mouse above the lines intersection points
			label: "Dataset",
			// the background color drawn behind the line
			// backgroundColor: "rgba(151, 187, 205, 0.6)",
			backgroundColor: "rgba(65, 190, 110, 0.6)",
			// the fill color of the line
			// borderColor: "rgba(151, 187, 205, 1)",
			borderColor: "rgba(65, 190, 110, 1)",
			// the line dataset
			data: records,
		});
		
		var options = {
			// turn off legend
			legend: {
				display: false,
			},
			// axes handling
			scales: {
				// Y Axis properties
				yAxes: [{
					// do not show y axis
					display: false,
					// hide horizontal grid lines too
					gridLines : {
						display : false,
					},
					// make sure the chart starts at 0
					ticks: {
						beginAtZero: true,
					},
				}],
				// X Axis properties
				xAxes: [{
					// hide vertical grid lines
					gridLines: {
						display: false,
					},
				}],
			},
			// tooltip handling
			tooltips: {
				// tooltip callbacks are used to customize default texts
				callbacks: {
					// format the tooltip text displayed when hovering a point
					label: function(tooltipItem, data) {
						// get items sold
						var sold = parseInt(tooltipItem.value);

						var label = '';

						// format label by fetching singular/plural form
						if (sold == 1)
						{
							label = Joomla.JText._('VRE_N_PRODUCTS_SOLD_1');
						} else {
							label = Joomla.JText._('VRE_N_PRODUCTS_SOLD').replace(/%d/, sold);
						}

						return ' ' + label;
					},
					// change label colors because, by default, the legend background is blank
					labelColor: function(tooltipItem, chart) {
						// get tooltip item meta data
						var meta = chart.data.datasets[tooltipItem.datasetIndex];

						return {
							// use white border
							borderColor: 'rgb(0,0,0)',
							// use same item background color
							backgroundColor: meta.borderColor,
						};
					},
				},
			},
		};
		
		// get 2D canvas for BAR chart
		var canvas = jQuery(id).find('canvas')[0];
		var ctx    = canvas.getContext('2d');

		// display BAR chart
		var barChart = new Chart(ctx, {
			type:    'bar',
			data:    data,
			options: options,
		});
	}

	Chart.defaults.global.responsive = true;

</script>
