/**
 * UIForm class.
 * This class is used to build the a form with all the related fields.
 */
class UIForm {

	/**
	 * Class constructor.
	 *
	 * @param 	object 	data    The fields structure.
	 * @param 	string  layout  The form layout (vertical or horizontal).
	 */
	constructor(data, layout) {
		this.fieldsets = {};

		this.layout = layout ? layout : 'vertical';

		// iterate fields
		for (var k in data) {
			if (!data.hasOwnProperty(k)) {
				continue;
			}

			// if fieldset is not defined, use the default one
			if (!data[k].fieldset) {
				data[k].fieldset = 'default';
			}

			// define fieldset if empty
			if (!this.fieldsets.hasOwnProperty(data[k].fieldset)) {
				this.fieldsets[data[k].fieldset] = [];
			}

			// inject field name
			data[k].name = k;

			if (data[k].id === undefined) {
				// use default ID
				data[k].id = 'form-field-' + data[k].name;
			}

			// create control
			var control = new UIFormControl(data[k]);

			// push control within the list
			this.fieldsets[data[k].fieldset].push(control);
		}
	}

	/**
	 * Returns the given control.
	 *
	 * @param 	string 	name 	  The control name.
	 * @param 	mixed 	fieldset  The fieldset to render. Leave empty to
	 * 							  render all the fieldsets.
	 *
	 * @return 	mixed 	The field control if exists, otherwise null.
	 */
	getControl(name, fieldset) {
		if (typeof name === 'string') {
			// strip non supported characters (e.g. [])
			name = name.replace(/[^a-zA-Z0-9_-]/g, '');

			// iterate the fieldsets
			for (var set in this.fieldsets) {
				if (!this.fieldsets.hasOwnProperty(set)) {
					continue;
				}

				// check if the fieldset should be rendered
				if (set === fieldset || fieldset === undefined) {
					// iterate the controls within the fieldset
					for (var k = 0; k < this.fieldsets[set].length; k++) {
						// check if the field matches
						if (this.fieldsets[set][k].field.data.name == name) {
							return this.fieldsets[set][k];
						}
					}
				}
			}
		}

		return null;
	}

	/**
	 * Returns the given field.
	 *
	 * @param 	string 	name 	  The field name.
	 * @param 	mixed 	fieldset  The fieldset to render. Leave empty to
	 * 							  render all the fieldsets.
	 *
	 * @return 	mixed 	The field if exists, otherwise null.
	 */
	getField(name, fieldset) {
		// get control
		var control = this.getControl(name, fieldset);

		if (control) {
			return control.field;
		}

		return null;
	}

	/**
	 * Returns the fields contained within the specified fieldset.
	 *
	 * @param 	mixed 	fieldset  The fieldset to check. Leave empty to
	 * 							  include all the fieldsets.
	 *
	 * @return 	array 	The fields list.
	 */
	getFields(fieldset) {
		var list = [];

		// iterate the fieldsets
		for (var set in this.fieldsets) {
			if (!this.fieldsets.hasOwnProperty(set)) {
				continue;
			}

			// check if the fieldset should be rendered
			if (set === fieldset || fieldset === undefined) {
				// iterate the controls within the fieldset
				for (var k = 0; k < this.fieldsets[set].length; k++) {
					// check if the field matches
					list.push(this.fieldsets[set][k].field);
				}
			}
		}

		return list;
	}

	/**
	 * Method used to render the form.
	 *
	 * @param 	object 	data 	  An object containing the field values.
	 * @param 	mixed 	fieldset  The fieldset to render. Leave empty to
	 * 							  render all the fieldsets.
	 *
	 * @return 	string 	The form HTML.
	 */
	render(data, fieldset) {
		var html = '';

		var firstFieldset = true;
		var index = 1;

		// get locale
		var locale = UILocale.getInstance();

		// only if the fieldset is undefined, display the
		// tab buttons to switch between the sections
		if (fieldset === undefined) {
			var tabs = '';

			for (var set in this.fieldsets) {
				if (!this.fieldsets.hasOwnProperty(set)) {
					continue;
				}

				// make sure the set is not equals to 'default'
				if (set != 'default') {
					// open tab
					tabs += '<div class="ui-tab-button' + (firstFieldset ? ' active' : '') + '" id="form-tab-' + index + '">\n';
					// add tab button
					tabs += '<a href="javascript:void(0);" onclick="UIInspector.switchPane(' + index + ');">' + locale.get(set) + '</a>';
					// close tab
					tabs += '</div>';

					firstFieldset = false;
					index++;
				}
			}

			if (tabs.length) {
				// open tab pane
				html += '<div class="ui-tab-pane">\n';
				// append tabs
				html += tabs;
				// close tab pane
				html += '</div>\n';
			}
		}

		firstFieldset = true;
		index = 1;

		// iterate the fieldsets
		for (var set in this.fieldsets) {
			if (!this.fieldsets.hasOwnProperty(set)) {
				continue;
			}

			// check if the fieldset should be rendered
			if (set === fieldset || fieldset === undefined) {
				// use id only for specific fieldsets
				var id = set != 'default' ? 'id="form-fieldset-' + index + '"' : '';

				// open fieldset (hide if the first fieldset has been already displayed)
				html += '<div class="ui-form-fieldset ' + this.layout + '" ' + id + ' style="' + (firstFieldset ? '' : 'display:none;') + '">\n';

				var lastSeparator = false;

				// iterate the controls within the fieldset
				for (var k = 0; k < this.fieldsets[set].length; k++) {
					// get control
					var control = this.fieldsets[set][k];

					// do not display if we are going to place 2 contiguous separators
					if (!lastSeparator || control.field.data.type != 'separator') {
						// bind value, if set and defined
						if (data && data.hasOwnProperty(control.field.data.name)) {
							// get value
							var value = data[control.field.data.name];

							// make sure the value is defined
							if (value !== undefined && value !== null) {
								control.field.bind('value', value);
							}
						}

						// get control HTML
						html += control.getInput();
					}

					lastSeparator = control.field.data.type == 'separator';
				}

				// close fieldset
				html += '</div>\n';

				firstFieldset = false;
				index++;
			}
		}

		html += "<script>jQuery('.param-help').tooltip();</script>";

		return html;
	}
}
