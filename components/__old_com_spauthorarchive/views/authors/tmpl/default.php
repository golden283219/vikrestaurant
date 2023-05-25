<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

?>
<div id="spauthorarchive" class="spauthorarchive sp-autho-archive-view-authors layout-<?php echo $this->layout_type; ?>">
    <?php echo JLayoutHelper::render('authors.authors', array('authors' => $this->items, 'columns' => $this->columns, 'show_desc' => $this->show_desc)); ?>
</div> <!-- /#spauthorarchive -->
