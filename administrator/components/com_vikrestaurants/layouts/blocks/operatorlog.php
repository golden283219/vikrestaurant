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
 * @param 	array    log          The log record.
 * @param 	boolean  group        True to display the group badge.
 * @param 	boolean  reservation  True to display the reservation badge.
 * @param 	boolean  operator     True to display the operator badge.
 */
extract($displayData);

$group       = isset($group)       ? $group       : true;
$reservation = isset($reservation) ? $reservation : true;
$operator    = isset($operator)    ? $operator    : true;

$data = (array) json_decode($log['content'], true);

$currency = VREFactory::getCurrency();

?>

<div class="operator-log-modal">

	<!-- SHORT DETAILS -->

	<div class="log-head">

		<?php
		if ($group)
		{
			?>
			<span class="badge badge-info"><?php echo JText::_('VROPLOGTYPE' . $log['group']); ?></span>
			<?php
		}

		if ($log['id_reservation'] > 0 && $reservation)
		{
			?>
			<span class="badge badge-important">
				<?php
				echo JText::sprintf(
					$log['group'] == 1 ? 'VRE_LOG_RES_NUM' : 'VRE_LOG_ORDER_NUM',
					$log['id_reservation']
				);
				?>
			</span>
			<?php
		}

		if ($operator)
		{
			$parts = array();

			if (!empty($log['code']))
			{
				$parts[] = '#' . $log['code'];
			}
			
			if (!empty($log['firstname']) || !empty($log['lastname']))
			{
				$parts[] = trim(@$log['firstname'] . ' ' . @$log['lastname']);
			}

			if ($parts)
			{
				$parts = implode(' - ', $parts);
			}
			else
			{
				$parts = 'ID: ' . $log['id_operator'];
			}
			?>
			<span class="badge badge-important"><?php echo $parts; ?></span>
			<?php
		}
		?>

		<span class="badge badge-warning">
			<?php echo JHtml::_('date', JDate::getInstance($log['createdon']), JText::_('DATE_FORMAT_LC2'), date_default_timezone_get()); ?>
		</span>

	</div>

	<!-- BIG DATA WRAPPER -->

	<div class="log-body">

		<!-- LOG SUBJECT -->

		<div class="log-subject">
			<?php
			if (preg_match("/^[A-Z][A-Z0-9]*$/", $log['log']))
			{
				// we probably have to pass the language key to JText
				echo JText::_($log['log']);
			}
			else
			{
				// in case the log exceeded the length of 256 bytes, the remaining part is added into
				// the "readmore" attribute of the content column
				echo $log['log'] . (!empty($data['readmore']) ? $data['readmore'] : '');
			}
			?>
		</div>

		<?php
		if ($data)
		{
			?>
			<!-- LOG DATA -->

			<div class="log-content">

				<?php
				if (!empty($data['diff']))
				{
					// display table of differences
					?>
					<table class="git-table">
						<thead>
							<tr>
								<th width="25%">&nbsp;</th>
								<th width="35%"><b><?php echo JText::_('VRE_LOG_NEW_VALUE'); ?></b></th>
								<th width="35%"><b><?php echo JText::_('VRE_LOG_OLD_VALUE'); ?></b></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($data['diff'] as $k => $diff)
							{
								?>
								<tr>
									<td><b><?php echo JText::_($diff['label']); ?></b></td>
									
									<td>
										<span style="font-weight: 500;">
											<?php echo strlen($diff['curr']) ? $diff['curr'] : '--'; ?>
										</span>

										<i class="fas fa-check-circle ok medium-big"></i>
									</td>
									
									<td>
										<span><?php echo strlen($diff['prev']) ? $diff['prev'] : '--'; ?></span>

										<i class="fas fa-dot-circle no medium-big"></i>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
					<?php
				}

				// look for any items differences
				if (!empty($data['items']))
				{
					// display added products
					if (isset($data['items']['insert']))
					{
						?>
						<table class="git-table">
							<thead>
								<tr>
									<th width="30%"><b><?php echo JText::_('VRMANAGETKSTOCK1'); ?></b></th>
									<th width="15%"><b><?php echo JText::_('VRMANAGETKRES20'); ?></b></th>
									<th width="15%"><b><?php echo JText::_('VRMANAGETKPRODUCT2'); ?></b></th>
									<th width="30%"><b><?php echo JText::_('VRMANAGETKRESTITLE4'); ?></b></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($data['items']['insert'] as $item)
								{
									?>
									<tr>
										<td>
											<b><?php echo $item['name']; ?></b>
											<i class="fas fa-plus-circle ok medium-big"></i>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $item['quantity']; ?></span>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $currency->format($item['price']); ?></span>
										</td>

										<td>
											<?php echo $item['notes']; ?>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
						<?php
					}

					// display deleted products
					if (isset($data['items']['delete']))
					{
						?>
						<table class="git-table">
							<thead>
								<tr>
									<th width="30%"><b><?php echo JText::_('VRMANAGETKSTOCK1'); ?></b></th>
									<th width="15%"><b><?php echo JText::_('VRMANAGETKRES20'); ?></b></th>
									<th width="15%"><b><?php echo JText::_('VRMANAGETKPRODUCT2'); ?></b></th>
									<th width="30%"><b><?php echo JText::_('VRMANAGETKRESTITLE4'); ?></b></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($data['items']['delete'] as $item)
								{
									?>
									<tr>
										<td>
											<b><?php echo $item['name']; ?></b>
											<i class="fas fa-minus-circle no medium-big"></i>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $item['quantity']; ?></span>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $currency->format($item['price']); ?></span>
										</td>

										<td>
											<span><?php echo $item['notes']; ?></span>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
						<?php
					}

					// display updated products
					if (isset($data['items']['update']))
					{
						?>
						<table class="git-table">
							<thead>
								<tr>
									<th width="15%">&nbsp;</th>
									<th width="30%"><b><?php echo JText::_('VRMANAGETKSTOCK1'); ?></b></th>
									<th width="15%"><b><?php echo JText::_('VRMANAGETKRES20'); ?></b></th>
									<th width="15%"><b><?php echo JText::_('VRMANAGETKPRODUCT2'); ?></b></th>
									<th width="15%"><b><?php echo JText::_('VRMANAGETKRESTITLE4'); ?></b></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ($data['items']['update'] as $items)
								{
									list($prev_item, $curr_item) = $items;
									?>
									<tr>
										<td>
											<span style="font-weight:500;"><?php echo JText::_('VRE_LOG_OLD_VALUE'); ?></span>
										</td>

										<td>
											<b><?php echo $prev_item['name']; ?></b>
											
											<?php
											if (!empty($prev_item['toppings']))
											{
												foreach ($prev_item['toppings'] as $tg)
												{
													?>
													<div>
														<small><?php echo $tg['title'] . ': ' . $tg['str']; ?></small>
													</div>
													<?php
												}
											}
											?>
											
											<?php
											if ($prev_item['name'] != $curr_item['name'])
											{
												?><i class="fas fa-dot-circle no medium-big"></i><?php
											}
											?>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $prev_item['quantity']; ?></span>

											<?php
											if ($prev_item['quantity'] != $curr_item['quantity'])
											{
												?><i class="fas fa-dot-circle no medium-big"></i><?php
											}
											?>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $currency->format($prev_item['price']); ?></span>

											<?php
											if ($prev_item['price'] != $curr_item['price'])
											{
												?><i class="fas fa-dot-circle no medium-big"></i><?php
											}
											?>
										</td>

										<td>
											<?php
											if ($prev_item['notes'])
											{
												?>
												<a href="javascript:void(0);">
													<i class="fas fa-comment medium-big hasTooltip" title="<?php echo $this->escape($prev_item['notes']); ?>"></i>
												</a>
												<?php
											}
											?>

											<?php
											if ($prev_item['notes'] != $curr_item['notes'])
											{
												?><i class="fas fa-dot-circle no medium-big"></i><?php
											}
											?>
										</td>
									</tr>

									<tr>
										<td>
											<span style="font-weight:500;"><?php echo JText::_('VRE_LOG_NEW_VALUE'); ?></span>
										</td>

										<td>
											<b><?php echo $curr_item['name']; ?></b>

											<?php
											if (!empty($curr_item['toppings']))
											{
												foreach ($curr_item['toppings'] as $tg)
												{
													?>
													<div>
														<small><?php echo $tg['title'] . ': ' . $tg['str']; ?></small>
													</div>
													<?php
												}
											}
											?>

											<?php
											if ($prev_item['name'] != $curr_item['name'])
											{
												?><i class="fas fa-check-circle ok medium-big"></i><?php
											}
											?>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $curr_item['quantity']; ?></span>

											<?php
											if ($prev_item['quantity'] != $curr_item['quantity'])
											{
												?><i class="fas fa-check-circle ok medium-big"></i><?php
											}
											?>
										</td>
										
										<td>
											<span style="font-weight:500;"><?php echo $currency->format($curr_item['price']); ?></span>

											<?php
											if ($prev_item['price'] != $curr_item['price'])
											{
												?><i class="fas fa-check-circle ok medium-big"></i><?php
											}
											?>
										</td>

										<td>
											<?php
											if ($curr_item['notes'])
											{
												?>
												<a href="javascript:void(0);">
													<i class="fas fa-comment medium-big hasTooltip" title="<?php echo $this->escape($curr_item['notes']); ?>"></i>
												</a>
												<?php
											}
											?>

											<?php
											if ($prev_item['notes'] != $curr_item['notes'])
											{
												?><i class="fas fa-check-circle ok medium-big"></i><?php
											}
											?>
										</td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
						<?php
					}
				}
				?>
				
			</div>
			<?php
		}
		?>

	</div>

</div>
