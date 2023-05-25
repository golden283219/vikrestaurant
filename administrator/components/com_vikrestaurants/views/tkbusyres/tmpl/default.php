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

$currency = VREFactory::getCurrency();
$config   = VREFactory::getConfig();

$vik = VREApplication::getInstance();

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

	<div class="modal-content-padding">
	
		<div class="btn-toolbar vr-btn-toolbar" style="height:32px;" id="busy-filter-bar">
			
			<?php
			$options = array(
				JHtml::_('select.option',  15, VikRestaurants::minutesToStr(15)),
				JHtml::_('select.option',  30, VikRestaurants::minutesToStr(30)),
				JHtml::_('select.option',  60, VikRestaurants::minutesToStr(60)),
				JHtml::_('select.option',  90, VikRestaurants::minutesToStr(90)),
				JHtml::_('select.option', 120, VikRestaurants::minutesToStr(120)),
			);
			?>
			<div class="btn-group pull-right">
				<select name="interval" id="vr-interval-select" class="active" onchange="document.adminForm.submit();">
					<?php echo JHtml::_('select.options', $options, 'value', 'text', $this->filters['interval']); ?>
				</select>   
			</div>
			
		</div>
		
	<?php
	if (count($rows) == 0)
	{
		echo $vik->alert(JText::_('VRNOTKRESERVATION'));
	}
	else
	{
		?>
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>">
			<?php echo $vik->openTableHead(); ?>
				<tr>
					<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES1'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES3'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES13'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES24'); ?></th>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGETKRES22'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGETKRES8'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;">&nbsp;</th>
					<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGETKRES9'); ?></th>
				</tr>
			<?php echo $vik->closeTableHead(); ?>

			<?php
			$kk = 0;
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];

				$route_obj = (object) json_decode($row['route']);
				?>
				<tr class="row<?php echo $kk; ?>">

					<td class="hidden-phone">
						<div class="td-primary"><?php echo $row['sid']; ?></div>
					</td>

					<td>
						<div class="td-primary">
							<?php
							echo JHtml::_('date', JDate::getInstance($row['checkin_ts']), JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'), date_default_timezone_get());

							if ($row['preparation_ts'])
							{
								// subtract a time slot from the preparation time
								$row['preparation_ts'] = strtotime('-' . $config->get('tkminint') . ' minutes', $row['preparation_ts']);
								// fetch preparation time hint
								$prepTip = JText::sprintf('VRE_TKRES_PREP_TIME_HINT', date($config->get('timeformat'), $row['preparation_ts']));

								?><i class="fas fa-info-circle hasTooltip" title="<?php echo $this->escape($prepTip); ?>" style="margin-left:4px;"></i><?php
							}
							?>
						</div>

						<?php
						if ($row['checkin_ts'] > VikRestaurants::now() && $row['delivery_service'] && !empty($route_obj->duration) && $route_obj->duration > 0)
						{
							?>
							<div class="td-secondary">
								<?php echo JText::sprintf('VRMANAGETKRES34', date($config->get('timeformat'), $row['checkin_ts'] - $route_obj->duration)); ?>
							</div>
							<?php	
						}
						?>

						<div class="mobile-only badge badge-info">
							<?php echo JText::_($row['delivery_service'] ? 'VRMANAGETKRES14' : 'VRMANAGETKRES15'); ?>
						</div>
					</td>

					<td class="hidden-phone">
						<div class="td-primary">
							<?php echo JText::_($row['delivery_service'] ? 'VRMANAGETKRES14' : 'VRMANAGETKRES15'); ?>

							<?php
							if (!empty($route_obj->distancetext))
							{
								?>
								<i class="fas fa-road" style="margin-left: 10px;"></i>&nbsp;<?php echo $route_obj->distancetext; ?>
								<?php
							}

							if (!empty($route_obj->durationtext))
							{
								?>
								<i class="fas fa-stopwatch" style="margin-left: 10px;"></i>&nbsp;<?php echo $route_obj->durationtext; ?>
								<?php
							}
							?>
						</div>

						<?php
						if (!empty($route_obj->origin))
						{
							?>
							<div class="td-secondary"><?php echo $route_obj->origin; ?></div>
							<?php
						}
						?>
					</td>

					<td class="hidden-phone">
						<div class="td-primary">
							<?php echo $row['purchaser_nominative']; ?>
						</div>
						
						<div class="td-secondary">
							<?php
							if ($row['purchaser_phone'])
							{
								?>
								<a href="tel:<?php echo $row['purchaser_phone']; ?>">
									<i class="fas fa-phone"></i>&nbsp;
									<?php echo $row['purchaser_phone']; ?>
								</a>
								<?php
							}
							else if ($row['purchaser_mail'])
							{
								?>
								<a href="mail:<?php echo $row['purchaser_mail']; ?>">
									<i class="fas fa-envelope"></i>&nbsp;
									<?php echo $row['purchaser_mail']; ?>
								</a>
								<?php
							}
							?>
						</div>

						<?php
						if ($row['delivery_service'] && $row['purchaser_address'])
						{
							?>
							<div class="td-secondary">
								<?php echo $row['purchaser_address']; ?>
							</div>
							<?php
						}
						?>
					</td>

					<td style="text-align: center;">
						<div class="td-primary hasTooltip" title="<?php echo $this->escape(JText::sprintf('VRTKRESITEMSINCART', $row['items_preparation_count'], $row['items_count'])); ?>">
							<?php
							if ($row['items_preparation_count'] > 0)
							{
								?>
								<div>
									<i class="fas fa-burn"></i>
									<?php echo $row['items_preparation_count']; ?>
								</div>
								<?php
							}
							?>
							
							<?php
							if ($row['items_count'] - $row['items_preparation_count'] > 0)
							{
								?>
								<div>
									<i class="fas fa-hamburger"></i>
									<?php echo ($row['items_count'] - $row['items_preparation_count']); ?>
								</div>
								<?php
							}
							?>
						</div>
					</td>

					<td class="hidden-phone">
						<div class="td-primary">
							<?php echo $currency->format($row['total_to_pay']); ?>
						</div>
					</td>

					<td style="text-align: center;" class="hidden-phone">
						<?php
						if (empty($row['code_icon']))
						{
							echo !empty($row['code']) ? $row['code'] : '';
						}
						else
						{
							?>
							<div class="vrrescodelink">
								<img src="<?php echo VREMEDIA_SMALL_URI . $row['code_icon']; ?>" class="hasTooltip" title="<?php echo $this->escape($row['code']); ?>" />
							</div>
							<?php
						}
						?>
					</td>

					<td>
						<div class="<?php echo 'vrreservationstatus' . strtolower($row['status']); ?>">
							<?php echo JText::_('VRRESERVATIONSTATUS' . $row['status']); ?>
						</div>

						<?php
						if ($row['status'] == 'PENDING')
						{
							$expires_in = VikRestaurants::formatTimestamp($config->get('dateformat') . ' ' . $config->get('timeformat'), $row['locked_until'], $local = false);
							?>
							<div class="td-secondary">
								<?php echo JText::sprintf('VRTKRESEXPIRESIN', $expires_in); ?>
							</div>
							<?php
						}
						?>
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

	</div>
		
	<input type="hidden" name="view" value="tkbusyres" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="date" value="<?php echo $this->filters['date']; ?>" />
	<input type="hidden" name="time" value="<?php echo $this->filters['time']; ?>" />
	
</form>

<script type="text/javascript">
	
	jQuery(document).ready(function() {

		onInstanceReady(() => {
			if (jQuery.fn.chosen === undefined) {
				return false;
			}

			return true;
		}).then(() => {
			VikRenderer.chosen('#busy-filter-bar');
		});

	});

</script>
