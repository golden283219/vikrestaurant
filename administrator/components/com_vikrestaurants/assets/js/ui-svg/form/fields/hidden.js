/**
 * UIFormFieldHidden class.
 * This field is used to display a HTML input hidden.
 */
class UIFormFieldHidden extends UIFormField {

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

		if (this.data.class) {
			attrs += 'class="' + this.data.class + '" ';
		}

		if (this.data.value === undefined) {
			this.data.value = this.data.default !== undefined ? this.data.default : '';
		}

		attrs += 'value="' + this.data.value + '" ';

		// return input
		return '<input type="hidden" ' + attrs + '/>';
	}
	
}

// Register class within the lookup
UIFormField.classMap.hidden = UIFormFieldHidden;
