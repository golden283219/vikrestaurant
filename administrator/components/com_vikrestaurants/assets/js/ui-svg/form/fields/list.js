/**
 * UIFormFieldList class.
 * This field is used to display a HTML select.
 */
class UIFormFieldList extends UIFormField {

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
			attrs += 'name="' + this.data.name + (this.data.multiple ? '[]' : '') + '" ';
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

		if (this.data.multiple) {
			attrs += 'multiple="multiple" ';
		}

		if (this.data.readonly === true) {
			attrs += 'readonly="readonly" ';
		}

		if (this.data.disabled === true) {
			attrs += 'disabled="disabled" ';
		}

		if (this.data.options === undefined) {
			this.data.options = {};
		}

		if (this.data.value === undefined) {
			var firstOption = Object.keys(this.data.options);
			firstOption 	= firstOption.length ? firstOption[0] : '';

			this.data.value = this.data.default !== undefined ? this.data.default : firstOption;
		}

		attrs += 'value="' + this.data.value + '" ';

		// fetch options
		var options = '';

		for (var k in this.data.options) {
			if (!this.data.options.hasOwnProperty(k)) {
				continue;
			}

			var selected = '';

			if ((this.data.multiple && this.data.value.indexOf(k) !== -1) || k == this.data.value) {
				selected = ' selected="selected"';
			}

			if (this.data.translate === true) {
				// translate option
				this.data.options[k] = UILocale.getInstance().get(this.data.options[k]);
			}

			options += '<option value="' + k + '"' + selected + '>' + this.data.options[k] + '</option>';
		}

		// return select
		return '<select ' + attrs + '>' + options + '</select>';
	}

	/**
	 * @override
	 * Method used to set the field value.
	 *
	 * @param 	mixed  	val  The value to set.
	 *
	 * @return 	mixed  	The source element.
	 */
	setValue(val) {
		// trigger change for chosen too
		return super.setValue(val).trigger('chosen:updated').trigger('liszt:updated');
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
		try {
			jQuery('#' + this.data.id).chosen();

			// init focus events to prevent shortcuts while searching
			this.initFocusEventsWhileSearching(inspector);
		} catch (err) {
			// do not break the code in case CHOSEN is not supported
		}

		// keep inspector reference within a static property
		UIFormFieldList.inspector = inspector;

		// invoke parent
		super.onInit(inspector);
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

		try {
			// destroy focus events
			this.destroyFocusEventsWhileSearching(inspector);

			jQuery('#' + this.data.id).chosen('destroy');
		} catch (err) {
			// do not break the code in case CHOSEN is not supported
		}
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
		try {
			// recreate chosen as it was rendered using a NULL width
			jQuery('#' + this.data.id).chosen('destroy').chosen();

			// destroy focus events
			this.destroyFocusEventsWhileSearching(UIFormFieldList.inspector);
			// re-init focus events to prevent shortcuts while searching
			this.initFocusEventsWhileSearching(UIFormFieldList.inspector);
		} catch (err) {
			// do not break the code in case CHOSEN is not supported
		}

		// invoke parent
		super.onShow();
	}

	/**
	 * Adds a new option within the list.
	 *
	 * @param 	mixed 	 value
	 * @param 	mixed 	 text
	 *
	 * @return 	boolean  Return false in case the option already exists.
	 */
	addOption(value, text) {
		// check if we already have this option
		if (this.data.options.hasOwnProperty(value)) {
			return false;
		}

		// update options property
		this.data.options[value] = text;

		// alter HTML select
		jQuery('#' + this.data.id).append('<option value="' + value + '">' + text + '</option>');

		return true;
	}

	/**
	 * Initialises the events used to prevent shortcuts
	 * while searching through the chosen dropdown.
	 *
	 * @param 	UIFormWrapper  inspector
	 *
	 * @return 	void
	 */
	initFocusEventsWhileSearching(inspector) {
		var chznId = this.data.id.replace(/-/g, '_');

		// get CHOSEN input search
		var chosenSearch = jQuery('#' + chznId + '_chzn .chzn-search input');

		if (chosenSearch.length) {
			// prevent shortcuts while search input has focus
			chosenSearch.on('focus', function() {
				inspector.grabFocus();
			});

			// make shortcuts available again
			chosenSearch.on('blur', function() {
				inspector.loseFocus();
			});
		}
	}

	/**
	 * Destroys the events used to prevent shortcuts
	 * while searching through the chosen dropdown.
	 *
	 * @param 	UIFormWrapper  inspector
	 *
	 * @return 	void
	 */
	destroyFocusEventsWhileSearching(inspector) {
		var chznId = this.data.id.replace(/-/g, '_');

		// get CHOSEN input search
		var chosenSearch = jQuery('#' + chznId + '_chzn .chzn-search input');

		if (chosenSearch.length) {
			// turn off focus and blur events
			chosenSearch.off('focus');
			chosenSearch.off('blur');
		}
	}
	
}

// Register class within the lookup
UIFormField.classMap.list = UIFormFieldList;
