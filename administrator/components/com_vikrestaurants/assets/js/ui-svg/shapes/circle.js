/**
 * UIShapeCircle class.
 * Class used to display a common circle.
 */
class UIShapeCircle extends UIShapeRect {

	/**
	 * Class constructor.
	 *
	 * @param 	float 	x 	The shape X position.
	 * @param 	float 	y 	The shape Y position.
	 * @param 	float 	r 	The shape radius.
	 */
	constructor(x, y, r) {
		super(x, y, r * 2, r * 2);
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
		var g = super.render(options);

		// create circle
		var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');

		circle.setAttribute('cx', this.x + this.w / 2);
		circle.setAttribute('cy', this.y + this.w / 2);
		circle.setAttribute('r',  this.w / 2);

		if (!this.table.published) {
			circle.setAttribute('fill-opacity', 0.6);
		}

		// replace rectangle with circle
		g.childNodes[0].replaceWith(circle);

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

		jQuery(this.getSource())
			.find('circle')
				.attr('cx', this.x + this.w / 2)
				.attr('cy', this.y + this.w / 2)
				.attr('r', this.w / 2)
				.attr('fill-opacity', this.table.published ? 1 : 0.6);
	}

	/**
	 * Sets the circle radius.
	 *
	 * @param 	float 	r 	The circle radius.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	setRadius(r) {
		return this.setWidth(r * 2);
	}

	/**
	 * @override
	 * Sets the width of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	w 	The shape width.
	 *
	 * @return 	self
	 */
	setWidth(w) {
		// set width
		super.setWidth(w);

		// update height too
		this.h = this.w;

		return this;
	}

	/**
	 * @override
	 * Sets the height of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	h 	The shape height.
	 *
	 * @return 	self
	 */
	setHeight(h) {
		// set height
		super.setHeight(h);

		// update width too
		this.w = this.h;

		return this;
	}

	/**
	 * @override
	 * Method used to obtain the inspector form.
	 *
	 * @return 	object 	The inspector form.
	 */
	getInspector() {
		var _this = this;

		var inspector = {
			// SHAPE FIELDSET

			// shape x position
			posx: {
				label: 'Position X',
				type: 'number',
				min: 0,
				max: 4096,
				default: 50,
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
				default: 50,
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

			// shape radius
			radius: {
				label: 'Radius',
				type: 'number',
				min: 5,
				max: 4096,
				default: 50,
				fieldset: 'Shape',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('radius').data;

					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					// iterate all inspected elements
					for (var k = 0; k < inspector.elements.length; k++) {
						inspector.elements[k].setRadius(value);
					}
				},
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

			// add separator
			separator2: {
				type: 'separator',
				fieldset: 'Shape',
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
		this.state.radius  = this.w / 2;
		this.state.rotate  = this.rotation;
		this.state.bgColor = shape.length ? shape.attr('fill').replace(/^#/, '').toUpperCase() : null;
		this.state.fgColor = shape.length ? shape.find('text').first().attr('fill').replace(/^#/, '').toUpperCase() : null;
		
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

		if (!isNaN(parseInt(this.state.radius))) {
			this.setRadius(this.state.radius);
		}

		if (!isNaN(parseInt(this.state.rotate))) {
			this.rotate(this.state.rotate);
		}

		if (this.state.bgColor) {
			// refresh background color (for undo/redo)
			jQuery('#' + this.id).attr('fill', '#' + this.state.bgColor);
			jQuery('#' + this.id).attr('stroke', '#' + this.state.bgColor);
		}

		if (this.state.fgColor) {
			// refresh foreground color (for undo/redo)
			jQuery('#' + this.id)
				.find('text')
				.attr('fill', '#' + this.state.fgColor);
		}

		// update table settings too
		this.table.save(data);
	}

}
