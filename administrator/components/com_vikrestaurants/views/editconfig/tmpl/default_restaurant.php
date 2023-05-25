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

$all_tmpl_files = glob(VREHELPERS . DIRECTORY_SEPARATOR . 'mail_tmpls' . DIRECTORY_SEPARATOR . '*.php');

$tmpl_elements = array();
$tmpl_base64   = array();

foreach ($all_tmpl_files as $file)
{
	$filename = basename($file);

	// encode file path in base64 for being used in URLs
	$tmpl_base64[$filename] = base64_encode(VREHELPERS . DIRECTORY_SEPARATOR . 'mail_tmpls' . DIRECTORY_SEPARATOR . $filename);

	// remove file extension
	$name = preg_replace("/\.php$/i", '', $filename);
	// remove ending "_mail_tmpl"
	$name = preg_replace("/_?e?mail_?tmpl$/i", '', $name);
	// replace dashes and underscores with spaces
	$name = preg_replace("/[-_]+/", ' ', $name);
	// capitalize words
	$name = ucwords(strtolower($name));

	$tmpl_elements[] = JHtml::_('select.option', $filename, $name);
}

/**
 * Trigger event to display custom HTML.
 * In case it is needed to include any additional fields,
 * it is possible to create a plugin and attach it to an event
 * called "onDisplayViewConfigRestaurant". The event method receives the
 * view instance as argument.
 *
 * @since 1.8.3
 */
$restaurantLeftForms  = $this->onDisplayView('Restaurant');
$restaurantRightForms = $this->onDisplayView('RestaurantSecondary');

?>

<!-- LEFT SIDE -->

<div class="config-left-side">

	<!-- RESTAURANT Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIGTITLE1'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- DISPLAY ON DASHBOARD - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['ondashboard']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['ondashboard']);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG36"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('ondashboard', $yes, $no); ?></td>
			</tr>
			
			<!-- MINUTES INTERVALS - Dropdown -->
			<?php
			$elements = array();
			foreach (array(10, 15, 30, 60) as $min)
			{
				$elements[] = JHtml::_('select.option', $min, $min);
			}
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG11"); ?></b> </td>
				<td>
					<select name="minuteintervals" class="small-medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['minuteintervals']); ?>
					</select>
				</td>
			</tr>
			
			<!-- AVERAGE TIME STAY - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG12"); ?></b> </td>
				<td>
					<input type="number" name="averagetimestay" value="<?php echo $params['averagetimestay']; ?>" size="10" min="5" step="5" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?></span>
				</td>
			</tr>
			
			<!-- BOOKING MINUTES RETRICTIONS - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG24"); ?></b> </td>
				<td><input type="number" name="bookrestr" value="<?php echo $params['bookrestr']; ?>" size="10" min="0" step="5" /></td>
			</tr>

			<!-- ASK FOR DEPOSIT - Select -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRCONFIGLOGINREQ1'),
				JHtml::_('select.option', 1, 'VRTKCONFIGOVERLAYOPT2'),
				JHtml::_('select.option', 2, 'VRPEOPLEALLOPT2'),
			);

			$ask = min(array(2, $params['askdeposit']));

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG89'),
				'content' => JText::_('VRMANAGECONFIG89_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG89"); ?></b><?php echo $help; ?> </td>
				<td>
					<select id="askdeposit" class="small-medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $ask, true); ?>
					</select>

					<span class="vr-askdeposit" style="<?php echo ($params['askdeposit'] > 1 ? '' : 'display: none;'); ?>">
						<input type="number" name="askdeposit" value="<?php echo $params['askdeposit']; ?>" min="<?php echo $ask; ?>" max="9999" />
						&nbsp;<?php echo strtolower(JText::_('VRORDERPEOPLE')); ?>
					</span>
				</td>
			</tr>
			
			<!-- RESERVATION DEPOSIT - Number -->
			<tr class="vr-deposit-child" style="<?php echo ($params['askdeposit'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG18"); ?></b> </td>
				<td>
					<div class="input-prepend currency-field">
						<button type="button" class="btn"><?php echo $params['currencysymb']; ?></button>

						<input type="number" id="resdeposit" name="resdeposit" value="<?php echo $params['resdeposit']; ?>" min="0" size="10" step="any" />
					</div>
				</td>
			</tr>
			
			<!-- COST PER PERSON - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['costperperson']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['costperperson']);
			?>
			<tr class="vr-deposit-child" style="<?php echo ($params['askdeposit'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG19"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('costperperson', $yes, $no); ?></td>
			</tr>
			
			<!-- DEFAULT STATUS - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option',   'PENDING',   'VRRESERVATIONSTATUSPENDING'),
				JHtml::_('select.option', 'CONFIRMED', 'VRRESERVATIONSTATUSCONFIRMED'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG35"); ?></b> </td>
				<td>
					<select name="defstatus" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['defstatus'], true); ?>
					</select>
				</td>
			</tr>

			<!-- SELF CONFIRMATION - Checkbox -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['selfconfirm']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['selfconfirm']);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG91'),
				'content' => JText::_('VRMANAGECONFIG91_HELP'),
			));
			?>
			<tr class="vr-defstatus-child" style="<?php echo $params['defstatus'] == 'PENDING' ? '' : 'display:none;'; ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG91"); ?></b><?php echo $help; ?> </td>
				<td><?php echo $vik->radioYesNo('selfconfirm', $yes, $no); ?></td>
			</tr>
			
			<!-- TABLES LOCKED FOR - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG20"); ?></b> </td>
				<td>
					<input type="number" id="tablocktime" name="tablocktime" value="<?php echo $params['tablocktime']; ?>" min="5" step="5" size="10" />
					<span class="right-label">&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?></span>
				</td>
			</tr>
			
			<!-- ENABLE CANCELLATION - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['enablecanc'], 'onClick="jQuery(\'.vrcancelchild\').show();"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['enablecanc'], 'onClick="jQuery(\'.vrcancelchild\').hide();"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG40"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('enablecanc', $yes, $no); ?></td>
			</tr>

			<!-- CANCELLATION REASON - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRCONFIGCANCREASONOPT0'),
				JHtml::_('select.option', 1, 'VRCONFIGCANCREASONOPT1'),
				JHtml::_('select.option', 2, 'VRCONFIGCANCREASONOPT2'),
			);
			?>
			<tr class="vrcancelchild" style="<?php echo ($params['enablecanc'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG68"); ?></b> </td>
				<td>
					<select name="cancreason" class="medium-large">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['cancreason'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- ACCEPT CANCELLATION BEFORE - Number -->
			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG41'),
				'content' => JText::_('VRMANAGECONFIG41_HELP'),
			));
			?>
			<tr class="vrcancelchild" style="<?php echo ($params['enablecanc'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG41"); ?></b><?php echo $help; ?> </td>
				<td>
					<input type="number" name="canctime" value="<?php echo $params['canctime']; ?>" min="0" max="999999" step="1">
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
			<tr class="vrcancelchild" style="<?php echo ($params['enablecanc'] ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG90"); ?></b><?php echo $help; ?> </td>
				<td>
					<input type="number" name="cancmins" value="<?php echo $params['cancmins']; ?>" min="0" max="999999" step="1">
					<span class="right-label">&nbsp;<?php echo JText::_('VRSHORTCUTMINUTE'); ?></span>
				</td>
			</tr>
			
			<!-- LOGIN REQUIREMENTS - Dropdown -->
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
					<select name="loginreq" class="medium" id="vrloginreqsel">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['loginreq'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- ENABLE USER REGISTRATION - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['enablereg']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['enablereg']);
			?>
			<tr id="vrenableregtr" style="<?php echo ($params['loginreq'] > 1 ? '' : 'display: none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG34"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('enablereg', $yes, $no); ?></td>
			</tr>
			
		</table>
	</div>

	<!-- RESERVATION COLUMNS LIST Fieldset -->

	<?php
	$all_list_fields = array(
		'1'  => 'id',
		'2'  => 'sid',
		'20' => 'payment',
		'3'  => 'checkin_ts',
		'4'  => 'people',
		'VRMANAGETABLE4'  => 'rname',
		'5'  => 'tname',
		'18' => 'customer',
		'6'  => 'mail', 
		'16' => 'phone',
		'7'  => 'info',
		'8'  => 'coupon',
		'10' => 'billval',
		'19' => 'rescode',
		'12' => 'status',
	);

	$listable_fields = array();

	if (!empty($params['listablecols']))
	{
		$listable_fields = explode(',', $params['listablecols']);
	}
	?>
	
	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRMANAGECONFIG38'); ?></div>
		<table class="admintable" cellspacing="1">
			<?php
			foreach ($all_list_fields as $k => $f)
			{
				$selected = in_array($f, $listable_fields);

				if (preg_match("/^\d+$/", $k))
				{
					$lk = 'VRMANAGERESERVATION' . $k;
				}
				else
				{
					$lk = $k;
				}
				
				$yes = $vik->initRadioElement('', JText::_('JYES'), $selected, 'onClick="toggleListField(\'' . $f . '\', 1);"');
				$no  = $vik->initRadioElement('', JText::_('JNO'), !$selected, 'onClick="toggleListField(\'' . $f . '\', 0);"');
				?>
				<tr>
					<td width="200" class="adminparamcol"> <b><?php echo JText::_($lk); ?></b> </td>
					<td>
						<?php echo $vik->radioYesNo($f . 'listcol', $yes, $no); ?>
						<input type="hidden" name="listablecols[]" value="<?php echo $f . ':' . $selected; ?>" id="vrhidden<?php echo $f; ?>" />
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
	 * the "restaurant-left" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($restaurantLeftForms as $formName => $formHtml)
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

	<!-- SEARCH Fieldset -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETSEARCH'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- RESERVATION REQUIREMENTS - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', '0', 'VRCONFIGRESREQ0'),
				JHtml::_('select.option', '1', 'VRCONFIGRESREQ1'),
				JHtml::_('select.option', '2', 'VRCONFIGRESREQ2'),
			);
			?> 
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG16"); ?><br>&nbsp; <small>(<?php echo JText::_("VRMANAGECONFIG17"); ?>)</small>:</b> </td>
				<td>
					<select name="reservationreq" class="medium-large">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['reservationreq'], true); ?>
					</select>
				</td>
			</tr>

			<!-- MIN DATE - Select -->
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
				'title'   => JText::_('VRMANAGECONFIG87'),
				'content' => JText::_('VRMANAGECONFIG87_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG87'); ?></b><?php echo $help; ?> </td> 
				<td>
					<select name="mindate">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['mindate']); ?>
					</select>
				</td>
			</tr>

			<!-- MAX DATE - Select -->
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
				'title'   => JText::_('VRMANAGECONFIG88'),
				'content' => JText::_('VRMANAGECONFIG88_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG88'); ?></b><?php echo $help; ?> </td> 
				<td>
					<select name="maxdate">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['maxdate']); ?>
					</select>
				</td>
			</tr>

			<!-- SAFE DISTANCE - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['safedistance'], 'onClick="jQuery(\'.vrsafedistrow\').show();"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['safedistance'], 'onClick="jQuery(\'.vrsafedistrow\').hide();"');
			
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG92'),
				'content' => JText::_('VRMANAGECONFIG92_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG92'); ?></b><?php echo $help; ?> </td>
				<td><?php echo $vik->radioYesNo('safedistance', $yes, $no); ?></td>
			</tr>

			<!-- SAFE FACTOR - Number -->
			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG93'),
				'content' => JText::_('VRMANAGECONFIG93_HELP'),
			));
			?>
			<tr class="vrsafedistrow" style="<?php echo ($params['safedistance'] == "1" ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG93"); ?></b><?php echo $help; ?> </td>
				<td>
					<input type="number" name="safefactor" value="<?php echo $params['safefactor']; ?>" min="1" step="any">
				</td>
			</tr>

			<!-- MINIMUM PEOPLE - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG13"); ?></b> </td>
				<td><input type="number" name="minpeople" value="<?php echo $params['minimumpeople']; ?>" size="10" min="1" step="1" /></td>
			</tr>
			
			<!-- MAXIMUM PEOPLE - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG14"); ?></b> </td>
				<td><input type="number" name="maxpeople" value="<?php echo $params['maximumpeople']; ?>" size="10" min="1" step="1" /></td>
			</tr>
			
			<!-- LARGE PARTY LABEL - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['largepartylbl'], 'onClick="jQuery(\'.vrlargepartyrow\').show();"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['largepartylbl'], 'onClick="jQuery(\'.vrlargepartyrow\').hide();"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG48"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('largepartylbl', $yes, $no); ?></td>
			</tr>
			
			<!-- LARGE PARTY URL - Text -->
			<tr class="vrlargepartyrow" style="<?php echo ($params['largepartylbl'] == "1" ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG49"); ?></b> </td>
				<td>
					<input type="text" name="largepartyurl" value="<?php echo $params['largepartyurl']; ?>" size="36">

					<!-- translation button -->
					<span class="config-trx" style="<?php echo $params['multilanguage'] ? '' : 'display:none;'; ?>">
						<a href="index.php?option=com_vikrestaurants&amp;view=langconfig&amp;param=largepartyurl" target="_blank">
							<?php
							foreach ($this->translations['largepartyurl'] as $lang)
							{
								echo ' ' . JHtml::_('vrehtml.site.flag', $lang) . ' ';
							}
							?>
						</a>
					</span>
				</td>
			</tr>

			<!-- APPLY PERCENTAGE COUPONS - Radio Button -->
			<?php
			$elements = array(
				JHtml::_('select.option', 1, 'VRCONFIGAPPLYCOUPONTYPE1'),
				JHtml::_('select.option', 2, 'VRCONFIGAPPLYCOUPONTYPE2'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG42"); ?></b> </td>
				<td>
					<select name="applycoupon" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['applycoupon'], true); ?>
					</select>
				</td>
			</tr>

		</table>
	</div>

	<!-- FOOD FIELDSET -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETFOOD'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- CHOOSABLE MENUS - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['choosemenu']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['choosemenu']);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG39"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('choosemenu', $yes, $no); ?></td>
			</tr>

			<!-- DISHES ORDERING - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRTKCONFIGOVERLAYOPT0'),
				JHtml::_('select.option', 1, 'VROPTIONATREST'),
				JHtml::_('select.option', 2, 'VRTKCONFIGOVERLAYOPT2'),
			);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG94'),
				'content' => JText::_('VRMANAGECONFIG94_HELP'),
			));
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG94"); ?></b><?php echo $help; ?> </td>
				<td>
					<select name="orderfood" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['orderfood'], true); ?>
					</select>
				</td>
			</tr>

			<!-- EDIT FOOD - Radio Button -->
			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['editfood']);
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['editfood']);

			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG95'),
				'content' => JText::_('VRMANAGECONFIG95_HELP'),
			));
			?>
			<tr class="order-food-child" style="<?php echo $params['orderfood'] ? '' : 'display:none;'; ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG95"); ?></b><?php echo $help; ?> </td>
				<td><?php echo $vik->radioYesNo('editfood', $yes, $no); ?></td>
			</tr>

		</table>
	</div>

	<!-- TAXES FIELDSET -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETTAXES'); ?></div>
		<table class="admintable" cellspacing="1">

			<!-- TAXES RATIO - Number -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK10"); ?></b> </td>
				<td>
					<div class="input-append">
						<input type="number" id="taxesratio" name="taxesratio" value="<?php echo $params['taxesratio']; ?>" min="0" max="99999" size="6" step="any" />
						
						<button type="button" class="btn">%</button>
					</div>
				</td>
			</tr>

			<!-- USE TAXES - Dropdown -->
			<?php
			$elements = array(
				JHtml::_('select.option', 0, 'VRTKCONFIGUSETAXOPT0'),
				JHtml::_('select.option', 1, 'VRTKCONFIGUSETAXOPT1'),
			);
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIGTK24"); ?></b> </td>
				<td>
					<select name="usetaxes" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['usetaxes'], true); ?>
					</select>
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
					<select name="mailcustwhen" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['mailcustwhen'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- SEND TO OPERATORS WHEN - Dropdown -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG45"); ?></b> </td>
				<td>
					<select name="mailoperwhen" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['mailoperwhen'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- SEND TO ADMIN WHEN - Dropdown -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG46"); ?></b> </td>
				<td>
					<select name="mailadminwhen" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['mailadminwhen'], true); ?>
					</select>
				</td>
			</tr>
			
			<!-- EMAIL TEMPLATE -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG47"); ?></b> </td>
				<td>
					<select name="mailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['mailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="restaurantBeforeOpenModal('mailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>
						
						<button type="button" class="btn" onclick="goToMailPreview('restaurant', 'customer', 'mailtmpl');">
							<i class="fas fa-eye"></i>
						</button>
					</div>
				</td>
			</tr>

			<!-- ADMIN EMAIL TEMPLATE -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG56"); ?></b> </td>
				<td>
					<select name="adminmailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['adminmailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="restaurantBeforeOpenModal('adminmailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>

						<button type="button" class="btn" onclick="goToMailPreview('restaurant', 'admin', 'adminmailtmpl');">
							<i class="fas fa-eye"></i>
						</button>
					</div>
				</td>
			</tr>

			<!-- CANCELLATION EMAIL TEMPLATE -->
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG57"); ?></b> </td>
				<td>
					<select name="cancmailtmpl" class="medium">
						<?php echo JHtml::_('select.options', $tmpl_elements, 'value', 'text', $params['cancmailtmpl']); ?>
					</select>
					&nbsp;
					<div class="btn-group">
						<button type="button" class="btn" onclick="restaurantBeforeOpenModal('cancmailtmpl'); return false;">
							<i class="fas fa-pen-square"></i>
						</button>

						<button type="button" class="btn" onclick="goToMailPreview('restaurant', 'cancellation', 'cancmailtmpl');">
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
				if ($field['group'] == 0)
				{
					$all_list_fields[$field['id']] = $field['name'];
				}
			}

			$listable_fields = (array) json_decode($params['listablecf'], true);
			
			foreach ($all_list_fields as $k => $field)
			{
				$selected = in_array($field, $listable_fields);

				$yes = $vik->initRadioElement('', JText::_('JYES'), $selected, 'onClick="toggleListFieldCF(\''.addslashes($field).'\', ' . $k . ', 1);"');
				$no  = $vik->initRadioElement('', JText::_('JNO'), !$selected, 'onClick="toggleListFieldCF(\''.addslashes($field).'\', ' . $k . ', 0);"');
				?>
				<tr>
					<td width="200" class="adminparamcol"> <b><?php echo JText::_($field); ?></b> </td>
					<td>
						<?php echo $vik->radioYesNo('listcf' . $k, $yes, $no); ?>
						<input type="hidden" name="listablecf[]" value="<?php echo $field . ':' . $selected; ?>" id="vrcfhidden<?php echo $k; ?>" />
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
	 * the "restaurant-right" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($restaurantRightForms as $formName => $formHtml)
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

		jQuery('select[name="mindate"],select[name="maxdate"]').select2({
			minimumResultsForSearch: -1,
			placeholder: Joomla.JText._('VRMANAGECONFIG32'),
			allowClear: true,
			width: 150,
		});

		// handle ask deposit

		jQuery('#askdeposit').on('change', function() {
			var value = parseInt(jQuery(this).val());

			jQuery('input[name="askdeposit"]').attr('min', value).val(value);

			if (value > 0) {
				jQuery('.vr-deposit-child').show();
			} else {
				jQuery('.vr-deposit-child').hide();
			}

			if (value > 1) {
				jQuery('.vr-askdeposit').show();
			} else {
				jQuery('.vr-askdeposit').hide();
			}
		});

		// handle self confirmation

		jQuery('select[name="defstatus"]').on('change', function() {
			if (jQuery(this).val() == 'PENDING') {
				jQuery('.vr-defstatus-child').show();
			} else {
				jQuery('.vr-defstatus-child').hide();
			}
		});

		// handle login requirements

		jQuery('#vrloginreqsel').on('change', function() {
			if (parseInt(jQuery(this).val()) > 1) {
				jQuery('#vrenableregtr').show();
			} else {
				jQuery('#vrenableregtr').hide();
			}
		});

		// handle order food

		jQuery('select[name="orderfood"]').on('change', function() {
			if (jQuery(this).val() != 0) {
				jQuery('.order-food-child').show();
			} else {
				jQuery('.order-food-child').hide();
			}
		});

	});

	// toggle reservation columns 

	function toggleListField(id, value) {
		jQuery('#vrhidden' + id).val(id + ':' + value);
	}

	function toggleListFieldCF(cf, id, value) {
		jQuery('#vrcfhidden' + id).val(cf + ':' + value);
	}

	// fetch modal

	var MAIL_TMPL_LOOKUP = <?php echo json_encode((object) $tmpl_base64); ?>;

	function restaurantBeforeOpenModal(id) {
		var url = null;

		SELECTED_MAIL_TMPL_FIELD = jQuery('select[name="' + id + '"]');
		SELECTED_MAIL_TMPL_GROUP = 'restaurant';

		// get file name
		var file = SELECTED_MAIL_TMPL_FIELD.val();
		// make sure the path exists
		var path = MAIL_TMPL_LOOKUP.hasOwnProperty(file) ? MAIL_TMPL_LOOKUP[file] : '';

		switch (id) {
			case 'mailtmpl':
			case 'adminmailtmpl':
			case 'cancmailtmpl':
				url = 'index.php?option=com_vikrestaurants&tmpl=component&task=file.edit&cid[]=' + path;
				id  = 'managetmpl';
				break;
		}

		vrOpenJModal(id, url, true);
	}

</script>
