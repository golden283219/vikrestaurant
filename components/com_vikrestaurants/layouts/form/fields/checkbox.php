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

JHtml::_('vrehtml.assets.fancybox');

$label = $displayData['label'];
$value = $displayData['value'];
$cf    = $displayData['field'];

$isreq = $cf['required'] == 1 ? "<span class=\"vrrequired\"><sup>*</sup></span> " : '';
				
if (!empty($cf['poplink']))
{
	if (preg_match("/^index.php/i", $cf['poplink']))
	{
		// route link to be used externally
		$cf['poplink'] = VREApplication::getInstance()->routeForExternalUse($cf['poplink']);
	}

	$label = "<a href=\"javascript: void(0);\" onclick=\"vreOpenPopup('" . $cf['poplink'] . "');\" id=\"vrcf" . $cf['id'] . "\">" . $isreq . $label . "</a>";
}
else
{
	$label = "<span id=\"vrcf" . $cf['id'] . "\">" . $isreq . $label . "</span>";
}

?>

<div class="vr-cf-checkbox-wrap">

	<span class="cf-label">&nbsp;</span>

	<span class="cf-value">

		<input
			type="checkbox"
			id="vrcfinput<?php echo $cf['id']; ?>"
			name="vrcf<?php echo $cf['id']; ?>"
			value="1"
			class="<?php echo $cf['required'] ? 'required' : ''; ?>"
			<?php echo ($value == 1 ? 'checked="checked"' : ''); ?>
		/>
		
		<label for="vrcfinput<?php echo $cf['id']; ?>"><?php echo $label; ?></label>

	</span>

</div>
