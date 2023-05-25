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

JHtml::_('vrehtml.sitescripts.animate');

$config = VREFactory::getConfig();

$now = VikRestaurants::now();

if (!$this->order || $this->order->status != 'CONFIRMED')
{
	// order not found or not confirmed yet
	?>
	<div class="vr-confirmpage order-error"><?php echo JText::_('VRCONFORDNOROWS'); ?></div>
	<?php
}
else
{
	// order found and confirmed
	if (!$this->history)
	{
		// no order status
		?>
		<div class="vr-confirmpage"><?php echo JText::_('VRTRACKORDERNOSTATUS'); ?></div>
		<?php
	}
	else
	{
		// display list of statuses
		?>
		<div class="vr-trackorder-wrapper">
			<?php
			foreach ($this->history as $day => $list)
			{
				?>
				<div class="vr-trackorder-day">

					<div class="vr-trackorder-day-head">
						<?php echo JHtml::_('date', $day, JText::_('DATE_FORMAT_LC1'), date_default_timezone_get()); ?>
					</div>

					<div class="vr-trackorder-day-list">

						<?php
						foreach ($list as $status)
						{
							// fetch code description
							$description = strlen($status->notes) ? $status->notes : $status->codeNotes;

							if (!$description)
							{
								// code description not found, use plain code
								$description = $status->code;
							}
							?>
							<div class="vr-trackorder-status">

								<span class="vr-trackorder-status-time">
									<?php echo date($config->get('timeformat'), $status->createdon); ?>
								</span>

								<span class="vr-trackorder-status-details">
									<?php echo $description; ?>
								</span>

								<?php
								if ($now - $status->createdon < 7200)
								{
									?>
									<span class="vr-trackorder-status-ago">
										(<?php echo VikRestaurants::formatTimestamp('', $status->createdon); ?>)
									</span>
									<?php
								}
								?> 

							</div>
							<?php
						}
						?>

					</div>

				</div>
				<?php
			}
			?>

		</div>
		<?php
	}
}
