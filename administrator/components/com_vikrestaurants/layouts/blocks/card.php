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
 * @param 	string 	 id         An optional ID to be set for the card.
 * @param 	string 	 class      An additional class to be used (optional).
 * @param 	string 	 image      Either the source image or the HTML itself.
 * @param 	string 	 badge      The text to display within the badge icon in the summary.
 * 								It is possible to specify plain text or HTML.
 * @param 	string 	 primary    The primary text to be displayed in the summary.
 * @param 	string 	 secondary  The secondary text to be displayed in the summary.
 * @param 	string 	 edit       True to display the EDIT button. In case a string was
 * 								passed, it will be used as callback when the button is clicked.
 */
$id        = isset($displayData['id'])        ? $displayData['id']        : null;
$class     = isset($displayData['class'])     ? $displayData['class']     : null;
$image     = isset($displayData['image'])     ? $displayData['image']     : null;
$badge     = isset($displayData['badge'])     ? $displayData['badge']     : null;
$primary   = isset($displayData['primary'])   ? $displayData['primary']   : '';
$secondary = isset($displayData['secondary']) ? $displayData['secondary'] : '';
$edit      = isset($displayData['edit'])      ? $displayData['edit']      : null;
$editText  = isset($displayData['editText'])  ? $displayData['editText']  : JText::_('VREDIT');

?>
	
<div
	class="vre-card<?php echo $class ? ' ' . $class : ''; ?>"
	<?php echo $id ? 'id="' . $id . '"' : ''; ?>
>

	<div class="vre-card-image" style="<?php echo $image ? '' : 'display:none;'; ?>">
		<?php
		if ($image)
		{
			if (preg_match("/^<img/", $image))
			{
				echo $image;
			}
			else
			{
				?><img src="<?php echo $image; ?>" /><?php
			}
		}
		?>
	</div>

	<div class="vre-card-summary">
		
		<?php
		if ($badge)
		{
			?>
			<div class="card-badge-icon">
				<?php echo $badge; ?>
			</div>
			<?php
		}
		?>

		<div class="card-text">
			<div class="card-text-primary">
				<?php echo $primary ?>
			</div>

			<div class="card-text-secondary">
				<?php echo $secondary ?>
			</div>
		</div>

		<?php
		if ($edit)
		{
			$onclick = is_string($edit) ? 'onclick="' . $edit . '"' : '';

			?>
			<button type="button" class="btn card-edit" <?php echo $onclick; ?>>
				<?php echo $editText; ?>
			</button>
			<?php
		}
		?>

	</div>

</div>
