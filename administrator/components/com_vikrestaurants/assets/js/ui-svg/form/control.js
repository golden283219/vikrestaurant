/**
 * UIFormControl class.
 * This class is used to wrap a form field within a standard layout (label/value).
 */
class UIFormControl {

	/**
	 * Class constructor.
	 *
	 * @param 	object 	data  The field attributes.
	 */
	constructor(data) {
		this.field = UIFormField.getInstance(data);
	}

	/**
	 * Method used to obtain the input control.
	 *
	 * @return 	string 	The control html.
	 */
	getInput() {
		var control = '';

		// get input HTML
		var input = this.field.getInput();

		// return only input in case it is just an hidden value
		if (this.field.data.type == 'hidden' || this.field.data.nowrap === true) {
			return input;
		}

		// check if the control should be hidden
		var style = this.field.data.visible === false ? 'display:none;' : '';

		// open control group
		control += '<div class="ui-control-group' + (this.field.data.controlClass ? ' ' + this.field.data.controlClass : '') + '" style="' + style + '">\n';

		// check if the label should be shown
		if (this.field.data.label) {
			var help = this.field.data.description;

			// create help popover in case the description is set
			if (help) {
				help = '&nbsp;&nbsp;<i class="fas fa-question-circle param-help" title="' + UILocale.getInstance().get(help) + '"></i>';
			} else {
				help = '';
			}

			control += '<div class="ui-control-label">\n';
			control += '<label for="' + this.field.data.id + '">' + UILocale.getInstance().get(this.field.data.label) + help + '</label>\n';
			control += '</div>\n';
		}

		// attach input to control
		control += '<div class="ui-control-input">' + input + '</div>\n';

		// close control
		control += '</div>\n';

		return control;
	}

	/**
	 * Method used to display the control.
	 *
	 * @return 	void
	 */
	show() {
		// show control
		jQuery(this.getSource()).show();

		// after displaying the control, trigger onShow
		// method of the related field
		this.field.onShow();
	}

	/**
	 * Method used to hide the control.
	 *
	 * @return 	void
	 */
	hide() {
		jQuery(this.getSource()).hide();

		// after hiding the control, trigger onHide
		// method of the related field
		this.field.onHide();
	}

	/**
	 * Returns the control source.
	 *
	 * @return 	mixed 	The control source.
	 */
	getSource() {
		return jQuery('#' + this.field.data.id).closest('.ui-control-group')[0];
	}
	
}
