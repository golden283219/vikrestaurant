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
 * @var  string  $codes  A list of status codes.
 */
extract($displayData);

?>

<div class="vrrescodedialog" style="display: none;">

	<div class="vrrescodeblock selected" data-code="0">
		<div class="vrrescodeblockname">--</div>
	</div>
	
	<?php
	foreach ($codes as $code)
	{
		?>
		<div class="vrrescodeblock" data-code="<?php echo $code->id; ?>">
			<?php
			if (!empty($code->icon))
			{
				?>
				<div class="vrrescodeblockimage">
					<img src="<?php echo VREMEDIA_SMALL_URI . $code->icon; ?>" />
				</div>
				<?php
			}
			?>

			<div class="vrrescodeblockname"><?php echo $code->code; ?></div>
		</div>
		<?php
	}
	?>

</div>
