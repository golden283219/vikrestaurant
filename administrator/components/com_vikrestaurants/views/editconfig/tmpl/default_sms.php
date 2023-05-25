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

JHtml::_('vrehtml.assets.intltel', 'input[name="smsapiadminphone"]');

$params = $this->params;

$vik = VREApplication::getInstance();

$languages = VikRestaurants::getKnownLanguages();

/**
 * Trigger event to display custom HTML.
 * In case it is needed to include any additional fields,
 * it is possible to create a plugin and attach it to an event
 * called "onDisplayViewConfigSms". The event method receives the
 * view instance as argument.
 *
 * @since 1.8.3
 */
$smsLeftForms  = $this->onDisplayView('Sms');
$smsRightForms = $this->onDisplayView('SmsSecondary');

?>

<style>
	#usercreditsp {
		display: inline-block;
		vertical-align: middle;
		width: 100px;
	}
	.vr-uc-text-green {
		color: #080;
		font-weight: bold;
	}
	.vr-uc-text-red {
		color: #900;
		font-weight: bold;
	}	
</style>

<!-- LEFT SIDE -->

<div class="config-left-side">

	<!-- SMS APIs Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGTITLE3'); ?></div>
		<table class="admintable" cellspacing="1">
			
			<!-- SMS API CLASS - Dropdown -->
			<?php
			$elements = JHtml::_('vrehtml.admin.smsdrivers');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGSMS1"); ?></b> </td>
				<td>
					<select name="smsapi" class="required" id="vr-smsdriver-sel">
						<option></option>
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['smsapi']); ?>
					</select>		
				</td>
			</tr>
			
			<!-- SMS API WHEN - Dropdown -->
			<?php
			$elements = array();
			
			if ($params['enablerestaurant'])
			{ 
				$elements[] = JHtml::_('select.option', 0, 'VRCONFIGSMSAPIWHEN0');
			}

			if ($params['enabletakeaway'])
			{
				$elements[] = JHtml::_('select.option', 1, 'VRCONFIGSMSAPIWHEN1');
			}

			if ($params['enablerestaurant'] && $params['enabletakeaway'])
			{
				$elements[] = JHtml::_('select.option', 2, 'VRCONFIGSMSAPIWHEN2');
			}

			$elements[] = JHtml::_('select.option', 3, 'VRCONFIGSMSAPIWHEN3');
			
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGSMS2"); ?></b> </td>
				<td>
					<select name="smsapiwhen" class="medium-large">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['smsapiwhen'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- SMS API TO - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRCONFIGSMSAPITO0'),
				JHtml::_('select.option', 1, 'VRCONFIGSMSAPITO1'),
				JHtml::_('select.option', 2, 'VRCONFIGSMSAPITO2'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGSMS3"); ?></b> </td>
				<td>
					<select name="smsapito" class="medium-large">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['smsapito'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- SMS API ADMIN PHONE - Text -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGSMS4"); ?></b> </td>
				<td>
					<input type="text" name="smsapiadminphone" value="<?php echo $params['smsapiadminphone']; ?>" />
				</td>
			</tr>
			
			<!-- SMS API ESTIMATE - Form -->
			<?php
			$can_estimate = false;
			
			try
			{
				$smsdriver = $vik->getSmsInstance($params['smsapi']);

				if (method_exists($smsdriver, 'estimate'))
				{ 
					$can_estimate = true;
					?>
					<tr>
						<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGSMS7"); ?></b> </td>
						<td>
							<span id="usercreditsp">/</span>
							<button type="button" class="btn" onClick="estimateSmsApiUserCredit();"><?php echo JText::_("VRMANAGECONFIGSMS8"); ?></button>
						</td>
					</tr>
					<?php
				}
			}
			catch (Exception $e)
			{
				// no SMS driver
			}
			?>
		
		</table>
	</div>

	<!-- SMS APIs Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGSMS5'); ?></div>
		<table class="admintable" cellspacing="1" id="vr-smsapi-params-table">

			<?php
			if (empty($params['smsapi']))
			{
				?>
				<tr>
					<td colspan="2">
						<?php echo $vik->alert(JText::_('VRMANAGEPAYMENT9')); ?>
					</td>
				</tr>
				<?php
			}
			?>

		</table>
	</div>

	<?php
	/**
	 * Iterate remaining forms to be displayed within
	 * the "sms-left" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($smsLeftForms as $formName => $formHtml)
	{
		?>
		<div class="config-fieldset">
			<div class="config-fieldset-legend"><?php echo JText::_($formName); ?></div>
			
			<?php echo $formHtml; ?>
		</div>
		<?php
	}
	?>

</div>

<!-- RIGHT SIDE -->

<div class="config-right-side">

	<!-- CUSTOMER TEMPLATE Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend">
			<span><?php echo JText::_('VRCONFIGSMSTITLE2'); ?></span>

			<div class="btn-group pull-right">
				<span class="badge badge-info" id="sms-site-section-1"><?php echo JText::_('VRMENUTITLEHEADER1'); ?></span>
				<span class="badge badge-warning" id="sms-site-langtag" style="margin-left:4px;"><?php echo $languages[0]; ?></span>
			</div>
		</div>

		<div class="admintable" cellspacing="1">

			<div style="margin-left: 10px;">

				<div class="btn-toolbar vr-btn-toolbar">
					<div class="btn-group pull-left">
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(1, '{total_cost}');">{total_cost}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(1, '{checkin}');">{checkin}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(1, '{people}');">{people}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(1, '{company}');">{company}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(1, '{created_on}');">{created_on}</button>
					</div>
				</div>

				<div class="control">
					<?php 
					$sms_tmpl_cust = array(
						(array) json_decode($params['smstmplcust'], true),
						(array) json_decode($params['smstmpltkcust'], true),
					);

					foreach ($languages as $k => $lang)
					{ 
						$lang_name = explode('-', $lang);
						$lang_name = strtolower(isset($lang_name[1]) ? $lang_name[1] : $lang_name[0]);

						for ($i = 0; $i < 2; $i++)
						{
							$content = "";
							
							if (!empty($sms_tmpl_cust[$i][$lang]))
							{
								$content = $sms_tmpl_cust[$i][$lang];
							}
							?>
							<textarea
								name="smstmplcust[<?php echo $i; ?>][]"
								class="vr-smscont-1"
								id="vrsmscont<?php echo $lang_name; ?>-<?php echo ($i+1); ?>" 
								style="width: calc(100% - 24px);height: 200px;resize: vertical;
								<?php echo ($k != 0 || $i == 1 ? 'display:none;' : ''); ?>"
							><?php echo $content; ?></textarea>
							<?php
						}
					}
					?>
				</div>

				<!-- LANGUAGES -->
				<div class="btn-toolbar vr-btn-toolbar" style="width: calc(100% - 12px);">
					<div class="btn-group pull-left">
						<button type="button" class="btn" id="vr-switch-button-1" onClick="switchSmsContent(1);"><?php echo JText::_('VRMANAGECONFIGSMS10'); ?></button>
					</div>

					<div class="btn-group pull-right">
						<?php
						foreach ($languages as $k => $lang)
						{ 
							$lang_name = explode('-', $lang);
							$lang_name = strtolower(isset($lang_name[1]) ? $lang_name[1] : $lang_name[0]);
							?>
							<button type="button" class="vr-sms-langtag btn <?php echo ($k == 0 ? 'active' : ''); ?>" id="vrsmstag<?php echo $lang_name; ?>" onClick="changeLanguageSMS('<?php echo $lang_name; ?>', '<?php echo $lang; ?>');">
								<i class="icon">
									<img src="<?php echo VREASSETS_URI . 'css/flags/' . $lang_name . '.png';?>" />
								</i>
								&nbsp;<?php echo strtoupper($lang_name); ?>
							</button>
							<?php
						}
						?>
					</div>
				</div>

			</div>

		</div>
	</div>

	<!-- ADMIN TEMPLATE Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend">
			<span><?php echo JText::_('VRCONFIGSMSTITLE3'); ?></span>
			
			<div class="btn-group pull-right">
				<span class="badge badge-info" id="sms-site-section-2"><?php echo JText::_('VRMENUTITLEHEADER1'); ?></span>
			</div>
		</div>
		<div class="admintable" cellspacing="1">

			<div style="margin-left: 10px;">

				<div class="btn-toolbar vr-btn-toolbar">
					<div class="btn-group pull-left">
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(2, '{total_cost}');">{total_cost}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(2, '{checkin}');">{checkin}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(2, '{people}');">{people}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(2, '{company}');">{company}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(2, '{customer}');">{customer}</button>
						<button type="button" class="btn" onClick="putSmsTagOnActiveContent(2, '{created_on}');">{created_on}</button>
					</div>
				</div>

				<div class="control">
					<?php 
					$sms_tmpl_admin = array(
						$params['smstmpladmin'],
						$params['smstmpltkadmin'],
					);

					for ($i = 0; $i < 2; $i++)
					{
						?>
						<textarea
							name="smstmpladmin[]"
							class="vr-smscont-2"
							id="vrsmscontadmin-<?php echo ($i+1); ?>"
							style="width: calc(100% - 24px);height: 200px;resize: vertical;
							<?php echo ($i != 0 ? 'display:none;' : ''); ?>"
						><?php echo $sms_tmpl_admin[$i]; ?></textarea>
						<?php
					}
					?>
				</div>
				
				<div class="btn-toolbar vr-btn-toolbar" style="width: calc(100% - 12px);">
					<div class="btn-group pull-left">
						<button type="button" class="btn" id="vr-switch-button-2" onClick="switchSmsContent(2);"><?php echo JText::_('VRMANAGECONFIGSMS10'); ?></button>
					</div>
				</div>

			</div>

		</div>
	</div>

	<?php
	/**
	 * Iterate remaining forms to be displayed within
	 * the "sms-right" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($smsRightForms as $formName => $formHtml)
	{
		?>
		<div class="config-fieldset">
			<div class="config-fieldset-legend"><?php echo JText::_($formName); ?></div>
			
			<?php echo $formHtml; ?>
		</div>
		<?php
	}
	?>

</div>

<div id="smsapi-no-params" style="display:none;">
	<?php echo $vik->alert(JText::_('VRMANAGEPAYMENT9')); ?>
</div>

<div id="smsapi-connection-err" style="display:none;">
	<?php echo $vik->alert(JText::_('VRE_AJAX_GENERIC_ERROR'), 'error'); ?>
</div>

<?php
JText::script('VRE_FILTER_SELECT_DRIVER');
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRMANAGECONFIGSMS9');
JText::script('VRMANAGECONFIGSMS10');
JText::script('VRMENUTITLEHEADER1');
JText::script('VRMENUTITLEHEADER5');
?>

<script type="text/javascript">

	jQuery(document).ready(function(){

		jQuery('#vr-smsdriver-sel').select2({
			placeholder: Joomla.JText._('VRE_FILTER_SELECT_DRIVER'),
			allowClear: true,
			width: 250,
		});

		jQuery('#vr-smsdriver-sel').on('change', refreshSmsApiParameters);

		<?php
		if (!empty($params['smsapi']))
		{
			?>
			refreshSmsApiParameters();
			<?php
		}
		?>

		<?php
		if (!$params['enablerestaurant'] && $params['enabletakeaway'])
		{
			// restaurant disabled, auto-toggle the SMS templates to immediately display the take-away
			?>
			switchSmsContent(1);
			switchSmsContent(2);
			<?php
		}
		?>

	});

	// refresh SMS API params
	
	function refreshSmsApiParameters() {
		var driver = jQuery('#vr-smsdriver-sel').val();

		// destroy select2 
		jQuery('#vr-smsapi-params-table select').select2('destroy');
		// unregister form fields
		// validator.unregisterFields('#vr-smsapi-params-table .required');
		
		jQuery('#vr-smsapi-params-table').html('');

		if (!driver) {
			// no driver selected, display message
			var alert = jQuery('#smsapi-no-params').html();
			jQuery('#vr-smsapi-params-table').html('<tr><td colspan="2">' + alert + '</td></tr>');
			return false;
		}

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=configuration.smsapifields&tmpl=component',
			{
				driver: driver,
			},
			function(resp) {
				var obj = jQuery.parseJSON(resp);

				if (!obj) {
					jQuery('#vikparamerr').show();
					return false;
				}

				jQuery('#vr-smsapi-params-table').html(obj[0]);

				// render select
				jQuery('#vr-smsapi-params-table select').each(function() {
					jQuery(this).select2({
						// disable search for select with 3 or lower options
						minimumResultsForSearch: jQuery(this).find('option').length > 3 ? 0 : -1,
						allowClear: false,
						width: 285,
					});
				});

				// register form fields for validation
				// validator.registerFields('#vr-smsapi-params-table .required');

				// init helpers
				jQuery('#vr-smsapi-params-table .vr-quest-popover').popover({sanitize: false, container: 'body'});

				jQuery('#vr-smsapi-params-table').trigger('smsapi.load');
			},
			function(error) {
				// display connection error message
				var alert = jQuery('#smsapi-connection-err').html();
				jQuery('#vr-smsapi-params-table').html('<tr><td colspan="2">' + alert + '</td></tr>');
			}
		);
	}
	
	<?php if ($can_estimate) { ?>

		// estimate the remaining credit

		function estimateSmsApiUserCredit() {
			jQuery('#usercreditsp')
				.removeClass('vr-uc-text-green')
				.removeClass('vr-uc-text-red')
				.html('<i class="fas fa-sync-alt fa-spin"></i>');

			UIAjax.do(
				'index.php?option=com_vikrestaurants&task=configuration.smsapicredit&tmpl=component',
				{
					driver: '<?php echo $params['smsapi']; ?>',
					phone:  jQuery('input[name="smsapiadminphone"]').val(),
				},
				function(credit) {
					credit = parseFloat(credit);

					if (isNaN(credit)) {
						credit = 0;
					}

					if (credit > 0) {
						jQuery('#usercreditsp').addClass('vr-uc-text-green');
						jQuery('#usercreditsp').removeClass('vr-uc-text-red');
					} else {
						jQuery('#usercreditsp').addClass('vr-uc-text-red');
						jQuery('#usercreditsp').removeClass('vr-uc-text-green');
					}

					jQuery('#usercreditsp').html(Currency.getInstance().format(credit));
				},
				function(error) {
					if (!error.responseText) {
						// use default connection lost error
						error.responseText = Joomla.JText._('VRSYSTEMCONNECTIONERR');
					}

					jQuery('#usercreditsp').html('/');

					// raise error
					alert(error.responseText);
				}
			);
		}

	<?php } ?>

	// insert selected placeholder on active textarea
	
	function putSmsTagOnActiveContent(id, cont) {
		
		var area = null;
		jQuery('.vr-smscont-' + id).each(function() {
			if (jQuery(this).css('display') != 'none') {
				area = jQuery(this);
			}
		});
		
		if (area == null) {
			return;
		}
		
		var start = area.get(0).selectionStart;
		var end = area.get(0).selectionEnd;
		area.val(area.val().substring(0, start) + cont + area.val().substring(end));
		area.get(0).selectionStart = area.get(0).selectionEnd = start + cont.length;
		area.focus();
	}

	// switch language
	
	function changeLanguageSMS(regional, langtag) {
		jQuery('.vr-sms-langtag').removeClass('active');
		jQuery('#vrsmstag' + regional).addClass('active');
		
		var area = null;
		jQuery('.vr-smscont-1').each(function(){
			if (jQuery(this).css('display') != 'none') {
				area = jQuery(this);
			}
		});
		
		if (area == null) {
			return;
		}
		
		jQuery('.vr-smscont-1').hide();
		jQuery('#vrsmscont' + regional + '-' + area.attr('id').split('-')[1]).show();

		jQuery('#sms-site-langtag').text(langtag);
	}

	// switch type of contents (restaurant or takeaway)
	
	function switchSmsContent(section) {
		var area = null;
		jQuery('.vr-smscont-' + section).each(function() {
			if (jQuery(this).css('display') != 'none') {
				area = jQuery(this);
			}
		});
		
		if (area == null) {
			return;
		}
		
		var id = area.attr('id').split('-');
		area.hide();
		jQuery('#' + id[0] + '-' + (id[1] == '1' ? '2' : '1')).show();
		
		if (id[1] == '1') {
			jQuery('#vr-switch-button-' + section).html(Joomla.JText._('VRMANAGECONFIGSMS9'));

			// "For Take-Away Orders" button clicked, change badge with "Take-Away" text
			jQuery('#sms-site-section-' + section).text(Joomla.JText._('VRMENUTITLEHEADER5'));			
		} else {
			jQuery('#vr-switch-button-' + section).html(Joomla.JText._('VRMANAGECONFIGSMS10'));

			// "For Restaurant Reservations" button clicked, change badge with "Restaurant" text
			jQuery('#sms-site-section-' + section).text(Joomla.JText._('VRMENUTITLEHEADER1'));
		}
	}

</script>
