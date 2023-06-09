<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('core');

$title = $displayData['title'];

?>
<button type="button" data-bs-toggle="modal" onclick="{document.getElementById('collapseModal').open(); return true;}" class="btn btn-primary">
    <span class="icon-square" aria-hidden="true"></span>
    <?php echo $title; ?>
</button>
