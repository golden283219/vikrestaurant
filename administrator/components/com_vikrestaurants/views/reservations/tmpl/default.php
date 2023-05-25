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

$ordering = $this->ordering;

$currency = VREFactory::getCurrency();

$config = VREFactory::getConfig();

$created_by_default = JText::_('VRMANAGERESERVATION23');

// ORDERING LINKS

foreach (array('r.id', 'r.checkin_ts', 'r.purchaser_nominative', 'r.bill_value', 'r.status') as $c)
{
	if (empty($ordering[$c]))
	{
		$ordering[$c] = 0;
	}
}

$links = array(
	OrderingManager::getLinkColumnOrder('reservations', JText::_('JGRID_HEADING_ID'), 'r.id', $ordering['r.id'], 1, $filters, 'vrheadcolactive'.(($ordering['r.id'] == 2) ? 1 : 2) ),
	OrderingManager::getLinkColumnOrder('reservations', JText::_('VRMANAGERESERVATION3'), 'r.checkin_ts', $ordering['r.checkin_ts'], 1, $filters, 'vrheadcolactive'.(($ordering['r.checkin_ts'] == 2) ? 1 : 2) ),
	OrderingManager::getLinkColumnOrder('reservations', JText::_('VRMANAGERESERVATION17'), 'r.purchaser_nominative', $ordering['r.purchaser_nominative'], 1, $filters, 'vrheadcolactive'.(($ordering['r.purchaser_nominative'] == 2) ? 1 : 2) ),
	OrderingManager::getLinkColumnOrder('reservations', JText::_('VRMANAGERESERVATION10'), 'r.bill_value', $ordering['r.bill_value'], 1, $filters, 'vrheadcolactive'.(($ordering['r.bill_value'] == 2) ? 1 : 2) ),
	OrderingManager::getLinkColumnOrder('reservations', JText::_('VRMANAGERESERVATION12'), 'r.status', $ordering['r.status'], 1, $filters, 'vrheadcolactive'.(($ordering['r.status'] == 2) ? 1 : 2) ),
);

$vik = VREApplication::getInstance();

// get listable columns
$listable_fields = VikRestaurants::getListableFields();
// get custom fields that should be displayed in the list
$listable_cf = $config->getArray('listablecf', array());

$canEdit      = JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants');
$canEditState = JFactory::getUser()->authorise('core.edit.state', 'com_vikrestaurants');

$has_filters = $this->hasFilters();

// get all reservation codes
$allCodes = JHtml::_('vikrestaurants.rescodes', 1);

$date_format = $config->get('dateformat');
$time_format = $config->get('timeformat');

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

		<?php
		if (count($rows) == 1 && strlen($rows[0]['cc_details']))
		{
			?>
			<div class="btn-group pull-right">
				<button type="button" class="btn btn-primary" onclick="SELECTED_ORDER=<?php echo $rows[0]['id']; ?>;vrOpenJModal('ccdetails', null, true); return false;">
					<i class="fas fa-credit-card" style="margin-right: 4px;"></i>&nbsp;<?php echo JText::_('VRSEECCDETAILS'); ?>
				</button>
			</div>
			<?php
		}
		?>
	</div>

	<div class="btn-toolbar hidden-phone" id="vr-search-tools" style="height: 32px;<?php echo ($has_filters ? '' : 'display: none;'); ?>">

		<?php
		// get order statuses
		$options = JHtml::_('vikrestaurants.orderstatuses', '*', true);

		// add closure status too
		$options[] = JHtml::_('select.option', 'CLOSURE', 'VRRESERVATIONSTATUSCLOSURE');
		?>
		<div class="btn-group pull-left">
			<select name="ordstatus" id="vr-ordstatus-sel" class="<?php echo ($filters['ordstatus'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['ordstatus'], true); ?>
			</select>
		</div>

		<?php
		// get rooms
		$options = JHtml::_('vikrestaurants.rooms', true);

		// add empty option
		array_unshift($options, JHtml::_('select.option', 0, JText::_('VRMAPSCHOOSEROOM')));
		?>
		<div class="btn-group pull-left">
			<select name="id_room" id="vr-room-sel" class="<?php echo ($filters['id_room'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
				<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['id_room']); ?>
			</select>
		</div>

		<?php
		// get operators
		$options = JHtml::_('vikrestaurants.operators', $group = 1);

		if ($options)
		{
			// add empty option
			array_unshift($options, JHtml::_('select.option', 0, JText::_('VRE_FILTER_SELECT_OPERATOR')));
			?>
			<div class="btn-group pull-left">
				<select name="id_operator" id="vr-operator-sel" class="<?php echo ($filters['id_operator'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
					<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['id_operator']); ?>
				</select>
			</div>
			<?php
		}
		?>

		<div class="btn-group pull-left vr-toolbar-setfont">
			<?php
			$attr = array();
			$attr['class']    = 'vrdatefilter';
			$attr['onChange'] = "document.adminForm.submit();";

			echo $vik->calendar($filters['datefilter'], 'datefilter', 'vrdatefilter', null, $attr);
			?>
		</div>

		<?php
		if ($filters['datefilter'])
		{
			// get working shifts
			$options = JHtml::_('vrehtml.admin.dayshifts', 1, $filters['datefilter']);

			// make sure the working shifts are available for the searched day
			if ($options)
			{
				array_unshift($options, JText::_('VRRESERVATIONSHIFTSEARCH'));
				?>
				<div class="btn-group pull-left">
					<select name="shift" id="vr-shift-sel" class="<?php echo ($filters['shift'] ? 'active' : ''); ?>" onchange="document.adminForm.submit();">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $filters['shift'], true); ?>
					</select>
				</div>
				<?php
			}
		}
		?>

	</div>

	<?php
	if ($filters['ids'] && count($rows) > 0)
	{
		?>
		<div class="btn-toolbar">
			<div class="btn-group pull-left vr-toolbar-setfont">
				<?php
				echo JText::sprintf(
					'VRFILTERCIDRES', 
					'<strong>' . $rows[0]['table_name'] . '</strong>', 
					'<strong>' . date("$date_format @ $time_format", $rows[0]['checkin_ts']) . '</strong>'
				);
				?>
			</div>
		</div>
		<?php
	}
	?>

	<!-- HIDDEN LINKS -->

	<a href="index.php?option=com_vikrestaurants&amp;view=statistics&amp;group=restaurant" id="statistics-link" style="display:none;"></a>
	
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
				<th width="1%">
					<?php echo $vik->getAdminToggle(count($rows)); ?>
				</th>
				
				<?php
				if (in_array('id', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="4%" style="text-align: left;"><?php echo $links[0]; ?></th><?php
				}

				if (in_array('sid', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION2'); ?></th><?php
				}

				if (in_array('checkin_ts', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo $links[1]; ?></th><?php
				}

				if (in_array('customer', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('left'); ?>" width="10%" style="text-align: left;"><?php echo $links[2]; ?></th><?php
				}

				if (in_array('phone', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="8%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION16'); ?></th><?php
				}

				/**
				 * Here's the custom fields that should be shown within the head of the table.
				 *
				 * @since 1.7.4
				 */
				foreach ($listable_cf as $field)
				{
					?><th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="10%" style="text-align: center;"><?php echo JText::_($field); ?></th><?php
				}

				if (in_array('info', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass(); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION7'); ?></th><?php
				}

				if (in_array('billval', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="8%" style="text-align: left;"><?php echo $links[3]; ?></th><?php
				}

				if (in_array('payment', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo JText::_('VRMANAGERESERVATION20'); ?></th><?php
				}

				if (in_array('coupon', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION8'); ?></th><?php
				}

				if (in_array('rname', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="7%" style="text-align: center;"><?php echo JText::_('VRMANAGETABLE4'); ?></th><?php
				}

				if (in_array('tname', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION5'); ?></th><?php
				}

				if (in_array('rescode', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('hidden-phone'); ?>" width="5%" style="text-align: center;"><?php echo JText::_('VRMANAGERESERVATION19'); ?></th><?php
				}

				if (in_array('status', $listable_fields))
				{
					?><th class="<?php echo $vik->getAdminThClass('left hidden-phone'); ?>" width="10%" style="text-align: left;"><?php echo $links[4]; ?></th><?php
				}
				?>
			</tr>
		<?php echo $vik->closeTableHead(); ?>

		<?php
		$kk = 0;
		for ($i = 0; $i < count($rows); $i++)
		{
			$row = $rows[$i];

			$oid_tooltip = '';

			if ($row['created_on'] > 0)
			{
				if ($row['created_by'] > 0)
				{
					$created_by = $row['createdby_name'];
				}
				else
				{
					$created_by = $created_by_default;
				}

				$oid_tooltip = JText::sprintf('VRRESLISTCREATEDTIP', date($date_format . ' ' . $time_format, $row['created_on']), $created_by);
			}

			/**
			 * Adjust attributes for CLOSURE reservation.
			 *
			 * @since 1.8
			 */
			if ($row['closure'])
			{
				// do not show (CLOSURE) nominative
				$row['purchaser_nominative'] = '';
				// use a different status
				$row['status'] = 'CLOSURE';
			}

			// decode stored CF data
			$cf_json = (array) json_decode($row['custom_f'], true);

			/**
			 * Translate custom fields values stored in the database.
			 *
			 * @since 1.8
			 */
			$cf_json = VRCustomFields::translateObject($cf_json, $this->customFields);
			?>
			<tr class="row<?php echo $kk; ?>">

				<td>
					<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $row['id']; ?>" onClick="<?php echo $vik->checkboxOnClick(); ?>">
				</td>
				
				<?php
				if (in_array('id', $listable_fields))
				{
					?>
					<td class="hidden-phone">
						<span class="hasTooltip" title="<?php echo $oid_tooltip; ?>"><?php echo $row['id']; ?></span>

						<?php
						// hide order status folder in case of CLOSURE
						if (!$row['closure'])
						{
							?>
							<a href="index.php?option=com_vikrestaurants&amp;view=rescodesorder&amp;id_order=<?php echo $row['id']; ?>&amp;group=1" class="td-pull-right">
								<i class="fa<?php echo (!$row['order_status_count'] ? 'r' : 's'); ?> fa-folder big" id="vrordfoldicon<?php echo $row['id']; ?>"></i>
							</a>
							<?php
						}
						?>
					</td>
					<?php
				}

				if (in_array('sid', $listable_fields))
				{
					?>
					<td class="hidden-phone">
						<div class="td-pull-left">
							<div>
								<?php
								if ($canEdit)
								{
									?>
									<a href="index.php?option=com_vikrestaurants&amp;task=reservation.edit&amp;cid[]=<?php echo $row['id']; ?>">
										<?php echo $row['sid']; ?>
									</a>
									<?php
								}
								else
								{
									echo $row['sid'];
								}
								?>
							</div>

							<?php
							if ($row['created_on'] > 0)
							{
								?>
								<div class="td-secondary">
									<?php echo JHtml::_('date', JDate::getInstance($row['created_on']), JText::_('DATE_FORMAT_LC3') . ' ' . $time_format, date_default_timezone_get()); ?>
								</div>
								<?php
							}
							?>
						</div>

						<?php
						// display order status link in case the ID column is turned off
						if (!in_array('id', $listable_fields))
						{
							?>
							<a href="index.php?option=com_vikrestaurants&amp;view=rescodesorder&amp;id_order=<?php echo $row['id']; ?>&amp;group=1" class="td-pull-right">
								<i class="fa<?php echo (!$row['order_status_count'] ? 'r' : 's'); ?> fa-folder big" id="vrordfoldicon<?php echo $row['id']; ?>"></i>
							</a>
							<?php
						}
						?>
					</td>
					<?php
				}

				if (in_array('checkin_ts', $listable_fields)) 
				{
					// use check-in date as primary field by default
					$primary   = JHtml::_('date', JDate::getInstance($row['checkin_ts']), JText::_('DATE_FORMAT_LC3'), date_default_timezone_get());
					$secondary = date($time_format, $row['checkin_ts']);

					if (!empty($filters['datefilter']))
					{
						// switch time and date because it doesn't make sense having
						// a primary column repeated for all the rows
						$tmp       = $primary;
						$primary   = $secondary;
						$secondary = $tmp;	
					}

					?>
					<td>
						<div class="td-primary">
							<?php
							// make checkin date clickable to access the details of the reservation
							// in case the "Order Key" column is turned off
							if ($canEdit && !in_array('sid', $listable_fields))
							{
								?>
								<a href="index.php?option=com_vikrestaurants&amp;task=reservation.edit&amp;cid[]=<?php echo $row['id']; ?>">
									<?php echo $primary; ?>
								</a>
								<?php
							}
							else
							{
								echo $primary;
							}
							?>
						</div>

						<div class="td-secondary">
							<span class="checkin-time">
								<?php echo $secondary; ?>
							</span>

							<?php
							// hide people in case of CLOSURE
							if (in_array('people', $listable_fields) && !$row['closure'])
							{
								?>
								<span class="td-pull-right">
									<?php
									echo $row['people'] . ' ';

									for ($p = 1; $p <= min(array(2, $row['people'])); $p++)
									{
										?><i class="fas fa-male"></i><?php
									}
									?>
								</span>
								<?php
							}
							?>
						</div>
					</td>
					<?php
				}

				if (in_array('customer', $listable_fields))
				{
					?>
					<td>
						<?php
						// use primary for mail in case the nominative is empty
						$mail_class = 'td-primary';

						if ($row['purchaser_nominative'])
						{
							// nominative not empty, use secondary class for mail
							$mail_class = 'td-secondary';
							?>
							<div class="td-primary">
								<?php
								if ($row['id_user'] > 0)
								{
									?>
									<a href="javascript: void(0);" onclick="SELECTED_USER=<?php echo $row['id_user']; ?>;vrOpenJModal('custinfo', null, true); return false;">
										<?php echo $row['purchaser_nominative']; ?>
									</a>
									<?php
								}
								else
								{
									echo $row['purchaser_nominative'];
								}
								?>
							</div>
							<?php
						}

						if (in_array('mail', $listable_fields))
						{
							?>
							<div class="<?php echo $mail_class; ?>">
								<?php echo $row['purchaser_mail']; ?>
							</div>
							<?php
						}
						?>

						<?php
						switch ($row['status'])
						{
							case 'PENDING':
								$badge_class = 'warning';
								break;

							case 'CONFIRMED':
								$badge_class = 'success';
								break;

							default:
								$badge_class = 'important';
						}
						?>
						<div class="badge badge-<?php echo $badge_class; ?> mobile-only">
							<?php echo JText::_('VRRESERVATIONSTATUS' . $row['status']); ?>
						</div>
					</td>
					<?php
				}

				if (in_array('phone', $listable_fields))
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php echo $row['purchaser_phone']; ?>
					</td>
					<?php
				}

				/**
				 * Here's the custom fields that should be shown within the body of the table.
				 *
				 * @since 1.7.4
				 */
				foreach ($listable_cf as $field)
				{
					/**
					 * Translate field name in order to support
					 * those fields that still use the old
					 * translation method. 
					 *
					 * @since 1.8
					 */
					$field = JText::_($field);
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php echo isset($cf_json[$field]) ? $cf_json[$field] : ''; ?>
					</td>
					<?php
				}

				if (in_array('info', $listable_fields))
				{
					?>
					<td style="text-align: center;">
						<?php
						// hide info link in case of CLOSURE
						if (!$row['closure'])
						{
							?>
							<a href="javascript: void(0);" onclick="SELECTED_ORDER=<?php echo $row['id']; ?>;vrOpenJModal('respinfo', null, true); return false;">
								<i class="fas fa-tag big-2x fa-flip-horizontal"></i>
							</a>
							<?php
						}
						?>
					</td>
					<?php
				}

				if (in_array('billval', $listable_fields))
				{
					?>
					<td class="hidden-phone">
						<?php
						// hide bill in case of closure
						if (!$row['closure'])
						{
							?>
							<div class="td-pull-left" style="max-width: calc(100% - 16px);">
								<div class="td-primary">
									<?php echo $currency->format($row['bill_value']); ?>
								</div>

								<div class="td-secondary">
									<?php
									if ($row['bill_value'] > max(array($row['deposit'], $row['tot_paid'])))
									{
										// display remaining balance
										echo JText::sprintf('VRORDERDUE', $currency->format($row['bill_value'] - $row['deposit']));
									}
									else if ($row['deposit'] > 0)
									{
										// display deposit left
										echo JText::sprintf('VRORDERDEP', $currency->format($row['deposit']));
									}

									// display tooltip to inform the administrator that the
									// deposit haven't been paid through VikRestaurants
									if (!$row['bill_closed'] && $row['deposit'] > $row['tot_paid'])
									{
										?>
										<i class="fas fa-info-circle hasTooltip" title="<?php echo addslashes(JText::_('VRORDERDEPNOTPAID')); ?>"></i>
										<?php
									}
									?>
								</div>
							</div>

							<div class="td-pull-right">
								<?php
								if ($canEditState)
								{
									if ($row['bill_value'] > 0)
									{
										// auto-close bill in case the amount is higher than 0
										$change_bill_url = 'index.php?option=com_vikrestaurants&amp;task=reservation.changebill&amp;cid[]=' . $row['id'] . '&amp;state=' . ($row['bill_closed'] == 1 ? 0 : 1);
									}
									else
									{
										// go to "managebill" page as it doesn't make sense to close an empty bill
										$change_bill_url = 'index.php?option=com_vikrestaurants&amp;task=reservation.editbill&amp;cid[]=' . $row['id'];
									}

									?>
									<a href="<?php echo $change_bill_url; ?>">
										<i class="fas fa-<?php echo $row['bill_closed'] ? 'check-circle ok' : 'dot-circle no'; ?> medium-big"></i>
									</a>
									<?php
								}
								else
								{
									?>
									<i class="fas fa-<?php echo $row['bill_closed'] ? 'check-circle ok' : 'dot-circle no'; ?> medium-big"></i>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</td>
					<?php
				}

				if (in_array('payment', $listable_fields))
				{
					?>
					<td class="hidden-phone">
						<?php echo !empty($row['payment_name']) ? $row['payment_name'] : JText::_('VRMANAGECONFIG32'); ?>
					</td>
					<?php
				}

				if (in_array('coupon', $listable_fields))
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php
						if ($row['coupon_str'])
						{
							list($coupon_code, $coupon_amount, $coupon_percentot) = explode(';;', $row['coupon_str']);
							?>
							<span class="badge hasTooltip" title="<?php echo $coupon_code; ?>">
								<?php
								if ($coupon_percentot == 1)
								{
									echo $coupon_amount . '%';
								}
								else
								{
									echo $currency->format($coupon_amount);
								}
								?>
							</span>
							<?php
						}
						?>
					</td>
					<?php
				}

				if (in_array('rname', $listable_fields))
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<span class="badge badge-warning"><?php echo $row['room_name']; ?></span>
					</td>
					<?php
				}

				if (in_array('tname', $listable_fields))
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<span class="badge badge-info badge-table"><?php echo $row['table_name']; ?></span>
						
						<?php
						/**
						 * Check if the reservation has been merged with
						 * other tables to host a larger group.
						 *
						 * @since 1.8
						 */
						if ($row['cluster'])
						{
							foreach (explode(',', $row['cluster']) as $tname)
							{
								?>
								<span class="badge badge-info badge-table"><?php echo $tname; ?></span>
								<?php
							}
						}
						?>
					</td>
					<?php
				}

				if (in_array('rescode', $listable_fields))
				{
					?>
					<td style="text-align: center;" class="hidden-phone">
						<?php
						// hide reservation code in case of CLOSURE
						if (!$row['closure'])
						{
							?>
							<a href="javascript: void(0);" data-id="<?php echo $row['id']; ?>" data-code="<?php echo (int) $row['rescode']; ?>" class="vrrescodelink" id="vrrescodelink<?php echo $row['id']; ?>">
								<?php
								if (empty($row['code_icon']))
								{
									echo !empty($row['code']) ? $row['code'] : '--';
								}
								else
								{
									?>
									<img src="<?php echo VREMEDIA_SMALL_URI . $row['code_icon']; ?>" title="<?php echo $row['code']; ?>" />
									<?php
								}
								?>
							</a>

							<?php
							echo JHtml::_('vrehtml.statuscodes.popup', 1, '#vrrescodelink' . $row['id']);
						}
						?>
					</td>
					<?php
				}

				if (in_array('status', $listable_fields))
				{
					?>
					<td class="hidden-phone">
						<span class="<?php echo 'vrreservationstatus' . strtolower($row['status']); ?>">
							<?php echo JText::_('VRRESERVATIONSTATUS' . $row['status']); ?>
						</span>

						<a href="javascript: void(0);" class="pull-right" onclick="SELECTED_ORDER=<?php echo $row['id']; ?>;vrOpenJModal('history', null, true); return false;">
							<i class="fas fa-history medium-big"></i>
						</a>
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
	
	<!-- invoice submit fields -->
	<input type="hidden" name="notifycust" value="0" />

	<!-- print orders submit fields -->
	<input type="hidden" name="printorders[header]" value="" />
	<input type="hidden" name="printorders[footer]" value="" />
	<input type="hidden" name="printorders[update]" value="0" />

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="reservations" />

	<?php echo JHtml::_('form.token'); ?>
	<?php echo $this->navbut; ?>
</form>

<?php
// order details modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-respinfo',
	array(
		'title'       => JText::_('VRMANAGERESERVATION7'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
	)
);

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

// reservation history modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-history',
	array(
		'title'       => JText::_('VRORDERSTATUSES'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
	)
);

// CC details modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-ccdetails',
	array(
		'title'       => JText::_('VRSEECCDETAILS'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => '', // it will be filled dinamically
	)
);
?>

<!-- INVOICE DIALOG -->
<div id="dialog-invoice" style="display: none;">
	<h3 style="margin-top: 0;"><?php echo JText::_('VRINVOICEDIALOG'); ?></h3>

	<p><?php echo JText::_('VRGENERATEINVOICESTXT'); ?></p>

	<div>
		<?php
		$elem_yes = $vik->initRadioElement('', '', false, 'onClick="notifyCustValueChanged(1);"');
		$elem_no  = $vik->initRadioElement('', '',  true, 'onClick="notifyCustValueChanged(0);"');
		
		echo $vik->openControl(JText::_('VRMANAGEINVOICE7'));
		echo $vik->radioYesNo('notifycust_radio', $elem_yes, $elem_no, false);
		echo $vik->closeControl();
		?>
	</div>
</div>

<!-- PRINT ORDERS DIALOG -->
<div id="dialog-printorders" style="display: none;">
	<?php $printorders_text = VikRestaurants::getPrintOrdersText(); ?>

	<h3 style="margin-top: 0;"><?php echo JText::_('VRPRINT'); ?></h3>

	<div>
		<?php echo $vik->openControl(JText::_('VRPRINTORDERS1')); ?>
			<textarea name="printorders_header" class="full-width" style="height: 50px;resize: vertical;max-height: 120px;"><?php echo $printorders_text['header']; ?></textarea>
		<?php echo $vik->closeControl(); ?>

		<?php echo $vik->openControl(JText::_('VRPRINTORDERS2')); ?>
			<textarea name="printorders_footer" class="full-width" style="height: 50px;resize: vertical;max-height: 120px;"><?php echo $printorders_text['footer']; ?></textarea>
		<?php echo $vik->closeControl(); ?>

		<?php
		$elem_yes = $vik->initRadioElement('', '', false, 'onClick="updatePrintValueChanged(1);"');
		$elem_no  = $vik->initRadioElement('', '',  true, 'onClick="updatePrintValueChanged(0);"');

		echo $vik->openControl(JText::_('VRPRINTORDERS3'));
		echo $vik->radioYesNo('printorders_update', $elem_yes, $elem_no, false);
		echo $vik->closeControl();
		?>
	</div>
</div>

<?php
JText::script('VROK');
JText::script('VRCANCEL');
?>

<script type="text/javascript">

	jQuery(document).ready(function() {
		
		VikRenderer.chosen('.btn-toolbar');

	});
	
	function notifyCustValueChanged(is) {
		jQuery('#adminForm input[name="notifycust"]').val(is);
	}

	function updatePrintValueChanged(is) {
		jQuery('#adminForm input[name="printorders[update]"]').val(is);	
	} 
	
	function clearFilters() {
		jQuery('#vrkeysearch').val('');
		jQuery('#vrdatefilter').val('');
		jQuery('#vr-ordstatus-sel').updateChosen('');
		jQuery('#vr-room-sel').updateChosen(0);

		if (jQuery('#vr-operator-sel').length) {
			jQuery('#vr-operator-sel').updateChosen(0);
		}

		if (jQuery('#vr-shift-sel').length) {
			jQuery('#vr-shift-sel').updateChosen(0);
		}

		jQuery('#adminForm').append('<input type="hidden" name="ids[]" value="0" />');

		document.adminForm.submit();
	}
	
	// JQUERY MODAL
	
	var SELECTED_ORDER = 0;
	var SELECTED_USER  = 0;
	
	function vrOpenJModal(id, url, jqmodal) {
		switch (id) {
			case 'respinfo':
				url = 'index.php?option=com_vikrestaurants&view=orderinfo&tmpl=component&id=' + SELECTED_ORDER;
				break;

			case 'custinfo':
				url = 'index.php?option=com_vikrestaurants&view=customerinfo&tmpl=component&locations=0&id=' + SELECTED_USER;
				break;

			case 'history':
				url = 'index.php?option=com_vikrestaurants&view=orderhistory&tmpl=component&group=1&id=' + SELECTED_ORDER;
				break;

			case 'ccdetails':
				url = 'index.php?option=com_vikrestaurants&view=ccdetails&tmpl=component&tid=0&id=' + SELECTED_ORDER;
				break;
		}

		<?php echo $vik->bootOpenModalJS(); ?>
	}

	// DIALOGS

	// create invoice dialog
	var invoiceDialog = new VikConfirmDialog('#dialog-invoice', 'vik-invoice-confirm');

	// add confirm button
	invoiceDialog.addButton(Joomla.JText._('VROK'), function(task, event) {
		// submit the form
		Joomla.submitform(task);
	});

	// add cancel button
	invoiceDialog.addButton(Joomla.JText._('VRCANCEL'));

	// create print dialog
	var printDialog = new VikConfirmDialog('#dialog-printorders', 'vik-print-confirm');

	// add confirm button
	printDialog.addButton(Joomla.JText._('VROK'), function(task, event) {
		// set up print orders parameters
		jQuery('#adminForm input[name="printorders[header]"]').val(jQuery('textarea[name="printorders_header"]').val());
		jQuery('#adminForm input[name="printorders[footer]"]').val(jQuery('textarea[name="printorders_footer"]').val());

		// prepare form to be submitted on a blank browser tab
		jQuery('#adminForm').attr('target', '_blank');
		// change form view
		jQuery('#adminForm input[name="view"]').val('printorders');

		// submit form (DO NOT SET TASK)
		document.adminForm.submit();

		// restore "reservations" view
		jQuery('#adminForm input[name="view"]').val('reservations');
		// restore form target attribute
		jQuery('#adminForm').attr('target', '');
	});

	// add cancel button
	printDialog.addButton(Joomla.JText._('VRCANCEL'));

	Joomla.submitbutton = function(task) {
		if (task == 'invoice.generate') {
			// show invoice dialog
			invoiceDialog.show(task);
		} else if (task == 'printorders') {
			// show print orders dialog
			printDialog.show(task);
		} else if (task == 'statistics') {
			// extract statistics HREF from link in order to use the correct platform URL
			document.location.href = jQuery('#statistics-link').attr('href');
		} else {
			Joomla.submitform(task, document.adminForm);
		}
	}
	
</script>
