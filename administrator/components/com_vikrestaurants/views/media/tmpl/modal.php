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

if ($this->firstConfig)
{
	JHtml::_('vrehtml.assets.toast', 'bottom-center', '.vre-media-modal');
	JText::script('VRMEDIAFIRSTCONFIG');
}

$files    = $this->rows;
$selected = $this->selected;
$multiple = $this->multiple;

$vik = VREApplication::getInstance();

?>

<div class="vre-media-modal">

	<div class="vre-media-grid-box">

		<div class="btn-toolbar" style="height: 32px;">

			<div class="btn-group pull-left input-append hide-with-size-320">
				<input type="text" id="mediakeysearch" size="32" value="" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>" autocomplete="off" />

				<button type="button" class="btn" onclick="jQuery('#mediakeysearch').trigger('change');">
					<i class="icon-search"></i>
				</button>
			</div>

			<div class="btn-group pull-left hide-with-size-390">
				<button type="button" class="btn" onclick="jQuery('#mediakeysearch').val('').trigger('change');"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>

		</div>

		<?php
		$attrs = array();
		$attrs['id'] = 'no-media-results-alert';

		if ($files)
		{
			$attrs['style'] = 'display:none;';
		}

		echo $vik->alert(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning', false, $attrs);
		?>

		<div class="vre-media-gallery">

			<?php
			foreach ($files as $media)
			{
				?>
				<div class="vre-media-block"
					data-name="<?php echo $this->escape($media['name']); ?>"
					data-ext="<?php echo $this->escape($media['file_ext']); ?>"
					data-size="<?php echo $this->escape($media['size']); ?>"
					data-date="<?php echo $this->escape($media['creation']); ?>"
					data-width="<?php echo $this->escape($media['width']); ?>"
					data-height="<?php echo $this->escape($media['height']); ?>"
					data-selected="<?php echo in_array($media['name'], $selected) ? 1 : 0; ?>"
				>
					<div class="media-preview">
						<div class="media-thumbnail">
							<div class="media-centered">
								<img src="<?php echo $media['uri']; ?>" />
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>

		</div>
	</div>

	<div class="vre-media-sidebar">
		
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

		<div class="vre-media-inspector">
			
			<div class="inspector-title">
				<span><?php echo JText::_('VRMEDIAFIELDSET1'); ?></span>
				<a href="javascript:void(0);" id="clear-media-selection">
					<i class="fas fa-window-close"></i>
				</a>
			</div>

			<div class="selected-media-pool">

				<div class="media-info-box" data-name="">
					<div class="media-thumbnail media-thumbnail-image">
						<a href="#" target="_blank">
							<img src="" />
						</a>
					</div>

					<div class="media-details">
						<div class="filename"></div>
						<div class="uploaded"></div>
						<div class="file-size"></div>
						<div class="dimensions"></div>

						<a href="javascript:void(0);" class="delete-media-image" onclick="vreDeleteMediaFile(this);"><?php echo JText::_('VRE_DELETE_PERMANENTLY'); ?></a>
					</div>
				</div>

			</div>

		</div>

	</div>

</div>

<?php
JText::script('VRE_DEF_N_SELECTED');
JText::script('VRE_DEF_N_SELECTED_1');
JText::script('VRE_DEF_N_SELECTED_0');
JText::script('VRSYSTEMCONFIRMATIONMSG');
?>

<script type="text/javascript">

	if (!window.parent.hasOwnProperty('VRE_TMP_FILES')) {
		window.parent.VRE_TMP_FILES = [];
	}

	vreShowMediaInspector();

	jQuery(document).ready(function() {

		jQuery('.vre-media-block').on('click', vreHandleMediaClick);

		jQuery('#mediakeysearch').on('change', function() {

			var search = jQuery(this).val().toLowerCase();

			var at_least_one = false;

			jQuery('.vre-media-block').each(function() {

				if (!search.length || vreMatchingMediaBox(this, search)) {
					jQuery(this).show();
					at_least_one = true;
				} else {
					jQuery(this).hide();
				}

			});

			if (at_least_one) {
				jQuery('#no-media-results-alert').hide();
			} else {
				jQuery('#no-media-results-alert').show();
			}

		});

		jQuery('#clear-media-selection').on('click', function() {
			// clear all selected media
			jQuery('.vre-media-block[data-selected="1"]').trigger('click');
		});

	});

	function vreHandleMediaClick() {
		var checked = parseInt(jQuery(this).attr('data-selected')) ? 0 : 1;

		<?php
		if (!$multiple)
		{
			?>
			if (checked) {
				jQuery('.vre-media-block[data-selected="1"]').each(function() {
					jQuery(this).attr('data-selected', 0);
				});
			}

			window.parent.VRE_TMP_FILES = [];
			<?php
		}
		?>

		jQuery(this).attr('data-selected', checked);

		var file  = jQuery(this).data('name');
		var index = window.parent.VRE_TMP_FILES.indexOf(file);

		if (checked && index === -1) {
			// add file to list
			window.parent.VRE_TMP_FILES.push(file);
		}

		if (!checked && index !== -1) {
			// remove file from list
			window.parent.VRE_TMP_FILES.splice(index, 1);
		}

		if (window.parent.VRE_TMP_FILES.length) {
			vreShowMediaInspector();
		} else {
			vreHideMediaInspector();
		}
	}

	function vreMatchingMediaBox(box, search) {
		// search by media name
		var media_name = jQuery(box).data('name').trim().toLowerCase();

		if (media_name.indexOf(search) !== -1) {
			return true;
		}

		return false;
	}

	// inspector

	jQuery(document).ready(function() {
		/**
		 * Assign the transition animations 32 ms after the page loading.
		 * Hack for Safari, which seems to apply the transition also
		 * for the initial state of the inspector, causing an unwanted 
		 * animation when the page loads.
		 */
		setTimeout(function() {
			jQuery('.vre-media-inspector').css('transition', '0.4s ease-out all');
			jQuery('.vre-media-inspector').css('-moz-transition', '0.4s ease-out all');
			jQuery('.vre-media-inspector').css('-webkit-transition', '0.4s ease-out all');
		}, 32);
	});

	function vreShowMediaInspector() {
		var inspector = jQuery('.vre-media-inspector');
		if (!inspector.hasClass('slide-in')) {
			jQuery('.vre-media-inspector').addClass('slide-in');
		}

		// directly use the list of temporary file
		var list = window.parent.VRE_TMP_FILES;

		// filter the list and remove all the files that doesn't exist
		list = list.filter(function(file) {
			return jQuery('.vre-media-block[data-name="' + file + '"').length ? true : false
		});

		// update reference as certain images might have been deleted
		window.parent.VRE_TMP_FILES = list;

		if (list.length == 0) {
			// hide media inspector because, even if the selection pool owned some files,
			// all them didn't exist and were removed to avoid unexpected behaviors
			vreHideMediaInspector();
		}

		<?php
		if ($multiple)
		{
			?>
			// remove all except for first media box
			jQuery('.vre-media-inspector .media-info-box:not(:first-child)').remove();

			for (var i = 0; i < list.length; i++) {
				if (i > 0) {
					var clone = jQuery('.vre-media-inspector .media-info-box').last().clone();
					jQuery('.selected-media-pool').append(clone);
				}

				vreFillInspectorMediaInfo(list[i]);
			}
			<?php
		}
		else
		{
			?>
			vreFillInspectorMediaInfo(list[0]);
			<?php
		}
		?>

		jQuery('.vre-media-inspector .media-info-box').show();
	}

	function vreFillInspectorMediaInfo(filename) {
		var media = jQuery('.vre-media-block[data-name="' + filename + '"]');

		var imgUrl = media.find('img').attr('src');

		var box = jQuery('.vre-media-inspector .media-info-box').last();

		box.attr('data-name', media.data('name'))
			.find('.media-thumbnail-image a')
				.attr('href', imgUrl)
				.find('img')
					.attr('src', imgUrl);

		box.find('.media-details .filename').text(media.data('name'));
		box.find('.media-details .uploaded').text(media.data('date'));
		box.find('.media-details .file-size').text(media.data('size'));
		box.find('.media-details .dimensions').text(media.data('width') + 'x' + media.data('height') + ' pixel');
	}

	function vreHideMediaInspector() {
		jQuery('.vre-media-inspector .media-info-box').hide();
		jQuery('.vre-media-inspector').removeClass('slide-in');
	}

	function vreDeleteMediaFile(link) {
		// double confirm
		var r = confirm(Joomla.JText._('VRSYSTEMCONFIRMATIONMSG'));

		if (!r) {
			return false;
		}

		// find media name
		var media = jQuery(link).closest('.media-info-box').attr('data-name');

		// delete media
		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=media.delete&ajax=1',
			{
				cid:  [media],
				path: '<?php echo base64_encode($this->path); ?>',
			}
		);

		// toggle media selection and then remove it
		jQuery('.vre-media-block[data-name="' + media + '"]').trigger('click').remove();
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

			jQuery('input#legacy-upload').trigger('click');

		});

		jQuery('input#legacy-upload').on('change', function() {
			
			vreDispatchMediaUploads(jQuery(this)[0].files);

		});

	});
	
	// upload
	
	function vreDispatchMediaUploads(files) {
		for (var i = 0; i < files.length; i++) {
			if (files[i].name.match(/\.(png|jpe?g|gif|bmp)$/)) {
				var status = new UploadingMediaFile();
				status.setFileName(files[i].name);
				status.setProgress(0);
				
				jQuery('.vre-media-gallery').prepend(status.getHtml());
				
				vreMediaFileUploadThread(status, files[i]);
			} else {
				alert('File ' + files[i].name + ' not supported');
			}
		}
	}
	
	function UploadingMediaFile() {
		// create parent
		this.fileBlock = jQuery('<div class="vre-media-block" data-name="" data-selected="0"></div>');

		// create media thumbnail
		this.fileThumb = jQuery('<div class="media-preview"><div class="media-thumbnail"><i class="fas fa-file"></i></div></div>').appendTo(this.fileBlock);
	 
		this.setFileName = function(name) {
			this.fileBlock.attr('data-name', name);
		}
		
		this.setProgress = function(progress) {       
			var opacity = parseFloat(progress / 100);

			this.fileThumb.find('i').css('opacity', opacity);
		}
		
		this.complete = function(file) {
			this.setProgress(100);

			this.fileBlock.attr('data-name', file.name);
			this.fileBlock.attr('data-ext', file.file_ext);
			this.fileBlock.attr('data-size', file.size);
			this.fileBlock.attr('data-date', file.creation);
			this.fileBlock.attr('data-width', file.width);
			this.fileBlock.attr('data-height', file.height);

			this.fileThumb.find('.media-thumbnail').html('<div class="media-centered"><img src="' + file.uri + '" /></div>');
		}
		
		this.getHtml = function() {
			return this.fileBlock;
		}
	}
	
	function vreMediaFileUploadThread(status, file) {
		jQuery.noConflict();
		
		var formData = new FormData();
		formData.append('image', file);
		formData.append('path', '<?php echo base64_encode($this->path); ?>');

		UIAjax.upload(
			// end-point URL
			'index.php?option=com_vikrestaurants&task=media.dropupload',
			// file post data
			formData,
			// success callback
			function(resp) {
				try {
					// try parsing JSON string
					resp = JSON.parse(resp);
				} catch (err) {
					// something went wrong with JSON decoding, log and abort callback
					console.error(err, resp);

					// remove file from gallery
					jQuery('.vre-media-block[data-name="' + file.name + '"]').remove();
					return;
				}

				// finalize upload
				status.complete(resp);

				// register click event and trigger it for media auto-selection
				jQuery(status.fileBlock).on('click', vreHandleMediaClick).trigger('click');
			},
			// failure callback
			function(error) {
				// raise alert
				alert(error.responseText);

				// remove file from gallery
				jQuery('.vre-media-block[data-name="' + file.name + '"]').remove();
			},
			// progress callback
			function(progress) {
				// update progress
				status.setProgress(progress);
			}
		);
	}

	<?php
	/**
	 * Display a toast message to inform the administrator
	 * that it is recommended to set up the media configuration
	 * before uploading the images for the first time.
	 *
	 * @since 1.8.2
	 */
	if ($this->firstConfig)
	{
		?>
		jQuery(document).ready(function() {
			// display toast message with a delay of 256 ms
			setTimeout(function() {
				ToastMessage.dispatch({
					text: Joomla.JText._('VRMEDIAFIRSTCONFIG'),
					status: 2,
					delay: 15000,
					action: function() {
						// open new media view in a blank page
						window.open('index.php?option=com_vikrestaurants&task=media.add&configure=1', '_blank');
					},
				});
			}, 256);
		});
		<?php
	}
	?>

</script>
