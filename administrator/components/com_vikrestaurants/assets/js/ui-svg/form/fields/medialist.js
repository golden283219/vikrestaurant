/**
 * UIFormFieldMedialist class.
 * This field is used to display a HTML select containing MEDIA files.
 */
class UIFormFieldMedialist extends UIFormFieldList {

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
		// invoke parent
		super.onInit(inspector);

		var _this = this;

		// subscribe field and callback to update the images
		// every time a new media is uploaded
		UIFileDialog.subscribe(this.data.id, function(image) {
			// make sure the field still exists
			if (!_this) {
				return;
			}

			for (var k in image) {
				if (!image.hasOwnProperty(k)) {
					continue;
				}

				// invoke parent to add select option
				_this.addOption(k, image[k]);
			}

			// refresh rendering
			_this.onShow();
		});
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
		// invoke parent
		super.onDestroy(inspector);

		// unsubscribe list from file dialog updates
		UIFileDialog.unsubscribe(this.data.id);
	}
	
}

// Register class within the lookup
UIFormField.classMap.medialist = UIFormFieldMedialist;
