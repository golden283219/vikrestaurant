/**
 * UIFormFieldSeparator class.
 * This field is used to display a HTML separator (<hr>).
 * It shouldn't be used for horizontal layouts.
 */
class UIFormFieldSeparator extends UIFormField {

	/**
	 * @override
	 * Method used to obtain the input HTML.
	 *
	 * @return 	string 	The input html.
	 */
	getInput() {
		// avoid wrapping the separator
		this.data.nowrap = true;

		var attrs = '';

		if (this.data.class) {
			attrs += 'class="' + this.data.class + '" ';
		}

		return '<hr ' + attrs + '/>';
	}
	
}

// Register class within the lookup
UIFormField.classMap.separator = UIFormFieldSeparator;
