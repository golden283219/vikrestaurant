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

if ($this->showHelp)
{
	JHtml::_('vrehtml.assets.toast', 'bottom-right');
	JText::script('VRMEDIAFIRSTCONFIG2');
}

$properties = $this->properties;

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" enctype="multipart/form-data" id="adminForm">

	<?php echo $vik->openCard(); ?>

		<div class="span12">

			<div class="row-fluid">

				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMEDIAFIELDSET4')); ?>
						<div class="vre-media-droptarget">
							<p class="icon">
								<i class="fas fa-upload"></i>
							</p>

							<div class="lead">
								<a href="javascript: void(0);" id="upload-file"><?php echo JText::_('VRE_MANUAL_UPLOAD'); ?></a>&nbsp;<?php echo JText::_('VRE_MEDIA_DRAG_DROP'); ?>
							</div>

							<p class="maxsize">
								<?php
								echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', 
									JHtml::_('number.bytes', ini_get('upload_max_filesize'), 'auto', 0)
								);
								?>
							</p>

							<input type="file" id="legacy-upload" multiple style="display: none;" />
						</div>
					<?php echo $vik->closeFieldset(); ?>
				</div>
				
				<div class="span6">
					<?php echo $vik->openFieldset(JText::_('VRMEDIAFIELDSET1')); ?>

						<!-- RESIZE - Radio Button -->
						<?php
						$elem_yes = $vik->initRadioElement(1, JText::_('VRYES'), $properties['resize'], 'onClick="resizeValueChanged(1);"');
						$elem_no  = $vik->initRadioElement(0, JText::_('VRNO'), !$properties['resize'], 'onClick="resizeValueChanged(0);"');
						
						echo $vik->openControl(JText::_('VRMANAGEMEDIA6'));
						echo $vik->radioYesNo('resize', $elem_yes, $elem_no, false);
						echo $vik->closeControl();
						?>

						<!-- RESIZE WIDTH - Number -->
						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA7')); ?>
							<div class="input-append">
								<input type="number" name="resize_value" value="<?php echo $properties['resize_value']; ?>" min="16" step="1" id="vr-resize-field" <?php echo ($properties['resize'] ? '' : 'readonly="readonly"'); ?> />
								<button type="button" class="btn">px</button>
							</div>
						<?php echo $vik->closeControl(); ?>

						<!-- THUMBNAIL WIDTH - Number -->
						<?php echo $vik->openControl(JText::_('VRMANAGEMEDIA8')); ?>
							<div class="input-append">
								<input type="number" name="thumb_value" value="<?php echo $properties['thumb_value']; ?>" min="16" step="1" id="vr-thumb-field" />
								<button type="button" class="btn">px</button>
							</div>
						<?php echo $vik->closeControl(); ?>
					
					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

			<div class="row-fluid">

				<div class="span12" style="display: none;" id="vr-uploads">
					<?php echo $vik->openFieldset(JText::_('VRMEDIAFIELDSET5')); ?>
						<div id="vr-uploads-cont"></div>
					<?php echo $vik->closeFieldset(); ?>
				</div>

			</div>

		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<script type="text/javascript">

	function resizeValueChanged(s) {
		jQuery('#vr-resize-field').prop('readonly', (s ? false : true));
	}

	// files upload

	jQuery(document).ready(function() {

		var dragCounter = 0;

		// drag&drop actions on target div

		jQuery('.vre-media-droptarget').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
		});

		jQuery('.vre-media-droptarget').on('dragenter', function(e) {
			// increase the drag counter because we may
			// enter into a child element
			dragCounter++;

			jQuery(this).addClass('drag-enter');
		});

		jQuery('.vre-media-droptarget').on('dragleave', function(e) {
			// decrease the drag counter to check if we 
			// left the main container
			dragCounter--;

			if (dragCounter <= 0) {
				jQuery(this).removeClass('drag-enter');
			}
		});

		jQuery('.vre-media-droptarget').on('drop', function(e) {

			jQuery(this).removeClass('drag-enter');
			
			var files = e.originalEvent.dataTransfer.files;
			
			vreDispatchMediaUploads(files);
			
		});

		jQuery('.vre-media-droptarget #upload-file').on('click', function() {
			// unset selected files before showing the dialog
			jQuery('input#legacy-upload').val(null).trigger('click');
		});

		jQuery('input#legacy-upload').on('change', function() {
			// execute AJAX uploads after selecting the files
			vreDispatchMediaUploads(jQuery(this)[0].files);
		});

	});
	
	// upload
	
	function vreDispatchMediaUploads(files) {
		var up_cont = jQuery('#vr-uploads-cont');

		for (var i = 0; i < files.length; i++) {
			if (files[i].name.match(/\.(png|jpe?g|gif|bmp)$/)) {
				// show "uploads" section only in case there is
				// at least a supported file
				jQuery('#vr-uploads').show();

				var status = new createStatusBar();
				status.setFileNameSize(files[i].name, files[i].size);
				status.setProgress(0);
				up_cont.append(status.getHtml());
				
				vreMediaFileUploadThread(status, files[i]);
			} else {
				alert('File [' + files[i].name + '] not supported');
			}
		}
	}
	
	var fileCount = 0;
	function createStatusBar() {
		fileCount++;
		this.statusbar = jQuery("<div class='vr-progressbar-status'></div>");
		this.filename = jQuery("<div class='vr-progressbar-filename'></div>").appendTo(this.statusbar);
		this.size = jQuery("<div class='vr-progressbar-filesize hidden-phone'></div>").appendTo(this.statusbar);
		this.progressBar = jQuery("<div class='vr-progressbar'><div></div></div>").appendTo(this.statusbar);
		this.abort = jQuery("<div class='vr-progressbar-abort hidden-phone'>Abort</div>").appendTo(this.statusbar);
		this.statusinfo = jQuery("<div class='vr-progressbar-info hidden-phone' style='display:none;'><?php echo addslashes(JText::_('VRMANAGEMEDIA9')); ?></div>").appendTo(this.statusbar);
		this.completed = false;
	 
		this.setFileNameSize = function(name, size) {
			var sizeStr = "";
			if(size > 1024*1024) {
				var sizeMB = size/(1024*1024);
				sizeStr = sizeMB.toFixed(2)+" MB";
			} else if(size > 1024) {
				var sizeKB = size/1024;
				sizeStr = sizeKB.toFixed(2)+" kB";
			} else {
				sizeStr = size.toFixed(2)+" B";
			}
	 
			this.filename.html(name);
			this.size.html(sizeStr);
		}
		
		this.setProgress = function(progress) {       
			var progressBarWidth = progress*this.progressBar.width()/100;  
			this.progressBar.find('div').css('width', progressBarWidth+'px').html(progress + "% ");
			if(parseInt(progress) >= 100) {
				if( !this.completed ) {
					this.abort.hide();
					this.statusinfo.show();
				}
			}
		}
		
		this.complete = function() {
			this.completed = true;
			this.abort.hide();
			this.statusinfo.hide();
			this.setProgress(100);
			this.progressBar.find('div').addClass('completed');
		}
		
		this.setAbort = function(jqxhr) {
			var bar = this.progressBar;
			this.abort.click(function() {
				jqxhr.abort();
				this.hide();
				bar.find('div').addClass('aborted');
			});
		}
		
		this.getHtml = function() {
			return this.statusbar;
		}
	}

	function vreMediaFileUploadThread(status, file) {
		jQuery.noConflict();
		
		var formData = new FormData();
		formData.append('image', file);
		formData.append('resize', jQuery('input[name="resize"]:checked').val());
		formData.append('resize_value', jQuery('input[name="resize_value"]').val());
		formData.append('thumb_value', jQuery('input[name="thumb_value"]').val());

		var xhr = UIAjax.upload(
			// end-point URL
			'index.php?option=com_vikrestaurants&task=media.dropupload',
			// file post data
			formData,
			// success callback
			function(resp) {
				try {
					resp = jQuery.parseJSON(resp);
				} catch (err) {
					console.warn(err, resp);
					resp = false;
				}
				
				if (resp) {
					status.complete();
					status.filename.html(resp.name);
				} else {
					status.progressBar.find('div').addClass('aborted');
				}
			},
			// failure callback
			function(error) {
				status.progressBar.find('div').addClass('aborted');
			},
			// progress callback
			function(progress) {
				// update progress
				status.setProgress(progress);
			}
		);

		status.setAbort(xhr);
	}

	<?php
	/**
	 * Display a toast message to guide the user about
	 * the steps needed to change the default size
	 * that will be used to create the thumbnails.
	 *
	 * @since 1.8.2
	 */
	if ($this->showHelp)
	{
		?>
		jQuery(document).ready(function() {
			// display toast message with a delay of 256 ms
			setTimeout(function() {
				ToastMessage.dispatch({
					text: Joomla.JText._('VRMEDIAFIRSTCONFIG2'),
					status: 3,
					delay: 10000,
				});
			}, 256);
		});
		<?php
	}
	?>

</script>
