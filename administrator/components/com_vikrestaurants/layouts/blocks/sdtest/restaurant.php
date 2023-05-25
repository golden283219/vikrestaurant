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

/**
 * Layout variables
 * -----------------
 * @param 	array    list    An array of supported special days.
 * @param 	boolean  closed  True in case of closure, false otherwise.
 * @param 	array    args    An associative array containing the searched arguments.
 * @param 	array 	 shifts  A list of supported shifts.
 * @param 	object   $data   The special day object.
 */
extract($displayData);

$currency = VREFactory::getCurrency();

?>

<table class="git-table">

	<thead>
		<tr>
			<th width="30%"><b><?php echo JText::_('VRE_CONFIG_PARAM'); ?></b></th>
			<th width="70%"><b><?php echo JText::_('VRE_CONFIG_SETTING'); ?></b></th>
		</tr>
	</thead>

	<tbody>

		<!-- Deposit -->

		<tr>
			<td><?php echo JText::_('VRMANAGERESERVATION9'); ?></td>

			<td>
				<?php
				if ($data->deposit)
				{
					if ($data->depositPerPerson)
					{
						// override currency configuration
						$options = array(
							// add "/person" next to the currency symbol
							'symbol' => JText::sprintf('VRPRICEPERPERSON', $currency->getSymbol()),
							// always show the currency after the price
							'position' => 1,
						);
					}
					else
					{
						// use default currency configuration
						$options = array();
					}

					// display deposit amount
					echo $currency->format($data->deposit, $options);

					if ($data->depositGuests > 1)
					{
						?>
						<span class="pull-right">
							<?php
							echo $data->depositGuests . '+ ';

							for ($i = 1; $i <= 2; $i++)
							{
								?><i class="fas fa-male"></i><?php
							}
							?>
						</span>
						<?php
					}
				}
				else
				{
					?><i class="fas fa-dot-circle no medium-big"></i><?php
				}
				?>
			</td>
		</tr>

		<!-- Choose Menu -->

		<tr>
			<td><?php echo JText::_('VRMANAGESPDAY19'); ?></td>

			<td>
				<?php
				if ($data->chooseMenu)
				{
					?><i class="fas fa-check-circle ok medium-big"></i><?php
				}
				else
				{
					?><i class="fas fa-dot-circle no medium-big"></i><?php
				}
				?>
			</td>
		</tr>

		<?php
		if ($data->allowedPeople)
		{
			?>
			<!-- Maximum Allowed People -->

			<tr>
				<td><?php echo JText::_('VRMANAGESPDAY21'); ?></td>

				<td><?php echo JText::plural('VRE_N_PEOPLE', $data->allowedPeople); ?></td>
			</tr>
			<?php
		}
		?>

		<!-- Working Shifts -->

		<tr>
			<td><?php echo JText::_('VRMANAGESPDAY4'); ?></td>

			<td>
				<?php
				foreach ($data->shifts ? $data->shifts : $shifts as $shift)
				{
					?>
					<span class="badge badge-info">
						<?php echo $shift->fromtime . ' - ' . $shift->totime; ?>
					</span>
					<?php
				}
				?>
			</td>
		</tr>

		<!-- Menus -->

		<tr>
			<td><?php echo JText::_('VRMANAGESPDAY10'); ?></td>

			<td>
				<?php
				foreach ($data->getMenus(true) as $menu)
				{
					?>
					<span class="badge badge-success">
						<?php echo $menu->name; ?>
					</span>
					<?php
				}
				?>
			</td>
		</tr>

	</tbody>

</table>
