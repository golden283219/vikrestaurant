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

// get global attributes
$room 	 = isset($displayData['room']) 	  ? $displayData['room']	: null;
$tables  = isset($displayData['tables'])  ? $displayData['tables']  : array();
$options = isset($displayData['options']) ? $displayData['options'] : new stdClass;
$codes   = isset($displayData['codes'])   ? $displayData['codes']   : array();

if (!$room)
{
	return;
}

$app = JFactory::getApplication();

$id_table = $app->input->getUint('id_table', 0);
$id_order = $app->input->getUint('id_order', 0);

$itemid = $app->input->get('Itemid', 0, 'uint');

if ($app->isClient('site'))
{
	$operator = VikRestaurants::getOperator();
}
else
{
	$operator = false;
}

$vik = VREApplication::getInstance();
?>

<!-- create table inspector -->
<div class="map-table-inspector" style="display:none;">

	<!-- HEAD -->

	<div class="inspector-head">

		<div class="head-toolbar">
			<div class="pull-left">
				<a href="javascript: void(0);" onclick="vrBackToSharedList();" class="back-to-list"><i class="fas fa-arrow-left"></i></a>
			</div>
			<div class="pull-right">
				<a href="javascript: void(0);" onclick="vrMaximizeReservation();" class="reservation-maximize"><i class="fas fa-window-maximize"></i></a>
				<a href="javascript: void(0);" onclick="vrCloseTableCommands();"><i class="fas fa-window-close-o"></i></a>
			</div>
		</div>

		<h3 class="head-tname"></h3>

	</div>

	<!-- BODY -->

	<div class="inspector-body">

		<!-- NO ORDER VIEW -->

		<div class="type-of-view no-order-view" style="display:none;">
			
			<button type="button" class="btn extended" onclick="vrCreateNewReservation();"><?php echo JText::_('VRE_CREATE_NEW_RESERVATION'); ?></button>

			<button type="button" class="btn extended" onclick="vrCloseTable();"><?php echo JText::_('VRE_CLOSE_TABLE'); ?></button>

		</div>

		<!-- SINGLE ORDER VIEW -->

		<div class="type-of-view single-order-view" style="display:none;">
			
			<!-- customer details -->

			<div class="customer-details">

				<div class="customer-info-box user-nominative">
					<i class="fas fa-user"></i>
					<span class="info-value"></span>
				</div>

				<div class="customer-info-box user-email">
					<i class="fas fa-envelope"></i>
					<span class="info-value"></span>
				</div>

				<div class="customer-info-box user-phone">
					<i class="fas fa-phone"></i>
					<span class="info-value"></span>
				</div>

			</div>

			<!-- reservation info -->

			<div class="reservation-details">

				<div class="reservation-info-box res-checkin">
					<i class="far fa-calendar-alt"></i>
					<span class="info-value"></span>
				</div>

				<div class="reservation-info-box res-people">
					<i class="fas fa-male" style="margin:0;"></i>
					<i class="fas fa-male"></i>
					<span class="info-value"></span>
				</div>

			</div>

			<!-- leaves in -->

			<div class="checkout-expiration">

				<i class="fas fa-stopwatch"></i>
				<span class="leaves-in-value"></span>

			</div>

			<!-- reservation code -->

			<div class="reservation-code">

				<div class="code-details"></div>
				<div class="code-notes"></div>

			</div>

			<div class="res-code-list" style="display:none;">

				<?php
				foreach ($codes as $code)
				{
					?>
					<div class="res-code-item" data-id="<?php echo $code->id; ?>">
						<?php
						if ($code->icon)
						{
							?>
							<img src="<?php echo VREMEDIA_SMALL_URI . $code->icon; ?>" />
							<?php
						}
						?>

						<span class="res-code-name"><?php echo $code->code; ?></span>
					</div>
					<?php
				}
				?>

				<div class="res-code-manage-notes">

					<textarea id="order-status-notes" placeholder="<?php echo JText::_('VRE_ORDER_STATUS_NOTES'); ?>"></textarea>

				</div>

			</div>

			<hr />

			<div class="shared-table-actions">
				<button type="button" class="btn extended" onclick="vrCreateNewReservation();"><?php echo JText::_('VRE_CREATE_NEW_RESERVATION'); ?></button>
			</div>

			<div class="change-table-wrapper">
				<button type="button" class="btn extended" onclick="vrChangeTableButtonClicked(this);"><?php echo JText::_('VRMAPCHANGETABLEBUTTON'); ?></button>

				<div class="change-table-tip" style="display:none;"><?php echo JText::_('VRE_CHANGE_TABLE_TIP'); ?></div>
			</div>

			<div class="reservation-notes-wrapper">
				<button type="button" class="btn extended" onclick="vrAddNotesButtonClicked(this);" id="add-notes-btn"><?php echo JText::_('VRE_ADD_NOTES'); ?></button>

				<div class="reservation-notes" style="display:none">
					<textarea id="res-notes-textarea" onkeyup="vrNotesHasChanged(this);"></textarea>
				</div>
			</div>

			<?php
			// display dropdown to assign the operator in case of master
			// account or if the client is the back-end
			if (!$operator || $operator->canSeeAll())
			{
				// get operators list
				$operators = JHtml::_('vikrestaurants.operators', $group = 1, $login = true);
				// include empty option
				array_unshift($operators, JHtml::_('select.option', 0, JText::_('VRE_FILTER_SELECT_OPERATOR')));

				?>
				<div class="reservation-operator-wrapper">
					<div class="vre-select-wrapper">
						<select id="res-operator-select" class="vre-select" onchange="vrSaveAssignee(this);">
							<?php echo JHtml::_('select.options', $operators); ?>
						</select>
					</div>
				</div>
				<?php
			}
			?>

		</div>

		<!-- MULTI ORDER VIEW -->

		<div class="type-of-view multi-order-view" style="display:none;">
			
			<div class="multi-order-list"></div>

			<hr />

			<div class="multi-order-buttons">
				<button type="button" class="btn extended" onclick="vrCreateNewReservation();"><?php echo JText::_('VRE_CREATE_NEW_RESERVATION'); ?></button>
			</div>

		</div>

	</div>

</div>

<?php
if ($app->isClient('administrator'))
{
	// order details modal
	echo JHtml::_(
		'bootstrap.renderModal',
		'jmodal-respinfo',
		array(
			'title'       => JText::_('VRMANAGERESERVATION7'),
			'closeButton' => true,
			'keyboard'    => true, 
			'bodyHeight'  => 80,
			'footer'      => '<a href="#" class="btn btn-success" id="edit-reservation-btn">' . JText::_('VREDIT') . '</a>',
			'url'		  => '', // it will be filled dinamically
		)
	);
}

JText::script('VRRESTIMELEFTMIN');
JText::script('VRE_NO_ORDER_STATUS');
JText::script('VRE_ADD_NOTES');
JText::script('VRE_SAVE_NOTES');
JText::script('VRE_TABLE_OCCUPIED_ERR');
JText::script('JERROR_AN_ERROR_HAS_OCCURRED');
?>

<script>

	<?php
	// back-end instruments
	$json = array();

	foreach ($tables as $table)
	{
		$json[$table->getData('id', 0)] = $table->getData('reservations', array());
	}
	?>
	var TABLE_ORDERS_LOOKUP = <?php echo json_encode($json); ?>;

	var ACTIVITY_FLAG  = false;
	var TABLE_SELECTED = <?php echo $id_table ? $id_table : 'null'; ?>;
	var ORDER_SELECTED = <?php echo $id_order ? $id_order : 'null'; ?>;

	jQuery(document).ready(function() {

		if (typeof Storage !== 'undefined') {
			// restore window scroll to minimize reload effect
			var scroll = parseInt(sessionStorage.getItem('mapScroll'));
			if (!isNaN(scroll)) {
				jQuery(window).scrollTop(scroll);
			}
		}

		jQuery('svg.room-svg g > *').on('click', function() {
			var id = jQuery(this).closest('g').data('id');

			if (!CHANGE_TABLE_FLAG) {
				vrOpenTableCommands(id);	
			} else {
				vrHandleChangeTable(id);
			}
		});

		jQuery('.map-table-inspector .reservation-code .code-details').on('click', function() {
			jQuery('.map-table-inspector .res-code-list').slideToggle();
		});

		jQuery('.map-table-inspector .res-code-list .res-code-item').on('click', function() {
			// close box
			jQuery('.map-table-inspector .reservation-code .code-details').trigger('click');

			if (jQuery(this).hasClass('active')) {
				return;
			}

			var code = {};

			code.id    = jQuery(this).data('id');
			code.code  = jQuery(this).find('.res-code-name').text();
			code.icon  = jQuery(this).find('img').attr('src').split('/').pop();
			code.notes = jQuery('#order-status-notes').val().trim();

			// update reservation code
			vrUpdateReservationCode(TABLE_SELECTED, ORDER_SELECTED, code);
		});

		jQuery(window).on('keydown', function(event) {
			if (event.keyCode == 27) {
				// ESC typed, cancel change table
				vrCancelTableChange();
			}
		});

		vrRegisterActivityListener();

		<?php
		if ($app->isClient('administrator'))
		{
			// render operator dropdown with chosen
			?>VikRenderer.chosen('#res-operator-select', '100%');<?php
		}
		?>

		if (TABLE_SELECTED) {
			// re-open table commands
			vrOpenTableCommands(TABLE_SELECTED, ORDER_SELECTED);
		}

	});

	function vrGetOrderIndex(id_table, id_order) {
		if (id_table === undefined || id_table === null) {
			id_table = TABLE_SELECTED;
		}

		if (id_order === undefined || id_order === null) {
			id_order = ORDER_SELECTED;
		}

		// make sure we have a valid table
		if (!TABLE_ORDERS_LOOKUP.hasOwnProperty(id_table) || !TABLE_ORDERS_LOOKUP[id_table].length) {
			return false;
		}

		var index = false;

		if (!id_order) {
			// find the first one
			id_order = TABLE_ORDERS_LOOKUP[id_table][0].id;
			index    = 0;
		} else {
			// find order ID
			for (var i = 0; i < TABLE_ORDERS_LOOKUP[id_table].length && index === false; i++) {
				if (TABLE_ORDERS_LOOKUP[id_table][i].id == id_order) {
					index = i;
				}
			}
		}

		return index;
	}

	function vrCloseTableCommands() {
		TABLE_SELECTED = ORDER_SELECTED = null;
		jQuery('.map-table-inspector').hide();

		// cancel table change
		vrCancelTableChange();
	}

	function vrOpenTableCommands(id, order_id) {
		var orders = [];

		// cancel table change
		vrCancelTableChange();

		if (TABLE_ORDERS_LOOKUP.hasOwnProperty(id)) {
			orders = TABLE_ORDERS_LOOKUP[id];
		}

		TABLE_SELECTED = id;
		ORDER_SELECTED = order_id;

		// get table graphic
		var g = jQuery('g[data-id="' + id + '"]');

		// find table name
		var tname = g.find('.table-name-text').text();

		// setup head
		jQuery('.map-table-inspector .inspector-head .head-tname').text(tname);

		// hide all body elements
		jQuery('.type-of-view').hide();

		// hide maximize
		jQuery('.map-table-inspector .inspector-head .reservation-maximize').hide();
		// hide back button
		jQuery('.map-table-inspector .inspector-head .back-to-list').hide();

		if (orders.length == 0) {

			// display buttons used to book a new reservation
		 	jQuery('.type-of-view.no-order-view').show();

		} else if (orders.length == 1 || order_id) {
			var order = orders[0];

			if (orders.length > 1) {
				var index = vrGetOrderIndex(id, order_id);

				if (index === false) {
					// order not found, close commands
					vrCloseTableCommands();
					return;
				}

				order = orders[index];
			}

			ORDER_SELECTED = order.id;

			// get customer details box
			var custBox = jQuery('.type-of-view.single-order-view .customer-details');

			// fill custom fields
			if (order.purchaser_nominative.length) {
				custBox.find('.user-nominative').show().find('.info-value').text(order.purchaser_nominative);
			} else {
				custBox.find('.user-nominative').hide().find('.info-value').text('');
			}

			if (order.purchaser_mail.length) {
				custBox.find('.user-email').show().find('.info-value').html('<a href="mailto:' + order.purchaser_mail + '">' + order.purchaser_mail + '</a>');
			} else {
				custBox.find('.user-email').hide().find('.info-value').html('');
			}

			if (order.purchaser_phone.length) {
				custBox.find('.user-phone').show().find('.info-value').html('<a href="tel:' + order.purchaser_prefix + order.purchaser_phone + '">' + order.purchaser_phone + '</a>');
			} else {
				custBox.find('.user-phone').hide().find('.info-value').html('');
			}

			// get reservation details box
			var resBox = jQuery('.type-of-view.single-order-view .reservation-details');

			// fill reservation details
			if (order.checkin_time.length) {
				resBox.find('.res-checkin').show().find('.info-value').text(order.checkin_time);
			} else {
				resBox.find('.res-checkin').hide().find('.info-value').text('');
			}

			if (order.people.length) {
				resBox.find('.res-people').show().find('.info-value').text('x' + order.people);
			} else {
				resBox.find('.res-people').hide().find('.info-value').text('');
			}

			// handle expiration
			vrUpdateExpiration(id, order.id);

			// get reservation code box
			var codeBox = jQuery('.type-of-view.single-order-view .reservation-code');

			// remove active class from any codes
			jQuery('.map-table-inspector .res-code-list .res-code-item').removeClass('active');

			// setup reservation code
			if (parseInt(order.rescode) > 0) {
				// build image
				var image = '';
				if (order.code_icon.length) {
					image = '<img src="<?php echo VREMEDIA_SMALL_URI; ?>' + order.code_icon + '" />';
				}
				
				// show and setup html
				codeBox.find('.code-details').html(image + '<span>' + order.code + '</span>');

				// mark selected code
				jQuery('.map-table-inspector .res-code-list .res-code-item[data-id="' + order.rescode + '"]').addClass('active');
			} else {
				codeBox.find('.code-details').html(Joomla.JText._('VRE_NO_ORDER_STATUS'));
			}

			// fetch code notes
			if (order.code_notes) {
				codeBox.find('.code-notes').text(order.code_notes).show();
			} else {
				codeBox.find('.code-notes').text('').hide();
			}

			// always unset the current notes
			jQuery('textarea#order-status-notes').val('');

			// setup reservation notes
			vrSetupReservationNotes(order.notes);

			// setup operator assignment
			vrSetupAssignee(order.id_operator);

			// handle buttons for shared tables
			var shared = parseInt(g.data('shared'));

			if (shared && orders.length == 1) {
				// display shared tables actions box (only in case of a single order)
				jQuery('.shared-table-actions').show();
			} else {
				// hide shared tables actions box
				jQuery('.shared-table-actions').hide();
			}

			if (shared && orders.length > 1) {
				// display back button
				jQuery('.map-table-inspector .inspector-head .back-to-list').show();
			}

			// show view
			jQuery('.type-of-view.single-order-view').show();
			// show maximize
			jQuery('.map-table-inspector .inspector-head .reservation-maximize').show();
		} else {
			// display multi-orders list

			var html = '';

			for (var i = 0; i < orders.length; i++) {
				html += '<div class="multi-order-item" data-order="' + orders[i].id + '">\n';

				html += '<div class="multi-order-item-left">\n';

				html += '<div class="multi-order-item-left-primary">\n';
				html += orders[i].purchaser_nominative ? orders[i].purchaser_nominative : orders[i].purchaser_mail;
				html += '</div>\n';

				html += '<div class="multi-order-item-left-secondary">\n';
				html += '<i class="fas fa-stopwatch"></i>';
				html += '<span>' + orders[i].checkin_time + '</span>\n';
				html += '<i class="fas fa-male"></i>';
				html += '<span>' + orders[i].people + '</span>\n';
				html += '</div>\n';

				html += '</div>\n';

				if (parseInt(orders[i].rescode)) {
					html += '<div class="multi-order-item-right">\n';
					if (orders[i].code_icon) {
						var image = '<?php echo VREMEDIA_SMALL_URI; ?>' + orders[i].code_icon;
						html += '<img src="' + image + '" title="' + orders[i].code + '" />\n';
					} else {
						html += '<span>' + orders[i].code + '</span>\n';
					}
					html += '</div>\n';
				}

				html += '</div>\n';
			}

			jQuery('.type-of-view.multi-order-view .multi-order-list').html(html)
				.find('.multi-order-item').on('click', function() {
					var id_order = parseInt(jQuery(this).data('order'));
					vrOpenTableCommands(TABLE_SELECTED, id_order);
				});

			jQuery('.type-of-view.multi-order-view').show();
		}

		jQuery('.map-table-inspector').show();
	}

	function vrBackToSharedList() {
		vrOpenTableCommands(TABLE_SELECTED);
	}

	function vrMaximizeReservation() {
		var orders = [];

		if (TABLE_ORDERS_LOOKUP.hasOwnProperty(TABLE_SELECTED)) {
			orders = TABLE_ORDERS_LOOKUP[TABLE_SELECTED];
		}

		if (!orders.length) {
			// nothing to maximize
			return;
		}

		<?php
		if ($app->isClient('administrator'))
		{
			// open modal in case of back-end
			?>
			vrOpenJModal('respinfo', ORDER_SELECTED);
			<?php
		}
		else
		{
			// define URL for front-end
			?>
			var url = '<?php echo JRoute::_("index.php?option=com_vikrestaurants&task=opreservation.edit&from=oversight" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';

			url += '&cid[]=' + ORDER_SELECTED;

			document.location.href = url;
			<?php
		}
		?>
	}

	function vrCreateNewReservation() {
		<?php
		if ($app->isClient('administrator'))
		{
			// define URL for back-end
			?>
			var url = 'index.php?option=com_vikrestaurants&task=reservation.add&from=maps';
			<?php
		}
		else
		{
			// define URL for front-end
			?>
			var url = '<?php echo JRoute::_("index.php?option=com_vikrestaurants&task=opreservation.add&from=oversight" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>

		url = vrAppendFiltersToURI(url);

		url += '&idt=' + TABLE_SELECTED;

		document.location.href = url;
	}

	function vrCloseTable() {
		<?php
		if ($app->isClient('administrator'))
		{
			// define URL for back-end
			?>
			var url = 'index.php?option=com_vikrestaurants&task=reservation.saveclosure&from=maps';
			<?php
		}
		else
		{
			// define URL for front-end
			?>
			var url = '<?php echo JRoute::_("index.php?option=com_vikrestaurants&task=oversight.saveclosure&from=oversight" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>

		url = vrAppendFiltersToURI(url);

		url += '&idt=' + TABLE_SELECTED;

		document.location.href = url;
	}

	function vrAppendFiltersToURI(url) {
		if (url.indexOf('?') === -1) {
			url = url + '?1';
		}

		<?php
		if (isset($options->filters) && isset($options->filters->date))
		{
			// use date set in filters
			?>
			url += '&date=<?php echo $options->filters->date; ?>';
			<?php
		}
		else
		{
			// recover date filter from input, if any
			?>
			url += '&date=' + jQuery('*[name="datefilter"]').val();
			<?php
		}
		?>

		<?php
		if (isset($options->filters) && isset($options->filters->hourmin))
		{
			// use hours and minutes set in filters
			?>
			url += '&hourmin=<?php echo $options->filters->hourmin; ?>';
			<?php
		}
		else
		{
			// recover hours and minutes from input, if any
			?>
			url += '&hourmin=' + jQuery('*[name="hourmin"]').val();
			<?php
		}
		?>

		<?php
		if (isset($options->filters) && isset($options->filters->people))
		{
			// use people set in filters
			?>
			url += '&people=<?php echo $options->filters->people; ?>';
			<?php
		}
		else
		{
			// recover people from input, if any
			?>
			url += '&people=' + jQuery('*[name="people"]').val();
			<?php
		}
		?>

		return url;
	}

	function vrUpdateExpiration(table, order, timePast) {
		// get index of order
		var index = vrGetOrderIndex(table, order);

		if (index === false) {
			// order not found
			return;
		}

		if (timePast === undefined) {
			timePast = 0;
		}

		TABLE_ORDERS_LOOKUP[table][index].time_left -= timePast;

		var minutesLeft = Math.ceil(TABLE_ORDERS_LOOKUP[table][index].time_left / 60);

		var expBox = jQuery('.checkout-expiration');

		if (minutesLeft > 0) {
			var expText = Joomla.JText._('VRRESTIMELEFTMIN');
			TABLE_ORDERS_LOOKUP[table][index].timeLeftLabel = expText.replace(/%d/, minutesLeft);
		} else {
			TABLE_ORDERS_LOOKUP[table][index].timeLeftLabel = '';
		}

		// update html, if needed

		if (TABLE_SELECTED == table && ORDER_SELECTED == order) {
			var label = TABLE_ORDERS_LOOKUP[table][index].timeLeftLabel;
			if (label.length > 0) {
				expBox.show().find('.leaves-in-value').text(label);
			} else {
				expBox.hide().find('.leaves-in-value').text('');
			}
		}
	}

	function vrUpdateReservationCode(id_table, id_order, code) {
		// get index of order
		var index = vrGetOrderIndex(id_table, id_order);

		if (index === false) {
			return;
		}

		<?php
		if ($app->isClient('administrator'))
		{
			?>
			// build AJAX end-point for back-end
			var url = 'index.php?option=com_vikrestaurants&task=reservation.changecodeajax&tmpl=component';
			<?php
		}
		else
		{
			?>
			// build AJAX end-point for front-end
			var url = '<?php echo $vik->ajaxUrl("index.php?option=com_vikrestaurants&task=oversight.changecodeajax&tmpl=component" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>

		var data = {
			id: 	 id_order,
			id_code: code.id,
			notes: 	 code.notes,
		};

		// perform AJAX call
		UIAjax.do(url, data, undefined, function(resp) {
				// display error
				alert(Joomla.JText._('JERROR_AN_ERROR_HAS_OCCURRED'));
			}
		);

		// update table details
		TABLE_ORDERS_LOOKUP[id_table][index].rescode    = code.id;
		TABLE_ORDERS_LOOKUP[id_table][index].code       = code.code;
		TABLE_ORDERS_LOOKUP[id_table][index].code_icon  = code.icon;
		TABLE_ORDERS_LOOKUP[id_table][index].code_notes = code.notes;

		// find graphic element
		var image = jQuery('g#table-' + id_table).find('image.table-rescode-badge');

		// if we have a badge, update it
		if (image.length) {
			var href = '';
			if (code.icon) {
				href = '<?php echo VREMEDIA_SMALL_URI; ?>' + code.icon;
			}

			// update href ad title
			image.attr('xlink:href', href)
				.find('title').text(code.code);
		}

		// re-build inspector
		vrOpenTableCommands(TABLE_SELECTED, ORDER_SELECTED);
	}

	function vrSetupReservationNotes(notes) {
		if (typeof notes !== 'string') {
			notes = '';
		}

		var btn = jQuery('button#add-notes-btn')
			.text(Joomla.JText._('VRE_ADD_NOTES'))
			.attr('onclick', 'vrAddNotesButtonClicked(this);');

		var textareaBox = jQuery('.map-table-inspector .reservation-notes');

		textareaBox.find('#res-notes-textarea').val(notes);

		if (notes.length > 0 && !textareaBox.hasClass('visible')) {
			textareaBox.addClass('visible');
			textareaBox.show();
			jQuery(btn).addClass('no-bottom');
		} else if (notes.length == 0 && textareaBox.hasClass('visible')) {
			textareaBox.removeClass('visible');
			textareaBox.hide();
			jQuery(btn).removeClass('no-bottom');
		}
	}

	function vrAddNotesButtonClicked(btn) {

		var textareaBox = jQuery('.map-table-inspector .reservation-notes');

		if (textareaBox.hasClass('visible')) {
			textareaBox.removeClass('visible');
			textareaBox.slideUp('fast', function() {
				jQuery(btn).removeClass('no-bottom');
			});
		} else {
			textareaBox.addClass('visible');
			textareaBox.slideDown('fast', function() {
				textareaBox.find('textarea').focus();
			});
			jQuery(btn).addClass('no-bottom');
		}

	}

	function vrSaveReservationNotes(btn) {
		// get index of order
		var index = vrGetOrderIndex();

		if (index === false) {
			return;
		}

		<?php
		if ($app->isClient('administrator'))
		{
			?>
			// build AJAX end-point for back-end
			var url = 'index.php?option=com_vikrestaurants&task=reservation.savenotesajax&tmpl=component';
			<?php
		}
		else
		{
			?>
			// build AJAX end-point for front-end
			var url = '<?php echo $vik->ajaxUrl("index.php?option=com_vikrestaurants&task=oversight.savenotesajax&tmpl=component" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>

		var data = {
			id: 	ORDER_SELECTED,
			notes: 	jQuery('#res-notes-textarea').val(),
			type: 	1,
		};

		// perform AJAX call
		UIAjax.do(url, data, undefined, function(resp) {
			// display error
			alert(Joomla.JText._('JERROR_AN_ERROR_HAS_OCCURRED'));
		});

		// update table details
		TABLE_ORDERS_LOOKUP[TABLE_SELECTED][index].notes = data.notes;

		// setup notes
		vrSetupReservationNotes(data.notes);
	}

	function vrNotesHasChanged(textarea) {
		// make sure we have a valid table
		if (!TABLE_ORDERS_LOOKUP.hasOwnProperty(TABLE_SELECTED)) {
			return;
		}

		// find order ID
		var index = false;

		for (var i = 0; i < TABLE_ORDERS_LOOKUP[TABLE_SELECTED].length && index === false; i++) {
			if (TABLE_ORDERS_LOOKUP[TABLE_SELECTED][i].id == ORDER_SELECTED) {
				index = i;
			}
		}

		if (index === false) {
			// order not found
			return;
		}

		var order = TABLE_ORDERS_LOOKUP[TABLE_SELECTED][index];

		if (order.notes != textarea.value) {
			jQuery('button#add-notes-btn').text(Joomla.JText._('VRE_SAVE_NOTES'))
				.attr('onclick', 'vrSaveReservationNotes(this);');
		} else {
			jQuery('button#add-notes-btn').text(Joomla.JText._('VRE_ADD_NOTES'))
				.attr('onclick', 'vrAddNotesButtonClicked(this);');
		}
	}

	function vrSetupAssignee(id_operator) {
		<?php
		if ($app->isClient('administrator'))
		{
			?>jQuery('#res-operator-select').updateChosen(id_operator);<?php
		}
		else
		{
			?>jQuery('#res-operator-select').val(id_operator);<?php
		}
		?>
	}

	function vrSaveAssignee(select) {
		// get index of order
		var index = vrGetOrderIndex();

		if (index === false) {
			return;
		}

		<?php
		if ($app->isClient('administrator'))
		{
			?>
			// build AJAX end-point for back-end
			var url = 'index.php?option=com_vikrestaurants&task=reservation.assignoperatorajax&tmpl=component';
			<?php
		}
		else
		{
			?>
			// build AJAX end-point for front-end
			var url = '<?php echo $vik->ajaxUrl("index.php?option=com_vikrestaurants&task=oversight.assignoperatorajax&tmpl=component" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>

		var data = {
			id: 	     ORDER_SELECTED,
			id_operator: parseInt(jQuery(select).val()),
		};

		// perform AJAX call
		UIAjax.do(url, data, undefined, function(err) {
			// display error
			alert(Joomla.JText._('JERROR_AN_ERROR_HAS_OCCURRED'));
			// restore previous operator
			data.id_operator = TABLE_ORDERS_LOOKUP[TABLE_SELECTED][index].id_operator;

			vrSetupAssignee(data.id_operator);
		});

		// update table details
		TABLE_ORDERS_LOOKUP[TABLE_SELECTED][index].notes = data.id_operator;
	}

	var CHANGE_TABLE_FLAG = false;

	function vrChangeTableButtonClicked(btn) {
		CHANGE_TABLE_FLAG = true;

		jQuery(btn).hide();
		jQuery('.map-table-inspector .change-table-tip').show();
	}

	function vrCancelTableChange() {
		CHANGE_TABLE_FLAG = false;

		jQuery('.map-table-inspector button').show();
		jQuery('.map-table-inspector .change-table-tip').hide();
	}

	function vrHandleChangeTable(id_table) {
		if (!TABLE_SELECTED) {
			return;
		}

		if (id_table == TABLE_SELECTED) {
			// the table hasn't changed, cancel action
			vrCancelTableChange();
			return;
		}

		// check if the table exists
		if (!TABLE_ORDERS_LOOKUP.hasOwnProperty(id_table)) {
			// table not found
			return;
		}

		var table = TABLE_ORDERS_LOOKUP[id_table];

		// check if multi res
		var shared = jQuery('g[data-id="' + id_table + '"]').data('shared') ? true : false;

		// if we have a non-shared table with a reservation, raise an error
		if (!shared && table.length > 0) {
			alert(Joomla.JText._('VRE_TABLE_OCCUPIED_ERR'));
			return;
		}

		<?php
		if ($app->isClient('administrator'))
		{
			// define URL for back-end
			?>
			var url = 'index.php?option=com_vikrestaurants&task=reservation.changetable&from=maps';
			<?php
		}
		else
		{
			// define URL for front-end
			?>
			var url = '<?php echo JRoute::_("index.php?option=com_vikrestaurants&task=oversight.changetable" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>

		url = vrAppendFiltersToURI(url);

		// make query string compatible with the task
		url += '&oldid=' + TABLE_SELECTED;
		url += '&newid=' + id_table;
		url += '&id_order=' + ORDER_SELECTED;

		document.location.href = url;
	}

	function vrRegisterActivityListener() {
		ACTIVITY_FLAG = false;

		jQuery(window).on('keydown', vrMarkActivity);
		jQuery(window).on('mousemove', vrMarkActivity);
		jQuery(window).on('mousedown', vrMarkActivity);

		setTimeout(function() {

			if (ACTIVITY_FLAG) {
				vrRegisterActivityListener();
			} else {
				vrReloadMap();
			}

		}, 30000);
	}

	function vrMarkActivity() {
		ACTIVITY_FLAG = true;

		// turn off any attached events to avoid spending
		// useless resources
		jQuery(window).off('keydown', vrMarkActivity);
		jQuery(window).off('mousemove', vrMarkActivity);
		jQuery(window).off('mousedown', vrMarkActivity);
	}

	function vrReloadMap() {
		<?php
		if ($app->isClient('administrator'))
		{
			// define URL for back-end
			?>
			var url = 'index.php?option=com_vikrestaurants&view=maps';
			<?php
		}
		else
		{
			// define URL for front-end
			?>
			var url = '<?php echo JRoute::_("index.php?option=com_vikrestaurants&view=oversight" . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>';
			<?php
		}
		?>

		url = vrAppendFiltersToURI(url);

		if (TABLE_SELECTED) {
			url += '&id_table=' + TABLE_SELECTED;
		}

		if (ORDER_SELECTED) {
			url += '&id_order=' + ORDER_SELECTED;
		}

		if (typeof Storage !== 'undefined') {
			// register window scroll to minimize reload effect
			sessionStorage.setItem('mapScroll', jQuery(window).scrollTop());
		}

		document.location.href = url;
	}

	// initialize the process that will update the 
	// orders expiration every 15 seconds
	var EXPIRATION_TIMER = setInterval(function() {
		// iterate all the tables
		for (var id_table in TABLE_ORDERS_LOOKUP) {
			if (!TABLE_ORDERS_LOOKUP.hasOwnProperty(id_table)) {
				continue;
			}

			// iterate all the orders of the table
			for (var i = 0; i < TABLE_ORDERS_LOOKUP[id_table].length; i++) {
				// decrease time left by 15 seconds (the interval execution time)
				vrUpdateExpiration(id_table, TABLE_ORDERS_LOOKUP[id_table][i].id, 15);
			}
		}
	}, 15000);

	<?php
	if ($app->isClient('administrator'))
	{
		?>
		function vrOpenJModal(id, cid) {
			switch (id) {
				case 'respinfo':
					url = 'index.php?option=com_vikrestaurants&view=orderinfo&tmpl=component&id=' + cid;
					jQuery('#edit-reservation-btn').attr('href', 'index.php?option=com_vikrestaurants&from=restaurant&from=maps&task=reservation.edit&cid[]=' + cid);
					break;
			}

			var jqmodal = true;

			<?php echo $vik->bootOpenModalJS(); ?>
		}
		<?php
	}
	?>

</script>
