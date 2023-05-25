/**
 * UIFormField class.
 * Abstract representation of a form field.
 * This class acts also as a field factory, as the fields
 * should be instantiated by using the getInstance() static method:
 * var field = UIFormField.getInstance({type: 'text'});
 */
class UIFormField {

	/**
	 * Returns an instance of the requested field.
	 * The field will be recognized by checking the
	 * type property contained within data argument.
	 *
	 * @param 	object 	data  The field attributes.
	 *
	 * @return 	mixed 	The new field.
	 */
	static getInstance(data) {
		// make sure the type exists
		if (!data.hasOwnProperty('type')) {
			console.error(data);
			throw 'Missing type property';
		}

		// fetch field class name
		var className = 'UIFormField' + data.type.charAt(0).toUpperCase() + data.type.substr(1);

		// make sure the class exists
		if (!UIFormField.classMap.hasOwnProperty(data.type)) {
			throw 'Form field [' + className + '] not found';
		}

		// find class
		var _class = UIFormField.classMap[data.type];

		// instantiate field
		return new _class(data);
	}

	/**
	 * Class constructor.
	 *
	 * @param 	object 	data  The field attributes.
	 */
	constructor(data) {
		this.data = data;

		// unset flag
		this.wasUnset = false;
	}

	/**
	 * Binds the given data.
	 *
	 * @param 	string 	k 	The attribute name.
	 * @param 	mixed 	v 	The attribute value.
	 *
	 * @return 	self
	 */
	bind(k, v) {
		this.data[k] = v;
		
		return this;
	}

	/**
	 * Method used to return the field value.
	 *
	 * @return 	mixed 	The value.
	 */
	getValue() {
		return jQuery('#' + this.data.id).val();
	}

	/**
	 * Method used to set the field value.
	 *
	 * @param 	mixed  val 	The value to set.
	 *
	 * @return 	mixed  The source element.
	 */
	setValue(val) {
		return jQuery('#' + this.data.id).val(val);
	}

	/**
	 * Method used to unset the field value.
	 *
	 * @return 	mixed  The source element.
	 */
	unsetValue() {
		// mark as unset
		this.wasUnset = true;

		// unset field value
		return this.setValue('');
	}

	/**
	 * Method used to clear the unset flag.
	 *
	 * @return 	mixed  The source element.
	 */
	clearUnset() {
		// mark as unset
		this.wasUnset = false;

		return jQuery('#' + this.data.id);
	}

	/**
	 * Method used to check if the field is unset.
	 *
	 * @return 	boolean  True if unset, otherwise false.
	 */
	isUnset() {
		return this.wasUnset;
	}

	/**
	 * Method used to return the field selector.
	 *
	 * @return 	mixed 	The field selector.
	 */
	getSelector() {
		return '#' + this.data.id;
	}

	/**
	 * Abstract method used to obtain the input HTML.
	 *
	 * @return 	string 	The input html.
	 */
	getInput() {
		// inherit in children classes
	}

	/**
	 * Abstract method used to initialise the field.
	 * This method is called once the field has been
	 * added within the document.
	 *
	 * @param 	UIFormWrapper  inspector
	 *
	 * @return 	void
	 */
	onInit(inspector) {
		// inherit in children classes

		if (this.data.onInit) {
			// invoke also custom initialize
			this.data.onInit(inspector);
		}
	}

	/**
	 * Abstract method used to destroy the field.
	 * This method is called before removing the field
	 * from the inspector.
	 *
	 * @param 	UIFormWrapper  inspector
	 *
	 * @return 	void
	 */
	onDestroy(inspector) {
		// inherit in children classes

		if (this.data.onDestroy) {
			// invoke also custom destroy
			this.data.onDestroy(inspector);
		}
	}

	/**
	 * Abstract method invoked when the field is shown.
	 * This method is called once the control that wraps
	 * the field is shown.
	 *
	 * @return 	void
	 */
	onShow() {
		// inherit in children classes

		if (this.data.onShow) {
			// invoke also custom show
			this.data.onShow();
		}
	}

	/**
	 * Abstract method invoked when the field is hidden.
	 * This method is called once the control that wraps
	 * the field is hidden.
	 *
	 * @return 	void
	 */
	onHide() {
		// inherit in children classes

		if (this.data.onHide) {
			// invoke also custom show
			this.data.onHide();
		}
	}

	/**
	 * Method used to trigger an event to which the field is registered.
	 *
	 * @param 	string 	event 	The event name to trigger.
	 *
	 * @return 	void
	 */
	trigger(event, inspector) {
		// make event compatible within internal events
		event = 'on' + event.charAt(0).toUpperCase() + event.substr(1);

		if (this.data[event]) {
			// trigger event without using jQuery
			this.data[event](this.getValue(), inspector);
		}
	}
	
}

/**
 * Form fields classes lookup.
 */
UIFormField.classMap = {};
