/**
 * UIFormFieldNumber class.
 * This field is used to display a HTML input number.
 */
class UIFormFieldNumber extends UIFormField {

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

		if (this.data.min !== undefined) {
			attrs += 'min="' + this.data.min + '" ';
		}

		if (this.data.max !== undefined) {
			attrs += 'max="' + this.data.max + '" ';
		}

		if (!this.data.step) {
			this.data.step = 1;
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

		attrs += 'value="' + this.data.value + '" ';
		attrs += 'step="' + this.data.step + '" ';

		// return input
		return '<input type="number" ' + attrs + '/>';
	}
	
}

// Register class within the lookup
UIFormField.classMap.number = UIFormFieldNumber;
