/**
 * UIFormFieldCheckbox class.
 * This field is used to display a HTML checkbox.
 */
class UIFormFieldCheckbox extends UIFormField {

	/**
	 * Class constructor.
	 *
	 * @param 	object 	data  The field attributes.
	 */
	constructor(data) {

		/**
		 * Add support for onBeforeSave and onAfterSave in order
		 * to register properly the old field state within the inspector.
		 */
		var onBeforeSave = function(inspector) { inspector.grabFocus(); };
		var onAfterSave  = function(inspector) { inspector.loseFocus(); };

		// keep reference to the previous callback, if any
		var callback = data.onBeforeSave ? data.onBeforeSave : undefined;

		// register before save adapter
		data.onBeforeSave = function(inspector) {
			// invoke adapter
			onBeforeSave(inspector);

			if (callback)
			{
				// then invoke registered method, if provided
				callback(inspector);
			}
		}

		// keep reference to the previous callback
		callback = data.onAfterSave ? data.onAfterSave : undefined;

		// register after save adapter
		data.onAfterSave = function(inspector) {
			// invoke adapter
			onAfterSave(inspector);

			if (callback)
			{
				// then invoke registered method, if provided
				callback(inspector);
			}
		}

		// construct field
		super(data);
	}

	/**
	 * @override
	 * Method used to obtain the input HTML.
	 *
	 * @return 	string 	The input html.
	 */
	getInput() {
		// fetch attributes
		var attrs = '';

		if (this.data.name) {
			attrs += 'name="' + this.data.name + '" ';
		}

		if (this.data.id) {
			attrs += 'id="' + this.data.id + '" ';
		}

		// check if required
		if (this.data.required) {
			if (this.data.class) {
				this.data.class += ' required';
			} else {
				this.data.class = 'required';
			}
		}

		if (this.data.class) {
			attrs += 'class="' + this.data.class + '" ';
		}

		if (this.data.style) {
			attrs += 'style="' + this.data.style + '" ';
		}

		if (this.data.readonly === true) {
			attrs += 'readonly="readonly" ';
		}

		if (this.data.disabled === true) {
			attrs += 'disabled="disabled" ';
		}

		if (this.data.value) {
			attrs += 'checked="checked" ';
		}

		attrs += 'value="1" ';

		// return input
		return '<input type="checkbox" ' + attrs + '/>';
	}

	/**
	 * @override
	 * Method used to return the field value.
	 *
	 * @return 	boolean  True if checked, otherwise false.
	 */
	getValue() {
		return jQuery('#' + this.data.id).is(':checked') ? true : false;
	}

	/**
	 * @override
	 * Method used to set the field value.
	 *
	 * @param 	boolean  val  True to check the checkbox, otherwise false.
	 *
	 * @return 	mixed  	 The source element.
	 */
	setValue(val) {
		return jQuery('#' + this.data.id).prop('checked', val ? true : false);
	}
	
}

// Register class within the lookup
UIFormField.classMap.checkbox = UIFormFieldCheckbox;
