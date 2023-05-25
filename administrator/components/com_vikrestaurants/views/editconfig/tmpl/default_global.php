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

// parse closing days

$closing_days = VikRestaurants::getClosingDays();

$date = JDate::getInstance();

for ($i = 0; $i < count($closing_days); $i++)
{
	$cd = $closing_days[$i];

	if ($cd['freq'] == 1)
	{
		// week frequency
		$closing_days[$i]['freqtitle'] = $date->dayToString(date('w', $cd['ts']));
	}
	else
	{
		// use translation
		$closing_days[$i]['freqtitle'] = JText::_('VRFREQUENCYTYPE' . $cd['freq']);
	}
}

/**
 * Trigger event to display custom HTML.
 * In case it is needed to include any additional fields,
 * it is possible to create a plugin and attach it to an event
 * called "onDisplayViewConfigGlobal". The event method receives the
 * view instance as argument.
 *
 * @since 1.8.3
 */
$globalLeftForms  = $this->onDisplayView('Global');
$globalRightForms = $this->onDisplayView('GlobalSecondary');

?>

<!-- LEFT SIDE -->

<div class="config-left-side">

	<!-- SYSTEM Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGGLOBSECTION1'); ?></div>
		<table class="admintable" cellspacing="1">
		
			<!-- RESTAURANT NAME - Text -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG0"); ?></b> </td>
				<td><input type="text" name="restname" class="required" value="<?php echo $params['restname']; ?>" size="40"></td>
			</tr>
			
			<!-- COMPANY LOGO - File -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG4"); ?></b> </td>
				<td>
					<?php echo JHtml::_('vrehtml.mediamanager.field', 'companylogo', $params['companylogo']); ?>
				</td>
			</tr>

			<!-- ENABLE RESTAURANT - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['enablerestaurant'], 'onClick="enableTabValueChanged(2, 1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['enablerestaurant'], 'onClick="enableTabValueChanged(2, 0);"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG54"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('enablerestaurant', $yes, $no); ?></td>
			</tr>

			<!-- ENABLE TAKEAWAY - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['enabletakeaway'], 'onClick="enableTabValueChanged(3, 1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['enabletakeaway'], 'onClick="enableTabValueChanged(3, 0);"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK0"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('enabletakeaway', $yes, $no); ?></td>
			</tr>
			
			<!-- DATE FORMAT - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 'Y/m/d', 'VRCONFIGDATEFORMAT1'),
				JHtml::_('select.option', 'm/d/Y', 'VRCONFIGDATEFORMAT2'),
				JHtml::_('select.option', 'd/m/Y', 'VRCONFIGDATEFORMAT3'),
				JHtml::_('select.option', 'Y-m-d', 'VRCONFIGDATEFORMAT4'),
				JHtml::_('select.option', 'm-d-Y', 'VRCONFIGDATEFORMAT5'),
				JHtml::_('select.option', 'd-m-Y', 'VRCONFIGDATEFORMAT6'),
				JHtml::_('select.option', 'Y.m.d', 'VRCONFIGDATEFORMAT7'),
				JHtml::_('select.option', 'm.d.Y', 'VRCONFIGDATEFORMAT8'),
				JHtml::_('select.option', 'd.m.Y', 'VRCONFIGDATEFORMAT9'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG5"); ?></b> </td>
				<td>
					<select name="dateformat" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['dateformat'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- TIME FORMAT - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 'h:i A', 'VRCONFIGTIMEFORMAT1'),
				JHtml::_('select.option',   'H:i', 'VRCONFIGTIMEFORMAT2'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG6"); ?></b> </td>
				<td>
					<select name="timeformat" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['timeformat'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- WORKING TIME MODE -->
			<?php
			$elements = array(
				JHtml::_('select.option', '0', 'VRCONFIGOPENTIME1'),
				JHtml::_('select.option', '1', 'VRCONFIGOPENTIME2'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG10"); ?></b> </td>
				<td>
					<select name="opentimemode" class="medium" id="vropentimeselect">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['opentimemode'], true); ?>
					</select>
				</td>
			</tr>

			<!-- CONTINUOUS OPENING HOUR -->
			<tr class="opening-cont-field" style="<?php echo ($params['opentimemode'] == 0 ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGESHIFT2"); ?></b> </td>
				<td><input type="number" name="hourfrom" value="<?php echo $params['hourfrom']; ?>" min="0" max="23" /></td>
			</tr>

			<!-- CONTINUOUS CLOSING HOUR -->
			<tr class="opening-cont-field" style="<?php echo ($params['opentimemode'] == 0 ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGESHIFT3"); ?></b> </td>
				<td><input type="number" name="hourto" value="<?php echo $params['hourto']; ?>" min="0" max="23" /></td>
			</tr>
			
			<!-- ENABLE MULTILANGUAGE - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['multilanguage'], 'onclick="multilangValueChanged(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['multilanguage'], 'onclick="multilangValueChanged(0);"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG50"); ?></b> </td>
				<td>
					<?php echo $vik->radioYesNo('multilanguage', $yes, $no); ?>
				</td>
			</tr>

			<!-- SHOW PHONE PREFIX - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['phoneprefix']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['phoneprefix']);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG80"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('phoneprefix', $yes, $no); ?></td>
			</tr>
			
			<!-- REFRESH DASHBOARD TIME - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG37"); ?></b> </td>
				<td>
					<input type="number" name="refreshdash" value="<?php echo $params['refreshdash']; ?>" size="10" min="15">
					<span class="right-label">&nbsp;<?php echo JText::_('VRSHORTCUTSECOND'); ?></span>
				</td>
			</tr>

			<!-- CHECKBOX STYLE - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option',    'ios', 'VRCONFIGUIRADIOOPT1'),
				/**
				 * Do not allow Joomla style selection as the component will
				 * be available also for WordPress.
				 *
				 * @since 1.8
				 */
				// JHtml::_('select.option', 'joomla', 'VRCONFIGUIRADIOOPT2'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG78"); ?></b> </td>
				<td>
					<select name="uiradio" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['uiradio'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- LOAD JQUERY - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['loadjquery']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['loadjquery']);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG15"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('loadjquery', $yes, $no); ?></td>
			</tr>
			
			<!-- SHOW FOOTER - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['showfooter']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['showfooter']);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG23"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('showfooter', $yes, $no); ?></td>
			</tr>

			<!-- CURRENT TIMEZONE - Label -->
			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG79'),
				'content' => JText::sprintf('VRMANAGECONFIG79_HELP', JFactory::getApplication()->get('offset', 'UTC')),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG79"); ?></b><?php echo $help; ?> </td>
				<td>
					<span class="badge badge-info">
						<?php echo str_replace('_', ' ', date_default_timezone_get()); ?>
					</span>

					<span class="badge badge-important">
						<?php echo date('Y-m-d H:i:s T'); ?>
					</span>
				</td>
			</tr>

			<?php
			if (isset($params['wizardstate']) && (int) $params['wizardstate'])
			{
				?>
				<!-- RESTORE WIZARD - Button -->
				<tr>
					<td width="200" class="adminparamcol">&nbsp;</td>
					<td>
						<a href="index.php?option=com_vikrestaurants&task=wizard.restore" target="_blank" class="btn">
							<?php echo JText::_('VRWIZARDBTNREST'); ?>
						</a>
					</td>
				</tr>
				<?php
			}
			?>

		
		</table>
	</div>

	<!-- REVIEWS Fieldset -->

	<div class="config-fieldset" id="reviews-panel">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETREVIEWS'); ?></div>
		<table class="admintable" cellspacing="1">
			
			<!-- ENABLE REVIEWS - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['enablereviews'], 'onClick="reviewsValueChanged(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['enablereviews'], 'onClick="reviewsValueChanged(0);"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG58"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('enablereviews', $yes, $no); ?></td>
			</tr>
			
			<!-- TAKEAWAY REVIEWS - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['revtakeaway']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['revtakeaway']);
			?>
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG59"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('revtakeaway', $yes, $no); ?></td>
			</tr>
			
			<!-- REVIEWS LEAVE MODE - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRCONFIGREVLEAVEMODEOPT0'),
				JHtml::_('select.option', 1, 'VRCONFIGREVLEAVEMODEOPT1'),
				JHtml::_('select.option', 2, 'VRCONFIGREVLEAVEMODEOPT2'),
			);
			?>
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG60"); ?></b> </td>
				<td>
					<select name="revleavemode" class="medium-large">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['revleavemode'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- REVIEW COMMENT REQUIRED - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['revcommentreq']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['revcommentreq']);
			?>
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG61"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('revcommentreq', $yes, $no); ?></td>
			</tr>
			
			<!-- MIN COMMENT LENGTH - Number -->
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG62"); ?></b> </td>
				<td>
					<input type="number" name="revminlength" value="<?php echo $params['revminlength']; ?>" min="0" step="1" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRCHARS'); ?></span>
				</td>
			</tr>
			
			<!-- MAX COMMENT LENGTH - Number -->
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG63"); ?></b> </td>
				<td>
					<input type="number" name="revmaxlength" value="<?php echo $params['revmaxlength']; ?>" min="32" step="1" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRCHARS'); ?></span>
				</td>
			</tr>
			
			<!-- REVIEWS LIST LIMIT - Number -->
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG64"); ?></b> </td>
				<td><input type="number" name="revlimlist" value="<?php echo $params['revlimlist']; ?>" min="1" step="1" /></td>
			</tr>
			
			<!-- AUTO PUBLISHED - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['revautopublished']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['revautopublished']);
			?>
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG65"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('revautopublished', $yes, $no); ?></td>
			</tr>
			
			<!-- FILTER BY LANGUAGE - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['revlangfilter']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['revlangfilter']);
			?>
			<tr class="vrreviewstr" <?php echo ($params['enablereviews'] == "0" ? 'style="display:none;"' : ''); ?>>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG66"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('revlangfilter', $yes, $no); ?></td>
			</tr>
			
		</table>
	</div>

	<?php
	/**
	 * Iterate remaining forms to be displayed within
	 * the "global-left" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($globalLeftForms as $formName => $formHtml)
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

	<!-- EMAIL Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGGLOBSECTION2'); ?></div>
		<table class="admintable" cellspacing="1">
			
			<!-- ADMIN EMAIL - Text -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG1"); ?></b> </td>
				<td><input type="text" name="adminemail" class="required" value="<?php echo $params['adminemail']; ?>" size="40"></td>
			</tr>
			
			<!-- SENDER EMAIL - Text -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG43"); ?></b> </td>
				<td><input type="email" name="senderemail" value="<?php echo $params['senderemail']; ?>" size="40"></td>
			</tr>
			
		</table>
	</div>

	<!-- CURRENCY Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGGLOBSECTION3'); ?></div>
		<table class="admintable" cellspacing="1">
			
			<!-- CURRENCY SYMB - Text -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG7"); ?></b> </td>
				<td><input type="text" name="currencysymb" value="<?php echo $params['currencysymb']; ?>" size="10"></td>
			</tr>
			
			<!-- CURRENCY NAME - Text --> 
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG8"); ?></b> </td>
				<td><input type="text" name="currencyname" value="<?php echo $params['currencyname']; ?>" size="10"></td>
			</tr>
			
			<!-- CURRENCY SYMB POSITION - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', '1', 'VRCONFIGSYMBPOSITION1'),
				JHtml::_('select.option', '2', 'VRCONFIGSYMBPOSITION2'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG25"); ?></b> </td>
				<td>
					<select name="symbpos" class="small-medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['symbpos'], true); ?>
					</select>

					<!-- translation button -->
					<span class="config-trx" style="<?php echo $params['multilanguage'] ? '' : 'display:none;'; ?>">
						<a href="index.php?option=com_vikrestaurants&amp;view=langconfig&amp;param=symbpos" target="_blank">
							<?php
							foreach ($this->translations['symbpos'] as $lang)
							{
								echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
							}
							?>
						</a>
					</span>
				</td>
			</tr>

			<!-- CURRENCY DECIMAL SEPARATOR - Text -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG51"); ?></b> </td>
				<td>
					<input type="text" name="currdecimalsep" value="<?php echo $params['currdecimalsep']; ?>" size="10" />

					<!-- translation button -->
					<span class="config-trx" style="<?php echo $params['multilanguage'] ? '' : 'display:none;'; ?>">
						<a href="index.php?option=com_vikrestaurants&amp;view=langconfig&amp;param=currdecimalsep" target="_blank">
							<?php
							foreach ($this->translations['currdecimalsep'] as $lang)
							{
								echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
							}
							?>
						</a>
					</span>
				</td>
			</tr>

			<!-- CURRENCY THOUSANDS SEPARATOR - Text -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG52"); ?></b> </td>
				<td>
					<input type="text" name="currthousandssep" value="<?php echo $params['currthousandssep']; ?>" size="10" />

					<!-- translation button -->
					<span class="config-trx" style="<?php echo $params['multilanguage'] ? '' : 'display:none;'; ?>">
						<a href="index.php?option=com_vikrestaurants&amp;view=langconfig&amp;param=currthousandssep" target="_blank">
							<?php
							foreach ($this->translations['currthousandssep'] as $lang)
							{
								echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
							}
							?>
						</a>
					</span>
				</td>
			</tr>

			<!-- CURRENCY NUMBER OF DECIMALS - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG53"); ?></b> </td>
				<td><input type="number" name="currdecimaldig" value="<?php echo $params['currdecimaldig']; ?>" min="0" max="9999" step="1" /></td>
			</tr>
			
		</table>
	</div>

	<!-- Google Maps Fieldset -->
	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGGLOBSECTION4'); ?></div>
		<table class="admintable" cellspacing="1">
			
			<!-- API KEY - Text -->
			<?php
			$help = $vik->createPopover(array(
				'title' 	=> JText::_('VRMANAGECONFIG55'),
				'content' 	=> JText::_('VRMANAGECONFIG55_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG55'); ?></b><?php echo $help; ?> </td>
				<td>
					<div class="input-append">
						<input type="text" name="googleapikey" value="<?php echo $params['googleapikey']; ?>" size="44" <?php echo (strlen($params['googleapikey']) ? 'readonly' : ''); ?> />
					
						<?php
						if (strlen($params['googleapikey']))
						{
							?>
							<button type="button" class="btn" onClick="lockUnlockInput(this);">
								<i class="fas fa-lock"></i>
							</button>
							<?php
						}
						?>
					</div>
				</td>
			</tr>

			<!-- PLACES API - Checkbox -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['googleapiplaces']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['googleapiplaces']);

			$help = $vik->createPopover(array(
				'title' 	=> JText::_('VRMANAGECONFIG84'),
				'content' 	=> JText::_('VRMANAGECONFIG84_HELP'),
			));
			?>
			<tr class="google-api-field" style="<?php echo $params['googleapikey'] ? '' : 'display:none;'; ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG84'); ?></b><?php echo $help; ?> </td>
				<td>
					<?php echo $vik->radioYesNo('googleapiplaces', $yes, $no); ?>
				</td>
			</tr>

			<!-- DIRECTIONS API - Checkbox -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['googleapidirections']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['googleapidirections']);

			$help = $vik->createPopover(array(
				'title' 	=> JText::_('VRMANAGECONFIG85'),
				'content' 	=> JText::_('VRMANAGECONFIG85_HELP'),
			));
			?>
			<tr class="google-api-field" style="<?php echo $params['googleapikey'] ? '' : 'display:none;'; ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG85'); ?></b><?php echo $help; ?> </td>
				<td>
					<?php echo $vik->radioYesNo('googleapidirections', $yes, $no); ?>
				</td>
			</tr>

			<!-- MAPS STATIC API - Checkbox -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['googleapistaticmap']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['googleapistaticmap']);

			$help = $vik->createPopover(array(
				'title' 	=> JText::_('VRMANAGECONFIG86'),
				'content' 	=> JText::_('VRMANAGECONFIG86_HELP'),
			));
			?>
			<tr class="google-api-field" style="<?php echo $params['googleapikey'] ? '' : 'display:none;'; ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG86'); ?></b><?php echo $help; ?> </td>
				<td>
					<?php echo $vik->radioYesNo('googleapistaticmap', $yes, $no); ?>
				</td>
			</tr>
			
		</table>
	</div>

	<!-- GDPR Fieldset -->
	<div class="config-fieldset">
		<div class="config-fieldset-legend">GDPR</div>
		<table class="admintable" cellspacing="1">
			
			<!-- GDPR - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['gdpr'], 'onclick="jQuery(\'.gdpr-child\').show();"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['gdpr'], 'onclick="jQuery(\'.gdpr-child\').hide();"');

			$help = $vik->createPopover(array(
				'title' 	=> JText::_('VRMANAGECONFIG82'),
				'content' 	=> JText::_('VRMANAGECONFIG82_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG82'); ?></b><?php echo $help; ?> </td>
				<td>
					<?php echo $vik->radioYesNo('gdpr', $yes, $no); ?>
				</td>
			</tr>
			
			<!-- PRIVACY POLICY - text -->
			<tr class="gdpr-child" style="<?php echo ($params['gdpr'] == 0 ? 'display: none;' : ''); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG83"); ?></b> </td>
				<td>
					<div class="multi-field">
						<input type="text" name="policylink" value="<?php echo $params['policylink']; ?>" />

						<!-- translation button -->
						<span class="config-trx" style="<?php echo $params['multilanguage'] ? '' : 'display:none;'; ?>">
							<a href="index.php?option=com_vikrestaurants&amp;view=langconfig&amp;param=policylink" target="_blank">
								<?php
								foreach ($this->translations['policylink'] as $lang)
								{
									echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
								}
								?>
							</a>
						</span>
					</div>
				</td>
			</tr>
			
		</table>
	</div>

	<!-- CLOSING DAYS Fieldset -->
	
	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIG21'); ?></div>
		<table class="admintable" cellspacing="1">
			
			<!-- CLOSING DAYS - Form -->
			<tr>
				<td colspan="2">
					<div class="btn-toolbar">
						<div class="btn-group pull-left vr-toolbar-setfont">
							<?php echo $vik->calendar('', 'vrday', 'vrday'); ?>
						</div>

						<div class="btn-group pull-left">
							<div class="vr-toolbar-setfont">
								<?php
								$elements = array();
								for ($i = 0; $i <= 3; $i++)
								{
									$elements[] = JHtml::_('select.option', $i, 'VRFREQUENCYTYPE' . $i);
								}
								?>
								<select id="vrfrequency" class="medium">
									<?php echo JHtml::_('select.options', $elements, 'value', 'text', null, true); ?>
								</select>
							</div>
						</div>

						<div class="btn-group pull-left">
							<button type="button" class="btn" onClick="addClosingDay();"><?php echo JText::_('VRMANAGECONFIG22'); ?></button>
						</div>
					</div>

					<br clear="all">

					<div id="vrclosingdayscont">
						<?php
						for ($i = 0; $i < count($closing_days); $i++)
						{
							$cd = $closing_days[$i];
							?>
							<div id="vrcdrow<?php echo $i; ?>" style="margin-bottom: 5px;">
								<span>
									<input type="text" style="vertical-align: middle;" value="<?php echo $cd['date']; ?>" readonly />
									<input type="text" style="vertical-align: middle;" value="<?php echo $cd['freqtitle']; ?>" readonly />
									<a href="javascript: void(0);" onClick="removeClosingDay(<?php echo $i; ?>)">
										<i class="fas fa-times big"></i>
									</a>
								</span>
							</div>

							<input id="vrcdhidden<?php echo $i; ?>" name="closing_days[]" type="hidden" value="<?php echo $cd['date'] . ':' . $cd['freq']; ?>" />
							<?php
						}
						?>
					</div>
				</td>
			</tr>
			
		</table>
	</div>

	<?php
	/**
	 * Iterate remaining forms to be displayed within
	 * the "global-right" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($globalRightForms as $formName => $formHtml)
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

<script type="text/javascript">

	jQuery(document).ready(function() {

		// handle opening time mode
		jQuery('#vropentimeselect').on('change', function() {
			if (jQuery(this).val() == 0) {
				jQuery('.opening-cont-field').show();
			} else {
				jQuery('.opening-cont-field').hide();
			}
		});

		// refresh currency symb when updated
		jQuery('input[name="currencysymb"]').on('change', function() {
			jQuery('.currency-field button').text(jQuery(this).val());
		});

		// toggle Google API Key sub-children
		jQuery('input[name="googleapikey"]').on('keyup', function() {
			if (jQuery(this).val().length) {
				jQuery('.google-api-field').show();
			} else {
				jQuery('.google-api-field').hide();
			}
		});

	});

	// enable restaurant / takeaway sections

	function enableTabValueChanged(tab, is) {
		if (is) {
			jQuery('#vretabli' + tab).show();
		} else {
			jQuery('#vretabli' + tab).hide();
		}
	}

	// toggle translation link

	function multilangValueChanged(is) {
		if (is) {
			jQuery('.config-trx').show();
		} else {
			jQuery('.config-trx').hide();
		}
	}

	// enable reviews settings

	function reviewsValueChanged(is) {
		if (is) {
			jQuery('.vrreviewstr').show();
		} else {
			jQuery('.vrreviewstr').hide();
		}
	}

	// closing days

	var _DAYS = [
		'<?php echo addslashes($date->dayToString(0)); ?>',
		'<?php echo addslashes($date->dayToString(1)); ?>',
		'<?php echo addslashes($date->dayToString(2)); ?>',
		'<?php echo addslashes($date->dayToString(3)); ?>',
		'<?php echo addslashes($date->dayToString(4)); ?>',
		'<?php echo addslashes($date->dayToString(5)); ?>',
		'<?php echo addslashes($date->dayToString(6)); ?>',
	];

	var daysCont = <?php echo count($closing_days); ?>;

	// add a closing day
	
	function addClosingDay() {
		var day = jQuery('#vrday').val();

		if (day.length > 0) {
			var f_id = parseInt(jQuery('#vrfrequency').val());
			var f_tx = jQuery('#vrfrequency option:selected').text();
			
			// check for weekly recurrence
			if (f_id == 1) {
				f_tx = _DAYS[getDate(day).getDay()];	
			}
			
			putClosingDay(day, f_id, f_tx);
			
			jQuery('#vrday').val(day);
			
			daysCont++;
		}
	}

	// build the closing day input

	function putClosingDay(day, f_id, f_val) {
		jQuery('#vrclosingdayscont').append(
			'<div id="vrcdrow' + daysCont + '" style="margin-bottom: 5px;">\n'+
				'<span>\n'+
					'<input type="text" style="vertical-align: middle;" value="' + day + '" readonly />\n'+
					'<input type="text" style="vertical-align: middle;" value="' + f_val + '" readonly />\n'+
					'<a href="javascript: void(0);" onClick="removeClosingDay(' + daysCont + ')">\n'+
						'<i class="fas fa-times big"></i>\n'+
					'</a>\n'+
				'</span>\n'+
			'</div>\n'
		);
		
		jQuery('#adminForm').append('<input id="vrcdhidden' + daysCont + '" name="closing_days[]" type="hidden" value="' + day + ':' + f_id + '" />');
	}

	// remove existing closing day
	
	function removeClosingDay(index) {
		jQuery('#vrcdrow' + index).remove();
		jQuery('#vrcdhidden' + index).remove();
	}

	// get date instance from formatted date string
	
	function getDate(day) {
		var df_separator = '<?php echo $params['dateformat'][1]; ?>';
		var formats = '<?php echo $params['dateformat']; ?>'.split(df_separator);
		var date_exp = day.split(df_separator);
		
		var _args = new Array();
		for (var i = 0; i < formats.length; i++) {
			_args[formats[i]] = parseInt(date_exp[i]);
		}
		
		return new Date(_args['Y'], _args['m'] - 1, _args['d']);
	}

</script>
