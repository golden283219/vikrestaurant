/**
 * UIFormFieldRadio class.
 * This field is used to display a HTML input radio.
 */
class UIFormFieldRadio extends UIFormField {

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

		if (this.data.value === undefined) {
			this.data.value = this.data.default !== undefined ? this.data.default : '';
		}

		// fetch options
		var input = '<span' + (this.data.id ? ' id="' + this.data.id + '"' : '') + '>';

		for (var k in this.data.options) {
			if (!this.data.options.hasOwnProperty(k)) {
				continue;
			}

			var checked = '';

			if (k == this.data.value) {
				checked = ' checked="checked"';
			}

			var _id = this.data.id ? this.data.id + '-' + k : '';
			var id  = '';

			if (_id) {
				id = 'id="' + _id + '" ';
			}

			if (this.data.translate === true) {
				this.data.options[k] = UILocale.getInstance().get(this.data.options[k]);
			}

			input += '<span class="ui-radio-box radio-box' + (this.data.class ? this.data.class : '') + '">';
			input += '<label for="' + _id + '">' + this.data.options[k] + '</label>';
			input += '<input type="radio" value="' + k + '" ' + id + attrs + checked + '/>';
			input += '</span>';
		}

		input += '</span>';

		// return input
		return input;
	}

	/**
	 * @override
	 * Method used to return the field value.
	 *
	 * @return 	mixed 	The value.
	 */
	getValue() {
		return jQuery(this.getSelector() + ':checked').val();
	}

	/**
	 * @override
	 * Method used to set the field value.
	 *
	 * @param 	mixed  val 	The value to set.
	 *
	 * @return 	mixed  The source element.
	 */
	setValue(val) {
		jQuery('input[name="' + this.data.name + '"]').filter('input[value="' + val + '"]').prop('checked', true);
	}

	/**
	 * @override
	 * Method used to return the field selector.
	 *
	 * @return 	mixed 	The field selector.
	 */
	getSelector() {
		return 'input[name="' + this.data.name + '"]';
	}
	
}

// Register class within the lookup
UIFormField.classMap.radio = UIFormFieldRadio;
