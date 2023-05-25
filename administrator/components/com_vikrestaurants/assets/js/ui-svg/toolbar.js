/**
 * UIToolbar class.
 * This class is used to inspect the selected command within 
 * the top toolbar.
 */
class UIToolbar extends UIFormWrapper {

	/**
	 * Class constructor.
	 *
	 * @param 	string 	source 	The selector used to access the DOM element. 
	 */
	constructor(source) {
		super(source);

		this.item 	= null;
	}

	/**
	 * @override
	 * Method used to build the given toolbar form.
	 *
	 * @param 	UIToolbarItem  item  The item to inspect.
	 *
	 * @return 	void
	 */
	build(item) {
		// dispose current toolbar
		this.close();

		// make sure we have an object that can be inspected
		if (!(item instanceof UICommand)) {
			console.error(item);
			throw 'Only UICommand instances can be inspected';
		}

		// keep a reference to the given item
		this.item = item;

		// get inspector form
		var data 	= item.getToolbar();
		var config 	= item.getConfig();

		// instantiate form
		this.form = new UIForm(data, 'horizontal');

		// get form HTML
		var html = this.form.render(config);

		// attach HTML within the inspector
		jQuery(this.source).html(html);

		// attach dom events function
		for (var fieldName in data) {
			if (!data.hasOwnProperty(fieldName)) {
				continue;
			}

			// get field instance
			var field = this.form.getField(fieldName);

			// init field
			if (field.onInit) {
				field.onInit(this);
			}

			// attach onChange event
			this.attachEvent('change', field);
			// attach onBlur event
			this.attachEvent('blur', field);
			// attach onFocus event
			this.attachEvent('focus', field);
			// attach onFocus event
			this.attachEvent('enter', field);
		}
	}

	/**
	 * @override
	 * Closes the inspector.
	 *
	 * @return 	void
	 */
	close() {
		if (this.item) {
			// get first element configuration
			var config = Object.keys(this.item.getToolbar());

			// scan config
			for (var k = 0; k < config.length; k++) {
				// get form field
				var f = this.form.getField(config[k]);
				// only if the field exists, destroy it
				if (f && f.onDestroy) {
					f.onDestroy(this);
				}
			}
		}

		this.item = null;

		// remove HTML from the inspector
		jQuery(this.source).html('');
	}

	/**
	 * @override
	 * Saves the configuration of the current inspected objects.
	 *
	 * @return 	void
	 */
	save() {
		// build configuration data
		var data = {};

		if (!this.form || !this.item) {
			// do nothing if there is no form
			return;
		}

		// get form fields
		var fields = this.form.getFields();

		for (var k = 0; k < fields.length; k++) {
			// get field name
			var name = fields[k].data.name;

			// get form value
			data[name] = this.form.getField(name).getValue();
		}

		// notify the element
		this.item.save(data);
	}
}
