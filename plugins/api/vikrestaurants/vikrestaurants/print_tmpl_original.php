<?php
/** 
 * @package   	VikRestaurants
 * @subpackage 	com_vikrestaurants
 * @author    	Matteo Galletti - e4j
 * @copyright 	Copyright (C) 2018 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license  	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link 		https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$type = $this->type;
$rows = $this->rows;

$date_format = VikRestaurants::getDateFormat(true);
$time_format = VikRestaurants::getTimeFormat(true);

$curr_symb = VikRestaurants::getCurrencySymb(true);
$symb_pos = VikRestaurants::getCurrencySymbPosition(true);

// load site language to include all tags about payment and prices (taxes, delivery cost, etc...)
VikRestaurants::loadLanguage(VikRestaurants::getDefaultLanguage('site'));

$VikRestaurants_css = (JUri::root() . 'administrator/components/com_vikrestaurants/assets/css/vikrestaurants.css');

$c_logo = VikRestaurants::getCompanyLogoPath(true);
$logo_str = '';
if( strlen($c_logo) > 0 && file_exists( JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_vikrestaurants'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.$c_logo ) ) {
    $logo_str = '<img src="'.JUri::root().'components/com_vikrestaurants/assets/media/'.$c_logo.'"/>';
}

?>
<html lang="en-gb" dir="ltr">
<head>
    <meta charset="utf-8" />
    <link href="<?php echo $VikRestaurants_css; ?>" rel="stylesheet" />
	<style>
		/*        @page {
            margin: 6mm 3mm;
        }
        @media print {
            body {
                margin: 6mm 3mm;
            }
	}*/

        body.contentpane {
			/*     margin: 6mm 3mm;  */
			padding: 6mm 6mm;
        }

        .tk-print-box {
            border: none;
            padding: 0;
			font-family: Arial;
        }
        .tk-item {
            padding: 0;
            border: none;
        }

        .tk-print-box .tk-field .tk-label {
            width: auto;
        }

        .tk-item .tk-details .name {
            width: 60%;
        }

        .tk-item .tk-details .quantity {
            width: 30%;
        }

        .tk-item .tk-details .price {
            width: 100%;
        }
		.tk-value, .tk-label , .tk-details, .tk-amount, .tk-field {
			font-size: 16px;
			font-family: Arial;
			color: black;
			font-weight: 600;
		}
		.qr-wrapper {
			margin-top: 20px;
			width: 100%;
			text-align: center;
		}

        body.contentpane {
			/*            width: 148mm;
            height: 105mm;
			box-sizing: border-box;
			margin: 8mm 8mm;*/
			width: 100%;
			padding: 8mm 8mm;
        }

        .beauti-field {
            font-size: 25px;
        }

        .beauti-small {
            font-size: 18px;
        }

        .beauti-field img {
            margin: 0 20%;
			width: 60%;
        }

        .beauti-center {
            text-align: center;
        }

        .beauti-bold {
            font-weight: bold;
        }
    </style>
</head>

<body class="contentpane component <?php echo $type; ?>">
<div class="vr-printer-layout">

<!--	--><?php //if( strlen($this->text['header']) ) { ?>
<!--		<div class="vr-printer-header">--><?php //echo $this->text['header']; ?><!--</div>-->
<!--	--><?php //} ?>

	<?php foreach( $rows as $i => $r ) {
		
		$fields = json_decode($r['custom_f'], true);

		$has_custom_fields = false;
		foreach( $fields as $k => $v ) {
			$has_custom_fields = $has_custom_fields || strlen($v);
		}
		 
		if( $type == 'restaurant' && $r['status'] == 'PENDING') {?>
			<!-- HEAD -->
			<div class="tk-print-box">
				<div class="tk-field">
					<span class="tk-label"><?php echo JText::_('VRORDERNUMBER').':'; ?></span>
                    <br>
					<span class="tk-value"><?php echo $r['id']." - ".$r['sid']; ?></span>
				</div>
				<div class="tk-field">
					<span class="tk-label"><?php echo JText::_('VRORDERSTATUS').':'; ?></span>
					<span class="tk-value order-<?php echo strtolower($r['status']); ?>"><?php echo strtoupper(JText::_('VRRESERVATIONSTATUS'.$r['status'])); ?></span>
				</div>
				<div class="tk-field">
<!--					<span class="tk-label">--><?php //echo JText::_('VRORDERDATETIME').':'; ?><!--</span>-->
					<span class="tk-value"><?php echo date($date_format." ".$time_format, $r['checkin_ts']); ?></span>
				</div>
                <hr style="border-top: dotted 1px;"/>
				<div class="tk-field">
					<span class="tk-label"><?php echo JText::_('VRORDERPEOPLE').':'; ?></span>
					<span class="tk-value"><?php echo $r['people']; ?></span>
				</div>
				<div class="tk-field">
					<span class="tk-label"><?php echo JText::_('VRORDERTABLE'); ?></span>
					<span class="tk-value"><?php echo $r['table_name']; ?></span>
				</div>
                <hr style="border-top: dotted 1px;"/>
<!--				--><?php //if( !empty($r['payment_name']) ) { ?>
<!--					<div class="tk-field">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRORDERPAYMENT').':'; ?><!--</span>-->
<!--						<span class="tk-value">--><?php //echo $r['payment_name']; ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!--				--><?php //if( $r['deposit'] > 0 ) { ?>
<!--					<div class="tk-field">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRMANAGERESERVATION9').':'; ?><!--</span>-->
<!--						<span class="tk-value">-->
<!--							--><?php //echo VikRestaurants::printPriceCurrencySymb($r['deposit'], $curr_symb, $symb_pos, true); ?>
<!--						</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!--				--><?php //if( !empty($r['coupon_str']) ) {
//					list($code, $pt, $value) = explode(';;', $r['coupon_str']);
//					?>
<!--					<div class="tk-field">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRORDERCOUPON').':'; ?><!--</span>-->
<!--						<span class="tk-value">--><?php //echo $code." : ".($pt == 1 ? $value.'%' : VikRestaurants::printPriceCurrencySymb($value, $curr_symb, $symb_pos, true)); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>


 			<!-- CUSTOMER DETAILS -->
			<?php if( $has_custom_fields ) { ?>

                    <div class="tk-field">
                        <span class="tk-value"><?php echo $fields['CUSTOMF_NAME'] . ' ' . $fields['CUSTOMF_LNAME'] ?></span>
                    </div>
                    <div class="tk-field">
                        <span class="tk-value"><?php echo $fields['CUSTOMF_EMAIL'] ?></span>
                    </div>
                    <div class="tk-field">
                        <span class="tk-value"><?php echo $fields['CUSTOMF_PHONE'] ?></span>
                    </div>

<!--					--><?php //foreach( $fields as $k => $v ) {
//						if( strlen($v) ) { ?>
<!--							<div class="tk-field">-->
<!--								<span class="tk-label">--><?php //echo JText::_($k).':'; ?><!--</span>-->
<!--								<span class="tk-value">--><?php //echo $v ?><!--</span>-->
<!--							</div>-->
<!--						--><?php //} ?>
<!--					--><?php //} ?>

			<?php } ?>
                <!-- ITEMS -->
<!--			--><?php //if( count($r['items']) ) { ?>
<!--				<div class="tk-print-box">-->
<!--					--><?php //foreach( $r['items'] as $item ) { ?>
<!--						<div class="tk-item">-->
<!--							<div class="tk-details">-->
<!--								<span class="name">--><?php //echo $item['name']; ?><!--</span>-->
<!--								<span class="quantity">x--><?php //echo $item['quantity']; ?><!--</span>-->
<!--								<span class="price">--><?php //echo VikRestaurants::printPriceCurrencySymb($item['price'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--							</div>-->
<!---->
<!--							--><?php //if( strlen($item['notes']) ) { ?>
<!--								<div class="tk-notes">--><?php //echo $item['notes']; ?><!--</div>-->
<!--							--><?php //} ?>
<!--						</div>-->
<!--					--><?php //} ?>
<!--				</div>-->
<!--			--><?php //} ?>
<!---->
                <!-- ORDER TOTAL -->
<!--			--><?php //if ($r['bill_value'] > 0) { ?>
<!--				<div class="tk-print-box">-->
<!---->
                        <!-- TIP -->
<!--					--><?php //if ($r['tip_amount'] > 0) { ?>
<!--						<div class="tk-total-row">-->
<!--							<span class="tk-label">--><?php //echo JText::_('VRTKCARTTOTALTIP'); ?><!--</span>-->
<!--							<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['tip_amount'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--						</div>-->
<!--					--><?php //} ?>
<!---->
<!--					<div class="tk-total-row">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRTKCARTTOTALPRICE'); ?><!--</span>-->
<!--						<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['bill_value'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--					</div>-->
<!--				</div>-->
<!--			--><?php //} ?>
            </div>
            <hr style="border-top: dotted 1px;"/>
        <?php } else if( $type == 'restaurant' && $r['status'] == 'CONFIRMED') { ?>
            <div class="beauti-print-box">
                <div class="beauti-field beauti-small">
                    <span class="beauti-value"><?php echo $r['table_name']; ?></span>
                </div>
                <div class="beauti-field">
                    <?php echo($logo_str); ?>
                </div>
                <hr style="border-top: dotted 1px;"/>
                <div class="beauti-field beauti-center">
                    <span class="beauti-value beauti-bold">Reserviert</span>
                </div>
                <div class="beauti-field beauti-center">
                    <span class="beauti-value">ab&nbsp;<?php echo date($time_format, $r['checkin_ts']); ?></span>
                </div>
                <hr style="border-top: dotted 1px;"/>
                <div class="beauti-field beauti-center">
                    <span class="beauti-value"><?php echo $fields['CUSTOMF_NAME'] . ' ' . $fields['CUSTOMF_LNAME'] ?></span>
                </div>
                <div class="beauti-field beauti-center">
                    <span class="beauti-value"><?php echo $r['people']; ?>&nbsp;Pers.</span>
                </div>
            </div>
		<?php } else { ?>
			<!-- HEAD -->
			<div class="tk-print-box">
				<div class="tk-field">
					<span class="tk-label"><?php echo JText::_('VRORDERNUMBER').':'; ?></span>
                    <br>
					<span class="tk-value"><?php echo $r['id']." - ".$r['sid']; ?></span>
				</div>
				<div class="tk-field">
					<span class="tk-label"><?php echo JText::_('VRORDERSTATUS').':'; ?></span>
					<span class="tk-value order-<?php echo strtolower($r['status']); ?>"><?php echo strtoupper(JText::_('VRRESERVATIONSTATUS'.$r['status'])); ?></span>
				</div>
				<div class="tk-field">
<!--					<span class="tk-label">--><?php //echo JText::_('VRORDERDATETIME').':'; ?><!--</span>-->
					<span class="tk-value"><?php echo date($date_format." ".$time_format, $r['checkin_ts']); ?></span>
				</div>
				<div class="tk-field">
					<span class="tk-label"><?php echo JText::_('VRTKORDERDELIVERYSERVICE').':'; ?></span>
					<span class="tk-value"><?php echo JText::_($r['delivery_service'] ? 'VRTKORDERDELIVERYOPTION' : 'VRTKORDERPICKUPOPTION'); ?></span>
				</div>
<!--				--><?php //if( !empty($r['payment_name']) ) { ?>
<!--					<div class="tk-field">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRORDERPAYMENT').':'; ?><!--</span>-->
<!--						<span class="tk-value">--><?php //echo $r['payment_name']; ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!--				--><?php //if( $r['total_to_pay'] > 0 ) { ?>
<!--					<div class="tk-field">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRTKORDERTOTALTOPAY').':'; ?><!--</span>-->
<!--						<span class="tk-value">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['total_to_pay'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!--				--><?php //if( !empty($r['coupon_str']) ) {
//					list($code, $pt, $value) = explode(';;', $r['coupon_str']);
//					?>
<!--					<div class="tk-field">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRORDERCOUPON').':'; ?><!--</span>-->
<!--						<span class="tk-value">--><?php //echo $code." : ".($pt == 1 ? $value.'%' : VikRestaurants::printPriceCurrencySymb($value, $curr_symb, $symb_pos, true)); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
				
				
            <hr style="border-top: dotted 1px;"/>

			<!-- CART -->
				<?php foreach( $r['items'] as $item ) { ?>
					<div class="tk-item">
						<div class="tk-details">
							<span class="name"><?php echo $item['name'].(!empty($item['option_name']) ? ' - '.$item['option_name'] : ''); ?></span>
							<span class="quantity">x<?php echo $item['quantity']; ?></span>
							<span class="price"><?php echo VikRestaurants::printPriceCurrencySymb($item['price'], $curr_symb, $symb_pos, true); ?></span>
						</div>

						<?php if( count($item['toppings_groups']) ) { ?>
							<div class="tk-toppings-cont">
								<?php foreach( $item['toppings_groups'] as $group ) { ?>
									<div class="tk-toppings-group">
										<span class="title"><?php echo $group['title']; ?>:&nbsp;</span>
										<span class="toppings">
											<?php foreach( $group['toppings'] as $k => $topping ) {
												echo ($k > 0 ? ', ' : '').$topping['name'];
											} ?>
										</span>
									</div>
								<?php } ?>
							</div>
						<?php } ?>

						<?php if( strlen($item['notes']) ) { ?>
							<div class="tk-notes"><?php echo $item['notes']; ?></div>
						<?php } ?>
					</div>
				<?php } ?>
			<hr style="border-top: dotted 1px;"/>

			<!-- ORDER TOTAL -->
			<?php
			$net = $r['total_to_pay']-$r['taxes']-$r['pay_charge']-$r['delivery_charge'];
			?>
<!--				<div class="tk-total-row">-->
<!--					<span class="tk-label">--><?php //echo JText::_('VRTKCARTTOTALNET'); ?><!--</span>-->
<!--					<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($net, $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--				</div>-->
<!--			-->
<!--				--><?php //if ($r['delivery_charge'] != 0) { ?>
<!--					<div class="tk-total-row">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRTKCARTTOTALSERVICE'); ?><!--</span>-->
<!--						<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['delivery_charge'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!--			-->
<!--				--><?php //if ($r['pay_charge'] != 0) { ?>
<!--					<div class="tk-total-row">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRTKCARTTOTALPAYCHARGE'); ?><!--</span>-->
<!--						<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['pay_charge'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!--			-->
<!--				--><?php //if ($r['taxes'] != 0) { ?>
<!--					<div class="tk-total-row">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRTKCARTTOTALTAXES'); ?><!--</span>-->
<!--						<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['taxes'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!--			-->
<!--				--><?php //if ($r['discount_val'] != 0) { ?>
<!--					<div class="tk-total-row">-->
<!--						<span class="tk-label red">--><?php //echo JText::_('VRTKCARTTOTALDISCOUNT'); ?><!--</span>-->
<!--						<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['discount_val'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
<!---->
				    <!-- TIP -->
<!--				--><?php //if ($r['tip_amount'] != 0) { ?>
<!--					<div class="tk-total-row">-->
<!--						<span class="tk-label">--><?php //echo JText::_('VRTKCARTTOTALTIP'); ?><!--</span>-->
<!--						<span class="tk-amount">--><?php //echo VikRestaurants::printPriceCurrencySymb($r['tip_amount'], $curr_symb, $symb_pos, true); ?><!--</span>-->
<!--					</div>-->
<!--				--><?php //} ?>
			
				<div class="tk-total-row">
					<span class="tk-label"><?php echo JText::_('VRTKCARTTOTALPRICE'); ?></span>
					<span class="tk-amount"><?php echo VikRestaurants::printPriceCurrencySymb($r['total_to_pay'], $curr_symb, $symb_pos, true); ?></span>
				</div>
			</div>
            <hr style="border-top: dotted 1px;"/>

			<!-- CUSTOMER DETAILS -->
			<?php if( $has_custom_fields ) { ?>
				    <div class="tk-field">
                        <span class="tk-value"><?php echo $fields['CUSTOMF_TKNAME'] ?></span>
                    </div>
				<?php if ($r['delivery_service']) { ?>
					<div class="tk-field">
						<span class="tk-value"><?php echo $fields['CUSTOMF_TKADDRESS'] ?></span>
					</div>
					<div class="tk-field">
						<span class="tk-value"><?php echo  $fields['CUSTOMF_TKZIP'] ?></span>
					</div>
					<div class="tk-field">
						<span class="tk-value"><?php echo  $fields['CUSTOMF_TKNOTE'] ?></span>
					</div>
				<?php } ?>
                    <div class="tk-field">
                        <span class="tk-value"><?php echo $fields['CUSTOMF_TKEMAIL'] ?></span>
                    </div>
                    <div class="tk-field">
                        <span class="tk-value"><?php echo $fields['CUSTOMF_TKPHONE'] ?></span>
                    </div>

<!--					--><?php //foreach( $fields as $k => $v ) {
//						if( strlen($v) ) { ?>
<!--							<div class="tk-field">-->
<!--								<span class="tk-label">--><?php //echo JText::_($k).':'; ?><!--</span>-->
<!--								<span class="tk-value">--><?php //echo $v ?><!--</span>-->
<!--							</div>-->
<!--						--><?php //} ?>
<!--					--><?php //} ?>
			<?php } ?>
	<?php if ($r['delivery_service']) { ?>
	<div class="qr-wrapper">
		<img src="http://api.qrserver.com/v1/create-qr-code/?size=120x120&data=https://maps.google.com/?q=<?php echo $fields['CUSTOMF_TKADDRESS'] . ', ' . $fields['CUSTOMF_TKZIP']  . ', switzerland' ?>" alt="qr-code" />
	</div>
	<?php } ?>
	
		<?php } 
		
	} ?>

<!--	--><?php //if( strlen($this->text['footer']) ) { ?>
<!--		<div class="vr-printer-footer">--><?php //echo $this->text['footer']; ?><!--</div>-->
<!--	--><?php //} ?>

</div>

</div>
</body>
</html>