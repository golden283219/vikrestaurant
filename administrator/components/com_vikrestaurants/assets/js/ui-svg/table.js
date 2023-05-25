/**
 * UITable class.
 * A basic representation of a table.
 */
class UITable extends UIObject {

	/**
	 * Class constructor.
	 *
	 * @param 	UIShape  shape  The shape to which the table belong.
	 */
	constructor(shape) {
		super();

		if (!(shape instanceof UIShape)) {
			throw 'Table can belong only to UIShape instances';
		}

		this.shape = shape;

		if (UITable.index === undefined) {
			// declare a static index to count the number of tables
			UITable.index = 1;
		}

		this.id   = 0;
		this.name = 'Table ' + UITable.index;

		this.minCapacity = 2;
		this.maxCapacity = 4;
		this.sharedTable = false;
		this.published   = true;

		// increase the tables counter after creating it
		UITable.index++;
	}

	/**
	 * @override
	 * Method used to obtain the inspector form.
	 *
	 * @return 	object 	The inspector form.
	 */
	getInspector() {
		var _this = this;

		return {
			// table id
			tableId: {
				label: 'ID',
				type: 'hidden',
				default: 0,
				size: 4,
				fieldset: 'Table',
			},

			// table name
			tableName: {
				label: 'Name',
				type: 'text',
				default: 'Table',
				size: 16,
				fieldset: 'Table',
				onAfterSave: function(inspector) {
					// refresh all the inspected shapes
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].refresh();
					}
				},
			},

			// table minimum capacity
			tableMinCapacity: {
				label: 'Min Capacity',
				type: 'number',
				min: 1,
				max: 999,
				default: 2,
				fieldset: 'Table',
				onChange: function(value, inspector) {
					// get maximum value
					var max = parseInt(inspector.form.getField('tableMaxCapacity').getValue());

					if (max < value) {
						// update max as it cannot be lower than min
						inspector.form.getField('tableMaxCapacity').setValue(value);
					}
				},
			},

			// table maximum capacity
			tableMaxCapacity: {
				label: 'Max Capacity',
				type: 'number',
				min: 1,
				max: 999,
				default: 4,
				fieldset: 'Table',
				onChange: function(value, inspector) {
					// get minimum value
					var min = parseInt(inspector.form.getField('tableMinCapacity').getValue());

					if (min > value) {
						// update min as it cannot be higher than max
						inspector.form.getField('tableMinCapacity').setValue(value);
					}
				},
			},

			// table can be shared
			tableCanBeShared: {
				label: 'Can be shared',
				type: 'checkbox',
				fieldset: 'Table',
				onAfterSave: function(inspector) {
					// refresh all the inspected shapes
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].refresh();
					}
				},
			},

			// table can be shared
			tablePublished: {
				label: 'Published',
				type: 'checkbox',
				fieldset: 'Table',
				onAfterSave: function(inspector) {
					// refresh all the inspected shapes
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].refresh();
					}
				},
			},
		};

		// end of method
	}

	/**
	 * @override
	 * Method used to obtain the object configuration.
	 *
	 * @return 	object 	The configuration state.
	 */
	getConfig() {
		// always overwrite table settings
		var config = {
			tableId: this.id,
			tableName: this.name,
			tableMinCapacity: this.minCapacity,
			tableMaxCapacity: this.maxCapacity,
			tableCanBeShared: this.sharedTable,
			tablePublished: this.published,
		};

		// returns a copy of the configuration
		return Object.assign({}, config);
	}

	/**
	 * @override
	 * Method used to save the object state.
	 *
	 * @param 	object 	data 	The form data.
	 *
	 * @return 	void
	 */
	save(data) {
		// validate settings
		if (data.tableName && data.tableName.length > 0) {
			this.name = data.tableName;
		}

		if (!isNaN(parseInt(data.tableMinCapacity))) {
			this.minCapacity = parseInt(data.tableMinCapacity);
		}

		if (!isNaN(parseInt(data.tableMaxCapacity))) {
			this.maxCapacity = parseInt(data.tableMaxCapacity);
		}

		if (data.tableCanBeShared !== undefined) {
			this.sharedTable = data.tableCanBeShared ? true : false;
		}

		if (data.tablePublished !== undefined) {
			this.published = data.tablePublished ? true : false;
		}

		if (!isNaN(parseInt(data.tableId))) {
			this.id = parseInt(data.tableId);
		}

		this.shape.refresh();
	}

	/**
	 * @override
	 * Clones this object by returning a new instance
	 * with the same properties.
	 *
	 * @return 	UIClonable
	 */
	clone() {
		// clone object
		var clone = super.clone();

		// unset ID from table
		clone.id = 0;

		return clone;
	}
}
