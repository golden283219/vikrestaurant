var VRE_SELECTED_FIELD = null;
var VRE_TMP_FILES      = [];
var VRE_MEDIA_FOLDER   = '';

jQuery(document).ready(function() {
	jQuery('#media-manager-save').on('click', vreMediaDismissHandler);
});

function vreMediaDismissHandler() {
	// get previous value
	var oldVal = jQuery(VRE_SELECTED_FIELD).mediamanager('val');

	// update media field
	jQuery(VRE_SELECTED_FIELD).mediamanager('val', VRE_TMP_FILES);

	// get new value
	var newVal = jQuery(VRE_SELECTED_FIELD).mediamanager('val');

	// trigger change event if the value changed
	if (JSON.stringify(oldVal) != JSON.stringify(newVal)) {
		jQuery(VRE_SELECTED_FIELD).trigger('change');
	}

	// dispose modal
	vreMediaCloseJModal();

	// unset selected field
	VRE_SELECTED_FIELD = null;
	// empty selected files list
	VRE_TMP_FILES = [];
}

function vreMediaStartPreview(id, path) {
	// get selected images
	var images = jQuery(id).mediamanager('val');

	if (!images || images.length == 0) {
		return false;
	}

	if (!path) {
		path = VRE_MEDIA_FOLDER;
	}

	if (Array.isArray(images)) {
		images = images.map(function(image) {
			return path + image;
		});
	} else {
		images = path + images;
	}

	// start preview
	vreOpenModalImage(images);
}

jQuery.fn.mediamanager = function(method, data) {
	if (typeof method === 'undefined') {
		method = 'open';
	}

	if (method.match(/^(val|value)$/i)) {
		// check if the field supports multiple selection
		var multiple = jQuery(this).attr('multiple') ? true : false;
		// extract field name
		var name = jQuery(this).data('name');
		// set value if 2nd argument is defined
		if (typeof data !== 'undefined') {
			// check if multiple
			if (multiple) {
				jQuery(this).siblings('input[type="hidden"][name="' + name + '"]').remove();

				// no selection by default
				var text = '';

				if (data && data.length) {
					if (!Array.isArray(data)) {
						data = [data];
					}

					for (var i = 0; i < data.length; i++) {
						jQuery('<input type="hidden" name="' + name + '" value="' + data[i] + '" />').insertBefore(this);
					}

					if (data.length == 1) {
						// text = Joomla.JText._('VRE_DEF_N_SELECTED_1');
						text = data[0];
					} else {
						text = Joomla.JText._('VRE_DEF_N_SELECTED').replace(/%d/, data.length);
					}
				}

				jQuery(this).val(text);
			} else {
				if (Array.isArray(data)) {
					data = data.length ? data.shift() : null;
				}

				jQuery(this).val(data);
			}

			return this;
		}

		// retrieve field value
		if (multiple) {
			var tmp = [];
			jQuery(this).siblings('input[type="hidden"][name="' + name + '"]').each(function() {
				tmp.push(jQuery(this).val());
			});
			return tmp;
		}

		return jQuery(this).val();
	}
	// check if we should open the dialog
	else if (method.match(/^(open|show)$/i)) {
		vreMediaOpenJModal(this, data ? data : null);
	}
	// check if we should dismiss the dialog
	else if (method.match(/^(close|dismiss|hide)$/i)) {
		vreMediaCloseJModal();
	}
	// check if we should display the preview
	else if (method.match(/^preview$/i)) {
		vreMediaStartPreview(this, data ? data : null);
	}

	return this;
}
