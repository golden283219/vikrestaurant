/**
 * UIFormFieldMedia class.
 * This field is used to display a media manager for the
 * selection of one or more images.
 */
class UIFormFieldMedia extends UIFormFieldList {
	
	/**
	 * Class constructor.
	 *
	 * @param 	object 	data  The field attributes.
	 */
	constructor(data) {
		super(data);

		// use full width for media fields
		this.data.controlClass = 'full';
	}

	/**
	 * @override
	 * Method used to obtain the input HTML.
	 *
	 * @return 	string 	The input html.
	 */
	getInput() {
		// keep style for media box
		var style = this.data.style;
		// overwrite style for dropdown
		this.data.style = 'display: none;';

		// get input select from parent
		var input = super.getInput();

		// setup style attribute
		style = style ? ' style="' + style + '"' : '';

		this.mediaId = this.data.id + '-media';

		// append media box to input
		input += '<div class="ui-media-field" id="' + this.mediaId + '">';

		// add search bar
		if (this.data.searchbar) {
			// define search id
			this.mediaSearchId = this.mediaId + '-search';

			input += '<div class="ui-search-bar">';
			input += '<i class="fas fa-search"></i>';
			/**
			 * @translate
			 */
			input += '<input type="text" id="' + this.mediaSearchId + '" placeholder="' + UILocale.getInstance().get('Search') + '" value="' + UIFormFieldMedia.search + '">';
			input += '</div>';
		}

		// display no records found box
		/**
		 * @translate
		 */
		input += '<div class="no-record-found" style="' + (Object.keys(this.data.options).length ? 'display:none;' : '') + '">';
		input += UILocale.getInstance().get('No media found');
		input += '</div>';
		
		// iterate all medias
		for (var k in this.data.options) {
			if (!this.data.options.hasOwnProperty(k)) {
				continue;
			}

			// check if selected
			var selected = ((this.data.multiple && this.data.value.indexOf(k) !== -1) || k == this.data.value);

			var label = this.data.options[k];

			// display at most 24 characters
			if (label.length > 24) {
				label = label.substr(0, 18) + '...';
			}

			input += '<div class="ui-media-file' + (selected ? ' selected' : '') + '">';
			input += '<img src="' + k + '" title="' + this.data.options[k] + '" />';
			input += '<span data-name="' + this.data.options[k] + '">' + label + '</span>';
			input += '</div>';
		}

		// close box
		input += '</div>';

		if (this.data.upload) {
			 // define search id
			this.mediaUploadId = this.mediaId + '-upload';

			input += '<button type="button" class="ui-button full" id="' + this.mediaUploadId + '">';
			/**
			 * @translate
			 */
			input += '<i class="fas fa-upload"></i> ' + UILocale.getInstance().get('Upload Media');
			input += '</button>';
		}

		return input;
	}

	/**
	 * @override
	 * Method used to initialise the field.
	 * This method is called once the field has been
	 * added within the document.
	 *
	 * @param 	UIFormWrapper  inspector
	 *
	 * @return 	void
	 */
	onInit(inspector) {

		var _this = this;

		this.initMediaBlocks();

		// setup search bar
		if (this.data.searchbar) {
			// register focus to avoid using canvas shortcuts
			jQuery('#' + this.mediaSearchId).on('focus', function() {
				inspector.grabFocus();
			});

			// unregister focus to restart using canvas shortcuts
			jQuery('#' + this.mediaSearchId).on('blur', function() {
				inspector.loseFocus();
			});

			// perform search on keyup
			jQuery('#' + this.mediaSearchId).on('keyup', function() {
				var search = jQuery(this).val().trim().toLowerCase();

				// cache searched string
				UIFormFieldMedia.search = search;

				if (!search.length) {
					// show all media
					jQuery('#' + _this.mediaId).find('.ui-media-file').show();
					jQuery('#' + _this.mediaId).find('.no-record-found').hide();
					return true;
				}

				var at_least_one = false;

				// iterate all media
				jQuery('#' + _this.mediaId).find('.ui-media-file').each(function() {
					// get media name
					var mediaName = jQuery(this).find('span').attr('data-name').toLowerCase();

					if (mediaName.indexOf(search) !== -1) {
						jQuery(this).show();
						at_least_one = true;
					} else {
						jQuery(this).hide();
					}
				});

				if (at_least_one) {
					jQuery('#' + _this.mediaId).find('.no-record-found').hide();
				} else {
					jQuery('#' + _this.mediaId).find('.no-record-found').show();
				}

			});

			// in case of cached search, trigger keyup to
			// hide all the images that don't match the value
			if (UIFormFieldMedia.search.length) {
				jQuery('#' + this.mediaSearchId).trigger('keyup');
			}
		}

		// setup upload button
		if (this.data.upload) {
			// open file dialog after clicking the upload button
			jQuery('#' + this.mediaUploadId).on('click', function() {
				// trigger file dialog
				UIFileDialog.open();
			});

			// subscribe field and callback to update the images
			// every time a new media is uploaded
			UIFileDialog.subscribe(this.data.id, function(value) {
				// make sure the field still exists
				if (_this && jQuery('#' + _this.mediaId).length) {
					// push image within the list
					_this.addImage(value);
				}
			});
		}

	}

	/**
	 * Helper method used to init media blocks events.
	 *
	 * @return 	void
	 */
	initMediaBlocks() {
		var _this = this;

		jQuery('#' + this.mediaId).find('.ui-media-file').on('click', function(event) {
			// get value
			var value = jQuery(this).find('img').attr('src');

			if (!_this.data.multiple) {

				// deselect all images
				jQuery('#' + _this.mediaId).find('.ui-media-file').removeClass('selected');

				// select image
				jQuery(this).addClass('selected');

				// update field and trigger change
				_this.setValue(value).trigger('change');

			} else {

				// get current values
				var current = _this.getValue();
				var indexOf;

				if (!current) {
					current = [];
				}

				// make sure the value is not yet selected
				if ((indexOf = current.indexOf(value)) === -1) {
					// select image
					jQuery(this).addClass('selected');
					// push the value
					current.push(value);
				} else {
					// deselect image
					jQuery(this).removeClass('selected');
					// remove the value
					current.splice(indexOf, 1);
				}
				
				// update field and trigger change
				_this.setValue(current).trigger('change');

			}

		});

		// cannot invoke parent method, dispatch the init
		// method of the field manually (if any)
		if (this.data.onInit) {
			this.data.onInit(inspector);
		}
	}

	/**
	 * @override
	 * Method used to destroy the field.
	 * This method is called before removing the field
	 * from the inspector.
	 *
	 * @param 	UIFormWrapper  inspector
	 *
	 * @return 	void
	 */
	onDestroy(inspector) {
		// cannot invoke parent method, dispatch the destroy
		// method of the field manually (if any)
		if (this.data.onDestroy) {
			this.data.onDestroy(inspector);
		}

		// unsubscribe media field from file dialog updates
		UIFileDialog.unsubscribe(this.data.id);
	}

	/**
	 * @override
	 * Method invoked when the field is shown.
	 * This method is called once the control that wraps
	 * the field is shown.
	 *
	 * @return 	void
	 */
	onShow() {
		// override parent method to avoid rendering
		// the hidden select using chosen plugin

		// cannot invoke parent method, dispatch the show
		// method of the field manually (if any)
		if (this.data.onShow) {
			this.data.onShow();
		}
	}

	/**
	 * Abstract method invoked when the field is hidden.
	 * This method is called once the control that wraps
	 * the field is hidden.
	 *
	 * @return 	void
	 */
	onHide() {
		// override parent method to avoid unrendering
		// the hidden select using chosen plugin

		// cannot invoke parent method, dispatch the show
		// method of the field manually (if any)
		if (this.data.onHide) {
			this.data.onHide();
		}
	}

	/**
	 * Method used to insert new images within the field.
	 *
	 * @param 	object 	image 	An object containing all the uploaded media.
	 *
	 * @return 	void
	 */
	addImage(image) {
		for (var k in image) {
			if (!image.hasOwnProperty(k)) {
				continue;
			}

			// invoke parent to add select option
			var added = this.addOption(k, image[k]);

			if (added) {
				// append media field
				var mediaFile = '';

				mediaFile += '<div class="ui-media-file">';
				mediaFile += '<img src="' + k + '" title="' + image[k] + '" />';
				mediaFile += '<span data-name="' + k + '">' + (image[k].length > 24 ? image[k].substr(0, 18) + '...' : image[k]) + '</span>';
				mediaFile += '</div>';
				
				jQuery('#' + this.mediaId).append(mediaFile);

				// hide no records box
				jQuery('#' + this.mediaId).find('.no-record-found').hide();
			}
		}

		// unregister blocks events
		jQuery('#' + this.mediaId).find('.ui-media-file').off('click');

		// register block events
		this.initMediaBlocks();
	}

}

// use static property to cache last searched value
UIFormFieldMedia.search = '';

// Register class within the lookup
UIFormField.classMap.media = UIFormFieldMedia;
