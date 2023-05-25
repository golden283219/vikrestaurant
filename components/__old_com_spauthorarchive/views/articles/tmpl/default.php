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

<div id="spauthorarchive" class="spauthorarchive spauthorarchive-view-articles layout-<?php echo $this->layout_type; ?>">

    <div class="spauthorarchive-row">
		<div class="spauthorarchive-articles spauthorarchive-col-md-12 spauthorarchive-col-lg-12>">
            <div class="spauthorarchive-author-wrap">
				<?php if(isset($this->author_infos->image) && $this->author_infos->image) { ?>
					<div class="spauthorarchive-author-img-wrap">
						<img src="<?php echo $this->author_infos->image ?>" />
					</div>
				<?php } ?>
				<div class="spauthorarchive-author-content">

                    <?php if ( !empty($this->author_infos->socials) && count($this->author_infos->socials) ) { ?>
						<ul class="spauthorarchive-author-socials">
							<?php foreach ($this->author_infos->socials as  $social) { ?>
							<li class="<?php echo $social['social_name']; ?>">
								<a href="<?php echo $social['social_url']; ?>" target="_blank">
									<i class="fa fa-<?php echo $social['social_name']; ?>"></i>
								</a>
							</li>
							<?php } ?>
						</ul>
                    <?php } ?>
                    
					<h3 class="spauthorarchive-author-title"><?php echo $this->author_infos->name; ?></h3>
					<?php if( isset($this->author_infos->designation) && $designation = $this->author_infos->designation ) { ?>
						<p><?php echo $designation; ?></p>
					<?php } ?>

					<?php if( isset($this->author_infos->description) && $description = $this->author_infos->description ) { ?>
						<p><?php echo $description; ?></p>
					<?php } ?>
				</div> <!-- /.spauthorarchive-author-content -->
			</div> <!-- /.spauthorarchive-author-wrap -->
		</div> <!-- /.spauthorarchive-articles -->
	</div> <!-- /.spauthorarchive-row -->
    

    <div class="spauthorarchive-content">
        <div class="spauthorarchive-row">
            <?php foreach ($this->items as $key => $item) {
            ?>
                <?php echo JLayoutHelper::render('articles.articles', array('item' => $item, 'columns' => $this->columns, 'show_thumbnail' => $this->show_thumbnail, 'show_intro' => $this->show_intro, 'intro_limit' => $this->intro_limit, 'readmore_text' => $this->readmore_text)); ?>
            <?php } // END:: foreach ?>

            <?php //if ($this->pagination->get('pages.total') >1) { ?>
                <div class="pagination">
                    <?php //echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php //} ?>

        </div> <!-- /.spauthorarchive-row -->
    </div> <!-- /.spauthorarchive-content -->

</div>


<?php if ($this->pagination->get('pages.total') >1) { ?>
    <div class="pagination pagination-wrapper">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
