/**
 * UIFormWrapper class.
 * Abstract class used to handle a form wrapper, 
 * such as a fieldset, an inspector, a toolbar, a popup, etc...
 */
class UIFormWrapper {

	/**
	 * Class constructor.
	 *
	 * @param 	string 	source 	The selector used to access the DOM element. 
	 */
	constructor(source) {
		this.form   = null;
		this.source = jQuery(source)[0];
	}

	/**
	 * Abstract method used to build the given toolbar form.
	 *
	 * @param 	mixed  item  The item to inspect or a list of items.
	 *
	 * @return 	void
	 */
	build(item) {
		// inherit in children classes
	}

	/**
	 * Abstract method used to refresh the form values with 
	 * the configuration of the attached elements.
	 *
	 * @return 	void 	
	 */
	refresh() {
		// inherit in children classes
	}

	/**
	 * Abstrcat method used to close the inspector.
	 *
	 * @return 	void
	 */
	close() {
		// inherit in children classes
	}

	/**
	 * Attaches an event to the document.
	 *
	 * @param 	string 		 event 	The jQuery event name.
	 * @param 	UIFormField  field 	The field object.
	 *
	 * @return 	void
	 */
	attachEvent(event, field) {
		// retrieve native event
		var eventAttr = 'on' + event.charAt(0).toUpperCase() + event.substr(1);

		var _this = this;

		// register event
		jQuery(field.getSelector()).on(event, function(e) {
			// make sure the event is set
			if (field.data[eventAttr]) {
				// pass new value to event callback
				field.data[eventAttr](field.getValue(), _this);
			}

			switch (event) {
				case 'change':
					// the value was changed, clear unset
					field.clearUnset();

					// invoke on before save method if supported
					if (field.data.onBeforeSave) {
						field.data.onBeforeSave(_this);
					}

					// force saving as the setting may be used by other components
					_this.save();

					// invoke on after change method if supported
					if (field.data.onAfterSave) {
						field.data.onAfterSave(_this);
					}
					break;

				case 'focus':
				case 'enter':
					// attach the focus to the inspector while entering on an input
					_this.grabFocus();
					break;

				case 'blur':
					// detach the focus from the inspector while entering on an input
					_this.loseFocus();
					break;
			}
		});
	}

	/**
	 * Returns the given control.
	 *
	 * @param 	string 	name 	The control name.
	 *
	 * @return 	mixed 	The control element.
	 */
	getControl(name) {
		// find the control
		var control = this.form.getControl(name);

		if (control) {
			return jQuery(control.getSource());
		}

		return null;
	}

	/**
	 * Returns the given field.
	 *
	 * @param 	string 	name 	The field name.
	 *
	 * @return 	mixed 	The field element.
	 */
	getField(name) {
		// find the field
		var field = this.form.getField(name);

		if (field) {
			return jQuery(field.getSelector());
		}

		return null;
	}

	/**
	 * Abstract method used to save the configuration.
	 *
	 * @return 	void
	 */
	save() {
		// inherit in children classes
	}

	/**
	 * Attaches the focus to the inspector.
	 *
	 * @return 	self
	 */
	grabFocus() {
		this.focus = true;

		return this;
	}

	/**
	 * Detaches the focus from the inspector.
	 *
	 * @return 	self
	 */
	loseFocus() {
		this.focus = false;

		return this;
	}

	/**
	 * Checks if the inspector owns the focus.
	 *
	 * @return 	boolean
	 */
	hasFocus() {
		return this.focus ? true : false;
	}
}
