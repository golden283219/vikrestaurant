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

$config = VREFactory::getConfig();

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

			<?php
			$rooms = array_merge(
				array(JHtml::_('select.option', 0, JText::_('VRMAPSCHOOSEROOM'))),
				JHtml::_('vikrestaurants.rooms')
			);
			?>
			<div class="btn-group pull-right" style="margin-left:6px;">
				<select name="id_room" id="vr-room-select" class="<?php echo ($this->filters['id_room'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
					<?php echo JHtml::_('select.options', $rooms, 'value', 'text', $this->filters['id_room']); ?>
				</select>   
			</div>
			
		</div>
		
	<?php
	if (count($rows) == 0)
	{
		echo $vik->alert(JText::_('VRNORESERVATION'));
	}
	else
	{
		?>
		<table cellpadding="4" cellspacing="0" border="0" width="100%" class="<?php echo $vik->getAdminTableClass(); ?>">
			<?php echo $vik->openTableHead(); ?>
				<tr>
					<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION1'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION3'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="15%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION17'); ?></th>
					<th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION5'); ?></th>
					<th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;">&nbsp;</th>
					<th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION12'); ?></th>
				</tr>
			<?php echo $vik->closeTableHead(); ?>

			<?php
			$kk = 0;
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				?>
				<tr class="row<?php echo $kk; ?>">
					
					<td class="hidden-phone">
						<div class="td-primary"><?php echo $row['sid']; ?></div>
					</td>

					<td>
						<div class="td-primary">
							<?php echo JHtml::_('date', JDate::getInstance($row['checkin_ts']), JText::_('DATE_FORMAT_LC3') . ' ' . $config->get('timeformat'), date_default_timezone_get()); ?>
						</div>

						<div class="td-secondary">
							<?php echo JText::plural('VRE_N_PEOPLE', $row['people']); ?>
						</div>
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
					</td>

					<td style="text-align: center;">
						<div class="badge badge-info hasTooltip" title="<?php echo $this->escape($row['room_name']); ?>"><?php echo $row['table_name']; ?></div>
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
	
	<input type="hidden" name="view" value="restbusyres" />
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
	