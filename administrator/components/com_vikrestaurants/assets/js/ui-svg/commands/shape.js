/**
 * UICommandShape class.
 * This command is used to add new shapes into the canvas.
 * The shape will be added after clicking the canvas. If a shape
 * is clicked while adding, that shape will be automatically selected.
 * It is possible to use the toolbar to change the shape type and
 * the related default settings.
 */
class UICommandShape extends UICommand {

	/**
	 * @override
	 * Initializes the command.
	 * The canvas is passed as argument in order to
	 * extend the default functionalities, such as a new event.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	init(canvas) {
		super.init(canvas);
		
		// keep a reference to the canvas
		this.canvas = canvas;
	}

	/**
	 * @override
	 * Returns the command title.
	 *
	 * @return string
	 */
	getTitle() {
		return 'Add new shapes';
	}

	/**
	 * @override
	 * Returns the shortcut that can be used to activate the command.
	 * The array must contain one and only one character or symbol.
	 * The array may contain one ore more modifiers, which must be specified first.
	 *
	 * @return 	array 	A list of modifiers and characters.
	 */
	getShortcut() {
		return ['n'];
	}

	/**
	 * Draws a new shape on click.
	 *
	 * @param 	Event 	event
	 * @param 	mixed 	element
	 *
	 * @return 	void
	 */
	mousedown(event, element) {
		if (!this.active) {
			// ignore if inactive
			return;
		}

		// make sure we clicked the canvas
		if (!(element instanceof UICanvas)) {
			// we clicked a different element, select it
			this.canvas.triggerCommand('cmd-select');

			// dispatch event again, which will be caught by
			// the select command this time
			this.canvas.handleEvent(event, element);

			// finally stop shape propagation
			return false;
		}

		// get shape type
		var shapeType = this.config.shapeType ? this.config.shapeType : 'rect';
		var shape     = null;

		// search for rectangle
		if (shapeType == 'rect') {

			// create rectangle
			shape = new UIShapeRect(
				event.offsetX,
				event.offsetY,
				this.config.shapeDefaultWidth ? parseInt(this.config.shapeDefaultWidth) : 100,
				this.config.shapeDefaultHeight ? parseInt(this.config.shapeDefaultHeight) : 100
			);

		}
		// search for circle
		else if (shapeType == 'circle') {

			// get circle radius
			var radius = this.config.shapeDefaultRadius ? parseInt(this.config.shapeDefaultRadius) : 50;

			// create circle
			shape = new UIShapeCircle(
				event.offsetX - radius,
				event.offsetY - radius,
				radius
			);

		}
		// search for image
		else if (shapeType == 'image') {

			// create image
			shape = new UIShapeImage(
				event.offsetX,
				event.offsetY,
				this.config.shapeDefaultWidth ? parseInt(this.config.shapeDefaultWidth) : 100,
				this.config.shapeDefaultHeight ? parseInt(this.config.shapeDefaultHeight) : 100
			);

		}
		// shape not supported
		else {
			throw 'Shape [' + shapeType + '] not supported';
		}

		// define rendering options
		var options = {};

		if (this.config.shapeDefaultBgColor) {
			options.fill = this.config.shapeDefaultBgColor;
		}

		if (this.config.shapeDefaultBackground) {
			options.href = this.config.shapeDefaultBackground;
		}

		// draw the shape within the canvas
		element.add(shape, options);

		// probably something has changed
		element.changed();
	}

	/**
	 * @override
	 * Method used to obtain the toolbar form.
	 *
	 * @return 	object 	The toolbar form.
	 */
	getToolbar() {
		var _this = this;

		return {

			// type of shape
			shapeType: {
				label: 'Shape type',
				type: 'list',
				options: {
					rect: 'Rectangle',
					circle: 'Circle',
					image: 'Image',
				},
				default: 'rect',
				translate: true,
				onChange: function(value, inspector) {
					// hide any shape type child
					jQuery('.shape-type-child').each(function() {
						// get input name
						var childName = jQuery(this).attr('name');
						// get input control and hide it
						inspector.form.getControl(childName).hide();
					});

					if (value == 'rect') {
						inspector.form.getControl('shapeDefaultBgColor').show();
						inspector.form.getControl('shapeDefaultWidth').show();
						inspector.form.getControl('shapeDefaultHeight').show();
					} else if (value == 'circle') {
						inspector.form.getControl('shapeDefaultBgColor').show();
						inspector.form.getControl('shapeDefaultRadius').show();
					} else if (value == 'image') {
						inspector.form.getControl('shapeDefaultBackground').show();
					}
				}
			},

			// default background color
			shapeDefaultBgColor: {
				label: 'Background',
				type: 'color',
				default: '#a3a3a3',
				class: 'shape-type-child',
				visible: this.config.shapeType !== 'image',
			},

			// default width
			shapeDefaultWidth: {
				label: 'Width',
				type: 'number',
				min: 10,
				max: 4096,
				default: 100,
				class: 'shape-type-child',
				visible: this.config.shapeType === undefined || this.config.shapeType === 'rect',
			},

			// default height
			shapeDefaultHeight: {
				label: 'Height',
				type: 'number',
				min: 10,
				max: 4096,
				default: 100,
				class: 'shape-type-child',
				visible: this.config.shapeType === undefined || this.config.shapeType === 'rect',
			},

			// default radius
			shapeDefaultRadius: {
				label: 'Radius',
				type: 'number',
				min: 5,
				max: 4096,
				default: 50,
				class: 'shape-type-child',
				visible: this.config.shapeType === 'circle',
			},

			// default background image
			shapeDefaultBackground: {
				label: 'Background',
				type: 'medialist',
				options: UIFileDialog.getMedia(),
				class: 'shape-type-child',
				visible: this.config.shapeType === 'image',
			},

		};

		// end of method
	}

}
