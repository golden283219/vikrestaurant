<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

?>

<ul class="latestnews">
<?php foreach ($list as $item) : ?>
	<li>
		<a href="<?php echo $item->link; ?>">
			<div class="module-content-wrap">
				<h4><?php echo $item->title; ?></h4>
				<span><?php echo HTMLHelper::_('date', $item->created, 'DATE_FORMAT_LC3'); ?></span>
			</div>
		</a>
	</li>
<?php endforeach; ?>
</ul>
