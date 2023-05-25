/**
 * UIFormFieldColor class.
 * This field is used to display a colorpicker.
 */
class UIFormFieldColor extends UIFormFieldText {

	/**
	 * @override
	 * Method used to obtain the input HTML.
	 *
	 * @return 	string 	The input html.
	 */
	getInput() {
		// keep style for colorpicker
		var style = this.data.style;
		// overwrite style for input text
		this.data.style = 'display: none;';

		// get input text from parent
		var input = super.getInput();

		// setup style attribute
		style = style ? ' style="' + style + '"' : '';

		this.pickerId = this.data.id + '-picker';

		// append color picker to input
		input += '<button type="button" id="' + this.pickerId + '" class="color-picker-eye"' + style + '>';
		input += '<span style="background-color:#' + this.data.value + ';">&nbsp;</span>';
		input += '<i class="fas fa-eye-dropper"></i>';
		input += '</button>';

		return input;
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
		// init input
		var el = super.setValue(val);

		if (!val) {
			val = 'transparent';
		} else {
			val = '#' + val;
		}

		// change color
		jQuery('#' + this.pickerId).find('span').css('background-color', val);

		return el;
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
		var THUMB_COLOR = null;

		var _this = this;

		jQuery('#' + this.pickerId).ColorPicker({
			onBeforeShow: function() {
				jQuery('body').append('<div class="ui-transparent-overlay"></div>');
			},
			onShow: function() {
				// preselect current value
				THUMB_COLOR = _this.getValue();

				if (!THUMB_COLOR) {
					// use a default color to avoid issues
					THUMB_COLOR = 'ffffff';
				}

				jQuery(this).ColorPickerSetColor('#' + THUMB_COLOR.toUpperCase());
				// trigger "enter" event as focus doesn't work for hidden elements
				jQuery('#' + _this.data.id).trigger('enter');
			},
			onChange: function (hsb, hex, rgb) {
				// assign tmp color
				THUMB_COLOR = hex;
			},
			onHide: function(){
				// remove overlay
				jQuery('.ui-transparent-overlay').remove();
				
				// commit changes only if value is changed
				if (THUMB_COLOR.toUpperCase() != jQuery('#' + _this.data.id).val().toUpperCase()) {
					// set value and refresh CSS thumb
					_this.setValue(THUMB_COLOR.toUpperCase()).trigger('change');
				}

				// always trigger blur
				jQuery('#' + _this.data.id).trigger('blur');
			}
		});
	
		// open datepicket also when clicking the label
		jQuery('label[for="' + this.data.id + '"]').on('click', function() {
			jQuery('#' + _this.pickerId).ColorPickerShow();
		});

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
		
		// destroy colorpicker instance
		jQuery('#' + this.pickerId).ColorPickerDestroy();
	}
	
}

// Register class within the lookup
UIFormField.classMap.color = UIFormFieldColor;
