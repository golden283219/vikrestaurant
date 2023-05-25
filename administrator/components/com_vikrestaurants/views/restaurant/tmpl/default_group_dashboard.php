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

$vik = VREApplication::getInstance();

?>

<div class="row-fluid dashboard-wrapper" style="margin-top:2px;">

	<?php
	foreach ($this->currentDashboard as $position => $widgets)
	{
		?>
		<div class="dashboard-widgets-container <?php echo $this->layout; ?>" data-position="<?php echo $position; ?>">

			<?php
			foreach ($widgets as $i => $widget)
			{
				$id = $widget->getID();

				// get widget description
				$help = $widget->getDescription();

				if ($help)
				{
					// create popover with the widget description
					$help = $vik->createPopover(array(
						'title'   => $widget->getTitle(),
						'content' => $help,
					));
				}

				// widen or shorten the widget
				switch ($widget->getSize())
				{
					// force EXTRA SMALL width : (100% / N) - (100% / (N * 2))
					case 'extra-small':
						$width = 'width: calc((100% / ' . count($widgets) . ') - (100% / ' . (count($widgets) * 2) . '));';
						break;

					// force SMALL width : (100% / N) - (100% / (N * 4))
					case 'small':
						$width = 'width: calc((100% / ' . count($widgets) . ') - (100% / ' . (count($widgets) * 4) . '));';
						break;

					// force NORMAL width : (100% / N)
					case 'normal':
						$width = 'width: calc(100% / ' . count($widgets) . ');';
						break;

					// force LARGE width : (100% / N) + (100% / (N * 4))
					case 'large':
						$width = 'width: calc((100% / ' . count($widgets) . ') + (100% / ' . (count($widgets) * 4) . '));';
						break;

					// force EXTRA LARGE width : (100% / N) + (100% / (N * 2))
					case 'extra-large':
						$width = 'width: calc((100% / ' . count($widgets) . ') + (100% / ' . (count($widgets) * 2) . '));';
						break;

					// fallback to flex basis to take the remaining space
					default:
						$width = 'flex: 1;';
				}
				?>
				<div
					class="dashboard-widget"
					id="widget-<?php echo $id; ?>"
					data-widget="<?php echo $widget->getName(); ?>"
					style="<?php echo $width; ?>"
				>

					<div class="widget-wrapper">
						<div class="widget-head">
							<h3><?php echo $widget->getTitle() . $help; ?></h3>

							<a href="javascript:void(0);" onclick="openWidgetConfiguration('<?php echo $id; ?>');" class="widget-config-btn">
								<i class="fas fa-ellipsis-h"></i>
							</a>
						</div>

						<div class="widget-body">
							<?php echo $widget->display(); ?>
						</div>

						<div class="widget-error-box" style="display: none;">
							<?php echo $vik->alert(JText::_('VRE_AJAX_GENERIC_ERROR'), 'error'); ?>
						</div>
					</div>

				</div>
				<?php
			}
			?>

		</div>
		<?php
	}
	?>

</div>
