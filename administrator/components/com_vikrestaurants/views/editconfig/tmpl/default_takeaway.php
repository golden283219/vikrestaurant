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

$params = $this->params;

$vik = VREApplication::getInstance();

$all_tk_tmpl_files = glob(VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . '*.php');

$tmpl_elements = array();
$tmpl_base64   = array();

foreach ($all_tk_tmpl_files as $file)
{
	$filename = basename($file);

	// encode file path in base64 for being used in URLs
	$tmpl_base64[$filename] = base64_encode(VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . $filename);

	// remove file extension
	$name = preg_replace("/\.php$/i", '', $filename);
	// remove initial "takeaway_"
	$name = preg_replace("/^(takeaway|tk)_/i", '', $name);
	// remove ending "_mail_tmpl"
	$name = preg_replace("/_?e?mail_?tmpl$/i", '', $name);
	// replace dashes and underscores with spaces
	$name = preg_replace("/[-_]+/", ' ', $name);
	// capitalize words
	$name = ucwords(strtolower($name));

	$tmpl_elements[] = JHtml::_('select.option', $filename, $name);
}

$editor = $vik->getEditor();

/**
 * Trigger event to display custom HTML.
 * In case it is needed to include any additional fields,
 * it is possible to create a plugin and attach it to an event
 * called "onDisplayViewConfigTakeaway". The event method receives the
 * view instance as argument.
 *
 * @since 1.8.3
 */
$takeawayLeftForms  = $this->onDisplayView('Takeaway');
$takeawayRightForms = $this->onDisplayView('TakeawaySecondary');

?>

<!-- LEFT SIDE -->

<div class="config-left-side">

	<!-- RESERVATION Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETRESERVATION'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- TK DEFAULT STATUS - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option',   'PENDING',   'VRRESERVATIONSTATUSPENDING'),
				JHtml::_('select.option', 'CONFIRMED', 'VRRESERVATIONSTATUSCONFIRMED'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK12"); ?></b> </td>
				<td>
					<select name="tkdefstatus" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkdefstatus'], true); ?>
					</select>
				</td>
			</tr>

			<!-- SELF CONFIRMATION - Checkbox -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkselfconfirm']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkselfconfirm']);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG91'),
				'content' => JText::_('VRMANAGECONFIG91_HELP'),
			));
			?>
			<tr class="vr-tkdefstatus-child" style="<?php echo $params['tkdefstatus'] == 'PENDING' ? '' : 'display:none;'; ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG91"); ?></b><?php echo $help; ?> </td>
				<td><?php echo $vik->radioYesNo('tkselfconfirm', $yes, $no); ?></td>
			</tr>
			
			<!-- TK ENABLE CANCELLATION - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkenablecanc'], 'onClick="jQuery(\'.vrtkcancelchild\').show();"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkenablecanc'], 'onClick="jQuery(\'.vrtkcancelchild\').hide();"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK14"); ?></b></td>
				<td><?php echo $vik->radioYesNo('tkenablecanc', $yes, $no); ?></td>
			</tr>

			<!-- TK CANCELLATION REASON - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRCONFIGCANCREASONOPT0'),
				JHtml::_('select.option', 1, 'VRCONFIGCANCREASONOPT1'),
				JHtml::_('select.option', 2, 'VRCONFIGCANCREASONOPT2'),
			);
			?>
			<tr class="vrtkcancelchild" style="<?php echo ($params['tkenablecanc'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG68"); ?></b> </td>
				<td>
					<select name="tkcancreason" class="medium-large">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkcancreason'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- TK ACCEPT CANCELLATION BEFORE - Number -->
			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK15'),
				'content' => JText::_('VRMANAGECONFIG41_HELP'),
			));
			?>
			<tr class="vrtkcancelchild" style="<?php echo ($params['tkenablecanc'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK15"); ?></b><?php echo $help; ?> </td>
				<td>
					<input type="number" name="tkcanctime" value="<?php echo $params['tkcanctime']; ?>" min="0" max="999999" step="1">
					<span class="right-label">&nbsp;<?php echo JText::_('VRDAYS'); ?></span>
				</td>
			</tr>

			<!-- ACCEPT CANCELLATION WITHIN - Number -->
			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG90'),
				'content' => JText::_('VRMANAGECONFIG90_HELP'),
			));
			?>
			<tr class="vrtkcancelchild" style="<?php echo ($params['tkenablecanc'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG90"); ?></b><?php echo $help; ?> </td>
				<td>
					<input type="number" name="tkcancmins" value="<?php echo $params['tkcancmins']; ?>" min="0" max="999999" step="1">
					<span class="right-label">&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?></span>
				</td>
			</tr>
			
			<!-- TK MINUTES INTERVAL -->
			<?php
			$elements = array();
			foreach (array(10, 15, 20, 30, 60) as $min)
			{
				$elements[] = JHtml::_('select.option', $min, $min);
			}
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK1"); ?></b> </td>
				<td>
					<select name="tkminint" class="small-medium" id="vrtkminselect">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkminint']); ?>
					</select>
				</td>
			</tr>
			
			<!-- TK SOONEST DELIVERY AFTER - Dropdown -->
			<?php
			$elements = array();
			for ($i = 1; $i <= 20; $i++)
			{
				$elements[] = JHtml::_('select.option', $i, $i * $params['tkminint']);
			}
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK9"); ?></b></td>
				<td>
					<select name="asapafter" class="small-medium" id="vrtkasapselect">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['asapafter']); ?>
					</select>
					
					<span class="right-label">&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?></span>
				</td>
			</tr>

			<!-- TK KEEP ORDER LOCKED - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK8"); ?></b> </td>
				<td>
					<input type="number" id="tklocktime" name="tklocktime" value="<?php echo $params['tklocktime']; ?>" min="5" step="5" size="10" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?></span>
				</td>
			</tr>
			
			<!-- TK LOGIN REQUIREMENTS -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, 'VRCONFIGLOGINREQ1'),
				JHtml::_('select.option', 2, 'VRCONFIGLOGINREQ2'),
				JHtml::_('select.option', 3, 'VRCONFIGLOGINREQ3'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG33"); ?></b> </td>
				<td>
					<select name="tkloginreq" class="medium" id="vrtkloginreqsel">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkloginreq'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- TK ENABLE REGISTRATION - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkenablereg']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkenablereg']);
			?>
			<tr id="vrtkenableregtr" style="<?php echo ($params['tkloginreq'] > 1 ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG34"); ?></b></td>
				<td><?php echo $vik->radioYesNo('tkenablereg', $yes, $no); ?></td>
			</tr>

		</table>
	</div>

	<!-- STOCKS Fieldset -->

	<div class="config-fieldset" id="stocks-panel">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGTKSECTION2'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- ENABLE STOCK SYSTEM - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkenablestock'], 'onClick="jQuery(\'.vre-stock-child\').show();"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkenablestock'], 'onClick="jQuery(\'.vre-stock-child\').hide();"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK16"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('tkenablestock', $yes, $no); ?></td>
			</tr>
			
			<!-- EMAIL TEMPLATE -->
			<tr class="vre-stock-child" style="<?php echo ($params['tkenablestock'] == 1 ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK17"); ?></b> </td>
				<td>
					<select name="tkstockmailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['tkstockmailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="takeawayBeforeOpenModal('tkstockmailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>

						<button type="button" class="btn" onclick="goToMailPreview('takeaway', 'stock', 'tkstockmailtmpl');">
							<i class="fas fa-eye"></i>
						</button>
					</div>
				</td>
			</tr>

		</table>
	</div>

	<!-- TAKE-AWAY Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETTAKEAWAY'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- TK SHOW IMAGES - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkshowimages']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkshowimages']);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK30"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('tkshowimages', $yes, $no); ?></td>
			</tr>

			<!-- TK SHOW TIMES - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkshowtimes']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkshowtimes']);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK37'),
				'content' => JText::_('VRMANAGECONFIGTK37_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK37'); ?></b><?php echo $help; ?> </td>
				<td><?php echo $vik->radioYesNo('tkshowtimes', $yes, $no); ?></td>
			</tr>

			<!-- TK PROD DESC LENGTH - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK29"); ?></b></td>
				<td>
					<input type="number" name="tkproddesclength" value="<?php echo $params['tkproddesclength']; ?>" min="0" step="1" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRCHARS'); ?></span>
				</td>
			</tr>
			
			<!-- TK NOTES - Textarea -->
			<tr>
				<td width="200" style="vertical-align: top;" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK6');?></b> </td>
				<td>
					<textarea name="tknote" style="width: calc(100% - 36px);height: 100px;resize: vertical;"><?php echo $params['tknote']; ?></textarea>

					<!-- translation button -->
					<span class="config-trx" style="float:right;<?php echo $params['multilanguage'] ? '' : 'display:none;'; ?>">
						<a href="index.php?option=com_vikrestaurants&amp;view=langconfig&amp;param=tknote" target="_blank">
							<?php
							foreach ($this->translations['tknote'] as $lang)
							{
								echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
							}
							?>
						</a>
					</span>
				</td>
			</tr>

		</table>
	</div>

	<!-- ORDERS LIST COLUMNS Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGTK13'); ?></div>
		<table class="admintable" cellspacing="1">

			<?php
			$all_tklist_fields = array(
				'1'  => 'id', 
				'2'  => 'sid',
				'27' => 'payment',
				'3'  => 'checkin_ts',
				'4'  => 'delivery',
				'24' => 'customer',
				'5'  => 'mail',
				'23' => 'phone', 
				'6'  => 'info',
				'7'  => 'coupon',
				'8'  => 'totpay',
				'21' => 'taxes',
				'26' => 'rescode',
				'9'  => 'status',
			);

			$tklistable_fields = array();
			
			if (!empty($params['tklistablecols']))
			{
				$tklistable_fields = explode(",", $params['tklistablecols']);
			}
			
			foreach ($all_tklist_fields as $k => $f) 
			{
				$selected = in_array($f, $tklistable_fields); 
				
				$yes = $vik->initRadioElement('', JText::_('JYES'), $selected, 'onClick="toggleTkListField(\'' . $f . '\', 1);"');
				$no  = $vik->initRadioElement('', JText::_('JNO'), !$selected, 'onClick="toggleTkListField(\'' . $f . '\', 0);"');
				?>
				<tr>
					<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGETKRES".$k); ?></b> </td>
					<td>
						<?php echo $vik->radioYesNo($f . 'tklistcol', $yes, $no); ?>
						<input type="hidden" name="tklistablecols[]" value="<?php echo $f . ':' . $selected; ?>" id="vrtkhidden<?php echo $f; ?>" />
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
	 * the "takeaway-left" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($takeawayLeftForms as $formName => $formHtml)
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

	<!-- ORDER Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETORDER'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- TK MIN COST PER ORDER - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK5"); ?></b> </td> 
				<td>
					<div class="input-prepend currency-field">
						<button type="button" class="btn"><?php echo $params['currencysymb']; ?></button>

						<input type="number" name="mincostperorder" value="<?php echo $params['mincostperorder']; ?>" min="0" size="10" step="any" />
					</div>
				</td>
			</tr>

			<!-- TK ORDERS PER INTERVAL - Number -->
			<?php
			$options = array(
				JHtml::_('select.option', 0, 'VRTKORDERPICKUPOPTION'),
				JHtml::_('select.option', 1, 'VRTKORDERDELIVERYOPTION'),
				JHtml::_('select.option', 2, 'VRTKCONFIGOVERLAYOPT2'),
			);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK39'),
				'content' => JText::_('VRMANAGECONFIGTK39_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK39"); ?></b><?php echo $help; ?> </td> 
				<td>
					<input type="number" name="tkordperint" value="<?php echo $params['tkordperint']; ?>" min="0" size="6" step="1" max="999999" />
					<select name="tkordmaxser" class="small-medium">
						<?php echo JHtml::_('select.options', $options, 'value', 'text', $params['tkordmaxser'], true); ?>
					</select>
				</td>
			</tr>

			<!-- TK MEALS PER INTERVAL - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK2"); ?></b> </td> 
				<td>
					<input type="number" name="mealsperint" value="<?php echo $params['mealsperint']; ?>" min="1" size="10" step="1" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRPREPARATIONMEALS'); ?></span>
				</td>
			</tr>

			<!-- TK MAX ITEMS IN CART - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK25"); ?></b> </td> 
				<td>
					<input type="number" name="tkmaxitems" value="<?php echo $params['tkmaxitems']; ?>" min="1" size="10" step="1" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRPREPARATIONMEALS'); ?></span>
				</td>
			</tr>

			<!-- TK MEALS SLOTS BACKWARD - Dropdown -->
			<?php
			$elements = array();
			for ($i = 1; $i <= 10; $i++)
			{
				$elements[] = JHtml::_('select.option', $i, $i);
			}

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK34'),
				'content' => JText::_('VRMANAGECONFIGTK34_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK34'); ?></b><?php echo $help; ?> </td>
				<td>
					<select name="tkmealsbackslots">
						<option></option>
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkmealsbackslots']); ?>
					</select>
				</td>
			</tr>

			<!-- TK USE OVERLAY - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 2, 'VRTKCONFIGOVERLAYOPT2'),
				JHtml::_('select.option', 1, 'VRTKCONFIGOVERLAYOPT1'),
				JHtml::_('select.option', 0, 'VRTKCONFIGOVERLAYOPT0'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK28"); ?></b></td>
				<td>
					<select name="tkuseoverlay" class="medium-large">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkuseoverlay'], true); ?>
					</select>
				</td>
			</tr>

			<!-- TK TODAY ORDERS - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkallowdate'], 'onClick="tkAllowDateValueChanged(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkallowdate'], 'onClick="tkAllowDateValueChanged(0);"');

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK26'),
				'content' => JText::_('VRMANAGECONFIGTK26_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK26'); ?></b><?php echo $help; ?> </td> 
				<td><?php echo $vik->radioYesNo('tkallowdate', $yes, $no); ?></td>
			</tr>

			<!-- TK LIVE ORDERS - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkwhenopen'], 'onClick="tkWhenOpenValueChanged(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkwhenopen'], 'onClick="tkWhenOpenValueChanged(0);"');

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK27'),
				'content' => JText::_('VRMANAGECONFIGTK27_HELP'),
			));
			?>
			<tr class="tkallowdate-child-0" style="<?php echo ($params['tkallowdate'] == 1 ? 'display:none;' : ''); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK27'); ?></b><?php echo $help; ?> </td> 
				<td><?php echo $vik->radioYesNo('tkwhenopen', $yes, $no); ?></td>
			</tr>

			<!-- TK PRE ORDERS - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkpreorder']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkpreorder']);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK38'),
				'content' => JText::_('VRMANAGECONFIGTK38_HELP'),
			));
			?>
			<tr class="tkwhenopen-rel-0" style="<?php echo ($params['tkwhenopen'] == 1 ? 'display:none;' : ''); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK38'); ?></b><?php echo $help; ?> </td> 
				<td><?php echo $vik->radioYesNo('tkpreorder', $yes, $no); ?></td>
			</tr>

			<!-- TK MIN DATE - Select -->
			<?php
			$elements = array(
				JHtml::_('select.option', '', ''),
				JHtml::_('select.option', 1, JText::plural('VRE_N_DAYS', 1)),
				JHtml::_('select.option', 2, JText::plural('VRE_N_DAYS', 2)),
				JHtml::_('select.option', 3, JText::plural('VRE_N_DAYS', 3)),
				JHtml::_('select.option', 4, JText::plural('VRE_N_DAYS', 4)),
				JHtml::_('select.option', 5, JText::plural('VRE_N_DAYS', 5)),
				JHtml::_('select.option', 6, JText::plural('VRE_N_DAYS', 6)),
				JHtml::_('select.option', 7, JText::plural('VRE_N_WEEKS', 1)),
				JHtml::_('select.option', 14, JText::plural('VRE_N_WEEKS', 2)),
			);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK35'),
				'content' => JText::_('VRMANAGECONFIGTK35_HELP'),
			));
			?>
			<tr class="tkallowdate-child-1" style="<?php echo ($params['tkallowdate'] == 0 ? 'display:none;' : ''); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK35'); ?></b><?php echo $help; ?> </td> 
				<td>
					<select name="tkmindate">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkmindate']); ?>
					</select>
				</td>
			</tr>

			<!-- TK MAX DATE - Select -->
			<?php
			$elements = array(
				JHtml::_('select.option', '', ''),
				JHtml::_('select.option', 1, JText::plural('VRE_N_DAYS', 1)),
				JHtml::_('select.option', 2, JText::plural('VRE_N_DAYS', 2)),
				JHtml::_('select.option', 3, JText::plural('VRE_N_DAYS', 3)),
				JHtml::_('select.option', 4, JText::plural('VRE_N_DAYS', 4)),
				JHtml::_('select.option', 5, JText::plural('VRE_N_DAYS', 5)),
				JHtml::_('select.option', 6, JText::plural('VRE_N_DAYS', 6)),
				JHtml::_('select.option', 7, JText::plural('VRE_N_WEEKS', 1)),
				JHtml::_('select.option', 14, JText::plural('VRE_N_WEEKS', 2)),
				JHtml::_('select.option', 30, JText::plural('VRE_N_MONTHS', 1)),
				JHtml::_('select.option', 60, JText::plural('VRE_N_MONTHS', 2)),
			);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK36'),
				'content' => JText::_('VRMANAGECONFIGTK36_HELP'),
			));
			?>
			<tr class="tkallowdate-child-1" style="<?php echo ($params['tkallowdate'] == 0 ? 'display:none;' : ''); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK36'); ?></b><?php echo $help; ?> </td> 
				<td>
					<select name="tkmaxdate">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkmaxdate']); ?>
					</select>
				</td>
			</tr>

			<!-- TK ENABLE GRATUITY - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkenablegratuity'], 'onClick="tkEnableGratuityValueChanged(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkenablegratuity'], 'onClick="tkEnableGratuityValueChanged(0);"');

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIGTK32'),
				'content' => JText::_('VRMANAGECONFIGTK32_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIGTK32'); ?></b><?php echo $help; ?> </td> 
				<td><?php echo $vik->radioYesNo('tkenablegratuity', $yes, $no); ?></td>
			</tr>

			<!-- TK SUGGESTED GRATUITY - Form -->
			<?php
			$def_gratuity  = 0;
			$gratuity_type = 1;

			if (preg_match("/^([\d.,]+):([12])$/", $params['tkdefgratuity'], $matches))
			{
				$def_gratuity  = (float) $matches[1];
				$gratuity_type = (int) $matches[2];
			}

			$elements = array(
				JHtml::_('select.option', 1, '%'),
				JHtml::_('select.option', 2, $params['currencysymb']),
			);
			?>
			<tr class="tkenablegratuity-child" style="<?php echo ($params['tkenablegratuity'] == 0 ? 'display:none;' : ''); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK33"); ?></b> </td> 
				<td>
					<input type="number" name="tkdefgrat_amount" value="<?php echo $def_gratuity; ?>" min="0" max="99999" size="6" step="any" />

					<select name="tkdefgrat_percentot" class="short">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $gratuity_type); ?>
					</select>
				</td>
			</tr>

		</table>
	</div>

	<!-- DELIVERY Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETDELIVERY'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- TK ENABLE DELIVERY SERVICE -->
			<?php
			$elements = array(
				JHtml::_('select.option', 2, 'VRDELIVERYSERVICEOPT3'),
				JHtml::_('select.option', 1, 'VRDELIVERYSERVICEOPT2'),
				JHtml::_('select.option', 0, 'VRDELIVERYSERVICEOPT1'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK3"); ?></b> </td>
				<td>
					<select name="deliveryservice" class="medium" id="vr-deliveryservice-sel">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['deliveryservice'], true); ?>
					</select>		
				</td>
			</tr>

			<!-- TK DEFAULT SERVICE -->
			<?php
			$disabled = $params['deliveryservice'] != 2 ? 'disabled="disabled"' : '';

			$elements = array(
				JHtml::_('select.option', 'delivery', 'VRTKORDERDELIVERYOPTION'),
				JHtml::_('select.option',   'pickup',   'VRTKORDERPICKUPOPTION'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK31"); ?></b> </td>
				<td>
					<select name="tkdefaultservice" class="medium" id="vr-tkdefservice-sel" <?php echo $disabled; ?>>
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkdefaultservice'], true); ?>
					</select>		
				</td>
			</tr>
			
			<!-- TK DELIVERY COST - Number -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, '%'),
				JHtml::_('select.option', 2, $params['currencysymb']),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK4"); ?></b> </td> 
				<td>
					<input type="number" name="dsprice" class="delivery-param" value="<?php echo $params['dsprice']; ?>" min="0" max="99999" size="6" step="any" <?php echo ($params['deliveryservice'] == 0 ? 'readonly="readonly"' : ''); ?> />

					<select name="dspercentot" class="delivery-param short" <?php echo ($params['deliveryservice'] == 0 ? 'disabled="disabled"' : ''); ?>>
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['dspercentot']); ?>
					</select>	
				</td>
			</tr>
			
			<!-- TK FREE DELIVERY WITH - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK7"); ?></b> </td> 
				<td>
					<div class="input-prepend currency-field">
						<button type="button" class="btn"><?php echo $params['currencysymb']; ?></button>

						<input type="number" name="freedelivery" class="delivery-param" value="<?php echo $params['freedelivery']; ?>" min="0" max="99999" size="6" step="any" <?php echo ($params['deliveryservice'] == 0 ? 'readonly="readonly"' : ''); ?> />
					</div>
				</td>
			</tr>

			<!-- TK PICKUP COST - Number -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, '%'),
				JHtml::_('select.option', 2, $params['currencysymb']),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK18"); ?></b> </td> 
				<td>
					<input type="number" name="pickupprice" class="pickup-param" value="<?php echo $params['pickupprice']; ?>" max="99999" size="6" step="any" <?php echo ($params['deliveryservice'] == 1 ? 'readonly="readonly"' : ''); ?> />

					<select name="pickuppercentot" class="pickup-param short" <?php echo ($params['deliveryservice'] == 1 ? 'disabled="disabled"' : ''); ?>>
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['pickuppercentot']); ?>
					</select>
				</td>
			</tr>

		</table>
	</div>

	<!-- TAXES Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETTAXES'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- TK TAXES RATIO - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK10"); ?></b> </td>
				<td>
					<div class="input-append">
						<input type="number" id="tktaxesratio" name="tktaxesratio" value="<?php echo $params['tktaxesratio']; ?>" min="0" max="99999" size="6" step="any" />
						
						<button type="button" class="btn">%</button>
					</div>
				</td>
			</tr>

			<!-- TK USE TAXES - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRTKCONFIGUSETAXOPT0'),
				JHtml::_('select.option', 1, 'VRTKCONFIGUSETAXOPT1'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK24"); ?></b> </td>
				<td>
					<select name="tkusetaxes" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkusetaxes'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- TK SHOW TAXES - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['tkshowtaxes']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['tkshowtaxes']);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK11"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('tkshowtaxes', $yes, $no); ?></td>
			</tr>

		</table>
	</div>

	<!-- ORIGIN ADDRESSES Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGTK19'); ?></div>
		<table class="admintable" cellspacing="1">

			<tr>
				<td colspan="2">
					<?php
					echo $vik->alert(JText::_('VRE_CONFIG_ORIGINS_SCOPE'), 'info');
					?>

					<a href="index.php?option=com_vikrestaurants&amp;view=origins" target="_blank" class="btn"><?php echo JText::_('VRMANAGECONFIGTK21'); ?></a>
				</td>
			</tr>

		</table>
	</div>

	<!-- EMAIL Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGGLOBSECTION2'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- SEND TO CUSTOMER WHEN - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, 'VRCONFIGSENDMAILWHEN1'),
				JHtml::_('select.option', 2, 'VRCONFIGSENDMAILWHEN2'),
				JHtml::_('select.option', 0, 'VRCONFIGSENDMAILWHEN0'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG44"); ?></b> </td>
				<td>
					<select name="tkmailcustwhen" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkmailcustwhen'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- SEND TO EMPLOYEE WHEN - Dropdown -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG45"); ?></b> </td>
				<td>
					<select name="tkmailoperwhen" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkmailoperwhen'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- SEND TO ADMIN WHEN - Dropdown -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG46"); ?></b> </td>
				<td>
					<select name="tkmailadminwhen" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['tkmailadminwhen'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- TAKE-AWAY CUSTOMER EMAIL TEMPLATE -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG47"); ?></b> </td>
				<td>
					<select name="tkmailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['tkmailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="takeawayBeforeOpenModal('tkmailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>

						<button type="button" class="btn" onclick="goToMailPreview('takeaway', 'customer', 'tkmailtmpl');">
							<i class="fas fa-eye"></i>
						</button>
					</div>
				</td>
			</tr>

			<!-- TAKE-AWAY ADMIN EMAIL TEMPLATE -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG56"); ?></b> </td>
				<td>
					<select name="tkadminmailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['tkadminmailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="takeawayBeforeOpenModal('tkadminmailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>

						<button type="button" class="btn" onclick="goToMailPreview('takeaway', 'admin', 'tkadminmailtmpl');">
							<i class="fas fa-eye"></i>
						</button>
					</div>
				</td>
			</tr>

			<!-- TAKE-AWAY CANCELLATION EMAIL TEMPLATE -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG57"); ?></b> </td>
				<td>
					<select name="tkcancmailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['tkcancmailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="takeawayBeforeOpenModal('tkcancmailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>

						<button type="button" class="btn" onclick="goToMailPreview('takeaway', 'cancellation', 'tkcancmailtmpl');">
							<i class="fas fa-eye"></i>
						</button>
					</div>
				</td>
			</tr>

			<!-- TAKE-AWAY REVIEW EMAIL TEMPLATE -->
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" || $params['revtakeaway'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG67"); ?></b> </td>
				<td>
					<select name="tkreviewmailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['tkreviewmailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="takeawayBeforeOpenModal('tkreviewmailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>

						<button type="button" class="btn" onclick="goToMailPreview('takeaway', 'review', 'tkreviewmailtmpl');">
							<i class="fas fa-eye"></i>
						</button>
					</div>
				</td>
			</tr>

		</table>
	</div>

	<!-- RESERVATION COLUMNS CF LIST Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMENUCUSTOMFIELDS'); ?></div>
		<table class="admintable" cellspacing="1">
			<?php 
			$all_list_fields = array();
			foreach ($this->customFields as $field)
			{
				if ($field['group'] == 1)
				{
					$all_list_fields[$field['id']] = $field['name'];
				}
			}

			$listable_fields = (array) json_decode($params['tklistablecf'], true);
			
			foreach ($all_list_fields as $k => $field)
			{
				$selected = in_array($field, $listable_fields); 

				$yes = $vik->initRadioElement('', JText::_('JYES'), $selected, 'onClick="toggleListFieldCF(\''.addslashes($field).'\', ' . $k . ', 1);"');
				$no  = $vik->initRadioElement('', JText::_('JNO'), !$selected, 'onClick="toggleListFieldCF(\''.addslashes($field).'\', ' . $k . ', 0);"');
				?>
				<tr>
					<td width="200" class="adminparamcol"> <b><?php echo JText::_($field); ?></b></td>
					<td>
						<?php echo $vik->radioYesNo('listcf' . $k, $yes, $no); ?>
						<input type="hidden" name="tklistablecf[]" value="<?php echo $field . ':' . $selected; ?>" id="vrcfhidden<?php echo $k; ?>" />
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
	 * the "takeaway-right" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($takeawayRightForms as $formName => $formHtml)
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

<?php
JText::script('VRTKCONFIGITEMOPT0');
JText::script('VRMANAGECONFIG32');
?>

<script type="text/javascript">

	jQuery(document).ready(function() {

		// listen console to catch any interesting error
		VikMapsFailure.listenConsole();

		jQuery('#vrtk-confitem-sel').select2({
			placeholder: Joomla.JText._('VRTKCONFIGITEMOPT0'),
			allowClear: true,
			width: 250,
		});

		jQuery('select[name="tkmealsbackslots"],select[name="tkmindate"],select[name="tkmaxdate"]').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRMANAGECONFIG32'),
			allowClear: true,
			width: 150,
		});

		// handle ASAP

		jQuery('#vrtkminselect').on('change', function() {
			asapChanged();
		});

		// handle self confirmation

		jQuery('select[name="tkdefstatus"]').on('change', function() {
			if (jQuery(this).val() == 'PENDING') {
				jQuery('.vr-tkdefstatus-child').show();
			} else {
				jQuery('.vr-tkdefstatus-child').hide();
			}
		});

		// handle login requirements

		jQuery('#vrtkloginreqsel').on('change', function() {
			if (parseInt(jQuery(this).val()) > 1) {
				jQuery('#vrtkenableregtr').show();
			} else {
				jQuery('#vrtkenableregtr').hide();
			}
		});

		jQuery('#vrtk-confitem-sel').on('change', function(){
			if (jQuery(this).val() == "-1") {
				jQuery('input[name="tkconfitem_custom"]').show();
			} else {
				jQuery('input[name="tkconfitem_custom"]').hide();
			}
		});

		jQuery('#vr-deliveryservice-sel').on('change', function(){
			var val = jQuery(this).val();

			jQuery('input.delivery-param').prop('readonly', (val == 0 ? true : false));
			jQuery('select.delivery-param').prop('disabled', (val == 0 ? true : false));
			
			jQuery('input.pickup-param').prop('readonly', (val == 1 ? true : false));
			jQuery('select.pickup-param').prop('disabled', (val == 1 ? true : false));

			jQuery('#vr-tkdefservice-sel').prop('disabled', (val != 2 ? true : false));

			if (val == 1) {
				jQuery('#vr-tkdefservice-sel').select2('val', 'delivery');
			} else if (val == 0) {
				jQuery('#vr-tkdefservice-sel').select2('val', 'pickup');
			}
		});
	});

	// handle first possible time

	function asapChanged() {
		var mins = parseInt(jQuery('#vrtkminselect').val());

		jQuery('#vrtkasapselect option').each(function(){
			jQuery(this).text((jQuery(this).val()*mins));
		});

		jQuery('#vrtkasapselect').select2('val', jQuery('#vrtkasapselect').val());
	}

	// handle date allowed setting

	function tkAllowDateValueChanged(is) {
		if (is) {
			jQuery('.tkallowdate-child-0').hide();
			jQuery('.tkallowdate-child-1').show();
		} else {
			jQuery('.tkallowdate-child-0').show();
			jQuery('.tkallowdate-child-1').hide();
		}
	}

	// handle live order setting

	function tkWhenOpenValueChanged(is) {
		if (is) {
			jQuery('.tkwhenopen-rel-0').hide();
		} else {
			jQuery('.tkwhenopen-rel-0').show();
		}
	}

	// handle gratuity settings

	function tkEnableGratuityValueChanged(is) {
		if (is) {
			jQuery('.tkenablegratuity-child').show();
		} else {
			jQuery('.tkenablegratuity-child').hide();
		}
	}

	// toggle order columns

	function toggleTkListField(id, value) {
		jQuery('#vrtkhidden' + id).val(id + ':' + value);
	}

	// fetch modal

	var TKMAIL_TMPL_LOOKUP = <?php echo json_encode((object) $tmpl_base64); ?>;

	function takeawayBeforeOpenModal(id) {
		var url  = null;
		
		SELECTED_MAIL_TMPL_FIELD = jQuery('select[name="' + id + '"]');
		SELECTED_MAIL_TMPL_GROUP = 'takeaway';

		// get file name
		var file = SELECTED_MAIL_TMPL_FIELD.val();
		// make sure the path exists
		var path = TKMAIL_TMPL_LOOKUP.hasOwnProperty(file) ? TKMAIL_TMPL_LOOKUP[file] : '';

		switch (id) {
			case 'tkmailtmpl':
			case 'tkadminmailtmpl':
			case 'tkcancmailtmpl':
			case 'tkreviewmailtmpl':
			case 'tkstockmailtmpl':
				url = 'index.php?option=com_vikrestaurants&tmpl=component&task=file.edit&cid[]=' + path;
				id  = 'managetmpl';
				break;
		}

		vrOpenJModal(id, url, true);
	}

	// register Google Autocomplete

	function registerAutocompleteField(fields) {
		if (typeof google === 'undefined' || typeof google.maps.places === 'undefined') {
			// Missing Google API Key or Places API not enabled, do not proceed
			return false;
		}

		<?php
		if (VikRestaurants::isGoogleMapsApiEnabled('places'))
		{
			// include JavaScript code to support the addresses autocompletion
			// only in case the Places API is enabled in the configuration

			?>
			// iterate all fields
			jQuery(fields).each(function() {
				var input = this;

				// use Google Autocomplete feature
				var googleAddress = new google.maps.places.Autocomplete(
					jQuery(input)[0], {}
				);

				jQuery(window).on('google.autherror google.apidisabled.places', function() {
					// disable autocomplete on failure
					VikMapsFailure.disableAutocomplete(jQuery(input)[0], googleAddress);
				});

				VikGeo.getCurrentPosition().then(function(coord) {
					// coordinates retrieved, set up google bounds
					var circle = new google.maps.Circle({
						center: coord,
						radius: 100,
					});

		  			googleAddress.setBounds(circle.getBounds());
				}).catch(function(error) {
					// unable to obtain current position, show error
					console.error(error);
				});

			});
			<?php
		}
		?>
	}

</script>
