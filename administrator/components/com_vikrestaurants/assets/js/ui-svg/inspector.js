/**
 * UIInspector class.
 * This class is used to inspect the selected elements within 
 * the right sidebar.
 */
class UIInspector extends UIFormWrapper {

	/**
	 * Class constructor.
	 *
	 * @param 	string 	source 	The selector used to access the DOM element. 
	 */
	constructor(source) {
		super(source);

		this.cache    = {};
		this.elements = [];
	}

	/**
	 * Attaches the canvas to the inspector.
	 *
	 * @param 	UICanvas  canvas 	The attached canvas.
	 *
	 * @return 	self 	  This object to support chaining.
	 */
	attach(canvas) {
		if (!(canvas instanceof UICanvas)) {
			console.error(canvas);
			throw 'Invalid [' + (typeof canvas) + '] canvas';
		}

		this.canvas = canvas;

		return this;
	}

	/**
	 * @override
	 * Method used to build the given inspector form.
	 *
	 * @param 	mixed  objects  The inspectable object or a list of items.
	 *
	 * @return 	void
	 */
	build(objects) {
		// dispose current inspector
		this.close();

		// we need to handle a list of objects
		if (!Array.isArray(objects)) {
			objects = [objects];
		}

		// init elements
		this.elements = [];
		for (var k = 0; k < objects.length; k++) {
			this.elements.push(objects[k]);
		}

		// fetch only the first element
		var obj = objects.shift();

		// make sure we have an object that can be inspected
		if (!(obj instanceof UIObject)) {
			console.error(obj);
			throw 'Only UIObject instances can be inspected';
		}

		// get object form
		var data = obj.getInspector();

		var tmpData = {};

		// make sure we are handling elements of the same type
		for (var i = 0; i < objects.length; i++) {
			// compare the class name
			if (objects[i].constructor.name != obj.constructor.name) {
				/**
				 * We are probably handling circles and rects simultaneously.
				 * Filter the fields of this element to keep only the ones that 
				 * are used also by the first element of the list.
				 */

				// do not check settings if we alread parsed this class
				if (!tmpData.hasOwnProperty(objects[i].constructor.name)) {
					// mark shape as checked
					tmpData[objects[i].constructor.name] = 1;

					// get object inspactor
					var ins = objects[i].getInspector();

					// iterate the settings within the base inspector
					for (var setting in data) {
						if (!data.hasOwnProperty(setting)) {
							continue;
						}

						// check if the tmp object owns this property
						if (!ins.hasOwnProperty(setting)) {
							// delete setting as this object doesn't use it
							delete data[setting];
						}
					}
				}
			}
		}

		// get class name
		var className = obj.constructor.name;
		// define empty cache if not set
		if (!this.cache.hasOwnProperty(className)) {
			this.cache[className] = {};
		}

		// get object configuration
		var config = obj.getConfig();

		// instantiate form
		this.form = new UIForm(data);

		// get form HTML
		var html = this.form.render(config);

		// attach HTML within the inspector
		jQuery(this.source).html(html);

		// restore cached active pane, if set
		if (this.cache[className].fieldset) {
			UIInspector.switchPane(this.cache[className].fieldset);
		}

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

		// iterate remaining object to keep only matching values
		for (var k = 0; k < objects.length; k++) {
			// get object config
			var tmp = objects[k].getConfig();

			// iterate all fields
			for (var fieldName in data) {
				if (!data.hasOwnProperty(fieldName)) {
					continue;
				}

				// compare the current object with the selected one
				if (config[fieldName] != tmp[fieldName]) {
					// we have 2 different values, we need to unset the related field
					this.form.getField(fieldName).unsetValue();
				}
			}
		}

		// readd shifted element as the list may be used by other components
		objects.unshift(obj);
	}

	/**
	 * @override
	 * Refreshes the form values with the configuration
	 * of the attached elements.
	 *
	 * @return 	void 	
	 */
	refresh() {
		if (this.elements.length == 0) {
			// do nothing, no attached elements
			return;
		}

		// get first element configuration
		var config = this.elements[0].getConfig();

		// scan config and assign values to fields
		for (var k in config) {
			if (!config.hasOwnProperty(k)) {
				continue;
			}

			// get form field
			var f = this.form.getField(k);
			// only if the field exists, set the value
			if (f) {
				f.setValue(config[k]);
			}
		}

		// iterate remaining object to keep only matching values
		for (var i = 1; i < this.elements.length; i++) {
			// get object config
			var tmp = this.elements[i].getConfig();

			// iterate all fields
			for (var k in config) {
				if (!config.hasOwnProperty(k)) {
					continue;
				}

				// get form field
				var f = this.form.getField(k);
				// compare the current object with the selected one
				if (f && config[k] != tmp[k]) {
					// we have 2 different values, we need to unset the related field
					f.unsetValue();
				}
			}
		}
	}

	/**
	 * @override
	 * Closes the inspector.
	 *
	 * @return 	void
	 */
	close() {
		// do not need anymore to save while closing
		// the inspector as save method is always triggered 
		// every time the state of a field changes
		// this.save();

		if (this.elements.length) {
			// get first element configuration
			var config = Object.keys(this.elements[0].getInspector());

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

		if (!this.form || !this.elements.length) {
			// do nothing if there is no form
			return;
		}

		// get form fields
		var fields = this.form.getFields();

		for (var k = 0; k < fields.length; k++) {
			// get field name
			var name = fields[k].data.name;

			// use value only if set
			if (!fields[k].isUnset()) {
				// get form value
				data[name] = fields[k].getValue();
			}
		}

		// notify all the elements
		for (var i = 0; i < this.elements.length; i++) {
			if (this.elements[i] instanceof UIObject) {
				// pass data as copy because we don't want to keep
				// any updates performed by an object
				this.elements[i].save(Object.assign({}, data));
			}
		}

		// cache some useful settings
		var className = this.elements[0].constructor.name;

		this.cache[className].fieldset = UIInspector.getActivePane();

		// get previous state
		var state = this.getPreviousState();

		if (state) {
			// register object changed action for undo/redo support
			this.canvas.stackActions.register(new UIStateActionObject(this.elements, state, this));
		}

		/**
		 * @translate
		 */
		if (this.elements.length > 1) {
			this.canvas.statusBar.display(UILocale.getInstance().get('%d elements saved', this.elements.length));
		} else {
			this.canvas.statusBar.display('Element saved', {translate: true});
		}

		// probably something has changed
		this.canvas.changed();
	}

	/**
	 * @override
	 * Attaches the focus to the inspector.
	 *
	 * @return 	self
	 */
	grabFocus() {
		// keep elements current state after entering an input
		this.currentState = this.elements.map(function(e) {
			return Object.assign({}, e.getConfig());
		});

		return super.grabFocus();
	}

	/**
	 * Returns the previous elements state, if any.
	 *
	 * @return 	mixed 	The previous state.
	 */
	getPreviousState() {
		return this.currentState ? this.currentState : null;
	}

	/**
	 * Shows the requested panel.
	 *
	 * @param 	integer  id  The identifier of the pane to show.
	 *
	 * @return 	void
	 */
	static switchPane(id) {
		if (jQuery('#form-fieldset-' + id).length == 0) {
			// do not restore fieldset if it doesn't exist
			return;
		}

		jQuery('.ui-tab-button').removeClass('active');
		jQuery('#form-tab-' + id).closest('.ui-tab-button').addClass('active');

		// hide only the form fieldset at the same level of the selected one
		// to avoid hiding form fieldset on other sections
		jQuery('#form-fieldset-' + id).siblings('.ui-form-fieldset').hide();
		jQuery('#form-fieldset-' + id).show();
	}

	/**
	 * Returns the identifier of the current active pane.
	 *
	 * @return 	mixed  The pane identifier if set, otherwise false.
	 */
	static getActivePane() {
		var id = jQuery('.ui-tab-button.active').attr('id');

		if (id) {
			return id.match(/^form-tab-(\d+)$/).pop();
		}

		return false;
	}
}
