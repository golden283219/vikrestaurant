/**
 * UIShapeRect class.
 * Class used to display a common rectangle and (obviously) also a square.
 */
class UIShapeRect extends UIShape {

	/**
	 * Class constructor.
	 *
	 * @param 	float 	x 	The shape X position.
	 * @param 	float 	y 	The shape Y position.
	 * @param 	float 	w 	The shape width.
	 * @param 	float 	h 	The shape height.
	 */
	constructor(x, y, w, h) {
		super(x, y, w, h);

		this.state = {};
	}

	/**
	 * @override
	 * Method used to render a rectangle.
	 *
	 * @param 	object 	options  A configuration object.
	 *
	 * @return 	mixed 	The rectangle element.
	 */
	render(options) {
		// populate options with state
		options.fill 	= options.fill 	 ? options.fill   : this.state.bgColor;
		options.stroke 	= options.stroke ? options.stroke : this.state.bgColor;
		options.color   = options.color  ? options.color  : this.state.fgColor;

		// define default attributes
		options.fill   		= options.fill   		? options.fill   		: 'a3a3a3';
		options.stroke 		= options.stroke 		? options.stroke 		: 'a3a3a3';
		options.color 		= options.color 		? options.color 		: 'ffffff';
		options.strokeWidth = options.strokeWidth 	? options.strokeWidth 	: 0;

		// save background color
		this.state.bgColor = options.fill;
		// save foreground color
		this.state.fgColor = options.color;

		if (this.id === undefined) {
			// save ID if the property doesn't exist yet
			this.id = options.id;
		}

		var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');

		g.setAttribute('fill', '#' + options.fill);
		g.setAttribute('stroke', '#' + options.stroke);
		g.setAttribute('stroke-width', options.strokeWidth);
		g.setAttribute('id', options.id);

		var rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');

		rect.setAttribute('x',      this.x + options.strokeWidth / 2);
		rect.setAttribute('y',      this.y + options.strokeWidth / 2);
		rect.setAttribute('width',  this.w - options.strokeWidth);
		rect.setAttribute('height', this.h - options.strokeWidth);

		if (!this.table.published) {
			rect.setAttribute('fill-opacity', 0.6);
		}

		g.appendChild(rect);

		// append table name
		var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');

		text.setAttribute('class', 'table-name-text');
		text.setAttribute('x', this.x + this.w / 2);
		text.setAttribute('y', this.y + 30);
		text.setAttribute('text-anchor', 'middle');
		text.setAttribute('fill', '#ffffff');

		text.innerHTML = this.table.name;

		g.appendChild(text);

		// append table capacity
		var capText = document.createElementNS('http://www.w3.org/2000/svg', 'text');

		capText.setAttribute('class', 'table-capacity-text');
		capText.setAttribute('x', this.x + 4);
		capText.setAttribute('y', this.y + this.h - 4);
		capText.setAttribute('fill', '#ffffff');

		capText.innerHTML = this.table.minCapacity + '-' + this.table.maxCapacity;

		g.appendChild(capText);

		// append shared table badge

		var image = document.createElementNS('http://www.w3.org/2000/svg', 'image');

		var sharedIcon = UITiles.getTile('sharedtable');

		image.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', sharedIcon.src);

		image.setAttribute('preserveAspectRatio', 'none');

		image.setAttribute('class', 'table-shared-badge');

		if (!this.table.sharedTable) {
			image.setAttribute('style', 'display:none;');
		}

		image.setAttribute('x',      this.x + this.w - (sharedIcon.width + 4));
		image.setAttribute('y',      this.y + this.h - (sharedIcon.height + 4));
		image.setAttribute('width',  sharedIcon.width);
		image.setAttribute('height', sharedIcon.height);

		g.appendChild(image);

		return g;
	}

	/**
	 * @override
	 * Method used to refresh the shape.
	 *
	 * @return 	void
	 */
	refresh() {
		// refresh parent too
		super.refresh();

		var transform = 'rotate(%f %f %f)'.sprintf(this.rotation, this.x + this.w / 2, this.y + this.h / 2);

		jQuery(this.getSource())
			.attr('transform', transform);

		jQuery(this.getSource())
			.find('rect')
				.attr('x', this.x)
				.attr('y', this.y)
				.attr('width', this.w)
				.attr('height', this.h)
				.attr('rx', this.state.roundness ? this.state.roundness : 0)
				.attr('ry', this.state.roundness ? this.state.roundness : 0)
				.attr('fill-opacity', this.table.published ? 1 : 0.6);

		jQuery(this.getSource())
			.find('text.table-name-text')
				.text(this.table.name)
				.attr('x', this.x + this.w / 2)
				.attr('y', this.y + 30);

		jQuery(this.getSource())
			.find('text.table-capacity-text')
				.text(this.table.minCapacity + '-' + this.table.maxCapacity)
				.attr('x', this.x + 4)
				.attr('y', this.y + this.h - 4);

		var sharedIcon = UITiles.getTile('sharedtable');

		var badge = jQuery(this.getSource())
			.find('image.table-shared-badge')
				.attr('x', this.x + this.w - (sharedIcon.width + 4))
				.attr('y', this.y + this.h - (sharedIcon.height + 4))
				.attr('width',  sharedIcon.width)
				.attr('height', sharedIcon.height);

		if (this.table.sharedTable) {
			badge.show();
		} else {
			badge.hide();
		}

		
	}

	/**
	 * Method used to access the source of the shape.
	 *
	 * @return 	mixed 	The shape source.
	 */
	getSource() {
		return jQuery('#' + this.id)[0];
	}

	/**
	 * @override
	 * Method used to obtain the inspector form.
	 *
	 * @return 	object 	The inspector form.
	 */
	getInspector(include) {
		var _this = this;

		var inspector = {
			// SHAPE FIELDSET

			// shape x position
			posx: {
				label: 'Position X',
				type: 'number',
				min: 0,
				max: 4096,
				default: 0,
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('posx').data;

					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].setX(value);
					}
				},
			},

			// shape y position
			posy: {
				label: 'Position Y',
				type: 'number',
				min: 0,
				max: 4096,
				default: 0,
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('posy').data;
					
					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].setY(value);
					}
				},
			},

			// add separator
			separator1: {
				type: 'separator',
				fieldset: 'Shape',
			},

			// shape width
			width: {
				label: 'Width',
				type: 'number',
				min: 10,
				max: 4096,
				default: 100,
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('width').data;

					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].setWidth(value);
					}

					if (inspector.form.getField('propSize').getValue()) {
						// update height too
						inspector.getField('height').val(value);

						// iterate all inspected elements
						for (var k = 0; k < inspector.elements.length; k++) {
							inspector.elements[k].setHeight(value);
						}
					}
				},
			},

			// shape height
			height: {
				label: 'Height',
				type: 'number',
				min: 10,
				max: 4096,
				default: 100,
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('height').data;

					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].setHeight(value);
					}

					if (inspector.form.getField('propSize').getValue()) {
						// update width too
						inspector.getField('width').val(value);

						// iterate all inspected elements
						for (var k = 0; k < inspector.elements.length; k++) {
							inspector.elements[k].setWidth(value);
						}
					}
				},
			},

			// proportional size
			propSize: {
				label: 'Proportional Size',
				description: 'When checked, the width and height will have always the same value.',
				type: 'checkbox',
				checked: false,
				fieldset: 'Shape',
			},

			// add separator
			separator2: {
				type: 'separator',
				fieldset: 'Shape',
			},

			// shape rotation
			rotate: {
				label: 'Rotation',
				type: 'number',
				min: 0,
				max: 360,
				default: 0,
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					// don't need to check min and max

					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].rotate(value);
					}
				},
			},

			// background color
			bgColor: {
				label: 'Background Color',
				type: 'color',
				default: 'a3a3a3',
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						var id = inspector.elements[k].id;

						jQuery('#' + id).attr('fill', '#' + value);
						jQuery('#' + id).attr('stroke', '#' + value);
					}
				},
			},

			// foreground color
			fgColor: {
				label: 'Foreground Color',
				type: 'color',
				default: 'ffffff',
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						var id = inspector.elements[k].id;

						jQuery('#' + id).find('text').attr('fill', '#' + value);
					}
				},
			},

			// shape roundness
			roundness: {
				label: 'Roundness',
				description: 'Roundness is the measure of how closely a shape approaches that of a mathematically perfect circle.',
				type: 'number',
				min: 0,
				max: 4096,
				default: 0,
				fieldset: 'Shape',				
			},
		};

		// get parent inspector
		var tableInspector = this.table.getInspector();

		// merge parent inspector with the current one
		inspector = Object.assign(inspector, tableInspector);

		return inspector;
	}

	/**
	 * @override
	 * Method used to obtain the object configuration.
	 *
	 * @return 	object 	The configuration state.
	 */
	getConfig() {
		var shape = jQuery('#' + this.id);

		// always overwrite shape settings
		this.state.posx    = this.x;
		this.state.posy    = this.y;
		this.state.width   = this.w;
		this.state.height  = this.h;
		this.state.rotate  = this.rotation;
		this.state.bgColor = shape.length ? shape.attr('fill').replace(/^#/, '').toUpperCase() : null;
		this.state.fgColor = shape.length ? shape.find('text').first().attr('fill').replace(/^#/, '').toUpperCase() : null;

		// use default roundess if not specified
		this.state.roundness = this.state.roundness ? this.state.roundness : 0;
		
		// merge current configuration with table settings
		this.state = Object.assign(this.state, this.table.getConfig());

		// return a copy of the configuration
		return Object.assign({}, this.state);
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
		this.state = data;

		// refresh design area (for undo/redo)
		if (!isNaN(parseInt(this.state.posx))) {
			this.setX(this.state.posx);
		}

		if (!isNaN(parseInt(this.state.posy))) {
			this.setY(this.state.posy);
		}

		if (!isNaN(parseInt(this.state.width))) {
			this.setWidth(this.state.width);
		}
		
		if (!isNaN(parseInt(this.state.height))) {
			this.setHeight(this.state.height);
		}

		if (!isNaN(parseInt(this.state.rotate))) {
			this.rotate(this.state.rotate);
		}

		if (this.state.bgColor) {
			// refresh background color (for undo/redo)
			jQuery('#' + this.id)
				.attr('fill', '#' + this.state.bgColor)
				.attr('stroke', '#' + this.state.bgColor);
		}

		if (this.state.fgColor) {
			// refresh foreground color (for undo/redo)
			jQuery('#' + this.id)
				.find('text')
				.attr('fill', '#' + this.state.fgColor);
		}

		if (!isNaN(parseInt(this.state.roundness))) {
			jQuery('#' + this.id)
				.find('rect')
					.attr('rx', this.state.roundness)
					.attr('ry', this.state.roundness);
		}

		// update table settings too
		this.table.save(data);
	}

}
