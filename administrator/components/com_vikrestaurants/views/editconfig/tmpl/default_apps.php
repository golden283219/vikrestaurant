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

/**
 * Trigger event to display custom HTML.
 * In case it is needed to include any additional fields,
 * it is possible to create a plugin and attach it to an event
 * called "onDisplayViewConfigApi". The event method receives the
 * view instance as argument.
 *
 * @since 1.8.3
 */
$apiLeftForms  = $this->onDisplayView('Api');
$apiRightForms = $this->onDisplayView('ApiSecondary');

?>

<!-- LEFT SIDE -->

<div class="config-left-side">

	<!-- APIs -->

	<div class="config-fieldset">
		<div class="config-fieldset-legend"><?php echo JText::_('VRCONFIGFIELDSETAPIFR'); ?></div>
		<table class="admintable" cellspacing="1">
			
			<!-- API FRAMEWORK -->

			<?php
			$yes = $vik->initRadioElement('', JText::_('JYES'), $params['apifw'], 'onClick="toggleApiFrameworkFields(1);"');
			$no  = $vik->initRadioElement('', JText::_('JNO'), !$params['apifw'], 'onClick="toggleApiFrameworkFields(0);"');
			?>
			<tr>
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG69"); ?></b> </td>
				<td><?php echo $vik->radioYesNo('apifw', $yes, $no); ?></td>
			</tr>

			<!-- API MAX FAILURE ATTEMPTS -->

			<?php
			$help = $vik->createPopover(array(
				'title'   => JText::_('VRMANAGECONFIG74'),
				'content' => JText::_('VRMANAGECONFIG75'),
			));
			?>
			<tr class="vr-apifw-field" style="<?php echo ($params['apifw'] ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_('VRMANAGECONFIG74'); ?></b><?php echo $help; ?> </td>
				<td>
					<input type="number" name="apimaxfail" value="<?php echo $params['apimaxfail']; ?>" min="1" step="1" />
				</td>
			</tr>

			<!-- API LOG MODE -->

			<?php
			$elements = array();
			for ($i = 0; $i < 3; $i++)
			{
				$elements[] = JHtml::_('select.option', $i, 'VRCONFIGAPIREGLOGOPT' . $i);
			}
			?>
			<tr class="vr-apifw-field" style="<?php echo ($params['apifw'] ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG72"); ?></b> </td>
				<td>
					<select name="apilogmode" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['apilogmode'], true); ?>
					</select>
				</td>
			</tr>

			<!-- API LOG MODE -->

			<?php
			$elements = array();
			$elements[] = JHtml::_('select.option',  1, 'VRCONFIGAPIFLUSHLOGOPT1');
			$elements[] = JHtml::_('select.option',  7, 'VRCONFIGAPIFLUSHLOGOPT2');
			$elements[] = JHtml::_('select.option', 30, 'VRCONFIGAPIFLUSHLOGOPT3');
			$elements[] = JHtml::_('select.option',  0, 'VRCONFIGAPIFLUSHLOGOPT0');
			?>

			<tr class="vr-apifw-field" style="<?php echo ($params['apifw'] ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG73"); ?></b> </td>
				<td>
					<select name="apilogflush" class="medium">
						<?php echo JHtml::_('select.options', $elements, 'value', 'text', $params['apilogflush'], true); ?>
					</select>
				</td>
			</tr>

			<!-- SEE USERS LIST -->

			<tr class="vr-apifw-field" style="<?php echo ($params['apifw'] ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG70"); ?></b> </td>
				<td>
					<a href="index.php?option=com_vikrestaurants&view=apiusers" class="btn"><?php echo JText::_("VRMANAGECONFIG71"); ?></a>
				</td>
			</tr>

			<!-- SEE USERS LIST -->

			<tr class="vr-apifw-field" style="<?php echo ($params['apifw'] ? '' : 'display:none;'); ?>">
				<td width="200" class="adminparamcol"> <b><?php echo JText::_("VRMANAGECONFIG76"); ?></b> </td>
				<td>
					<a href="index.php?option=com_vikrestaurants&view=apiplugins" class="btn"><?php echo JText::_("VRMANAGECONFIG77"); ?></a>
				</td>
			</tr>
		
		</table>
	</div>

	<?php
	/**
	 * Iterate remaining forms to be displayed within
	 * the "api-left" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($apiLeftForms as $formName => $formHtml)
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

	<?php
	/**
	 * Iterate remaining forms to be displayed within
	 * the "api-right" menu item as different fieldsets.
	 *
	 * @since 1.8.3
	 */
	foreach ($apiRightForms as $formName => $formHtml)
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

	// toggle api fields

	function toggleApiFrameworkFields(is) {
		if (is) {
			jQuery('.vr-apifw-field').show();
		} else {
			jQuery('.vr-apifw-field').hide();
		}
	}

</script>
