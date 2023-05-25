/**
 * UICanvas class.
 * Base class used to draw the shapes within a canvas.
 */
class UICanvas extends UIObject {

	/**
	 * Class constructor.
	 *
	 * @param 	string 		 selector 	The selector used to access the DOM element.
	 * @param 	UIInspector  inspector  The inspector instance.
	 * @param 	UIToolbar  	 toolbar    The toolbar instance.
	 * @param 	object 		 config 	The canvas initial configuration.
	 */
	constructor(selector, inspector, toolbar, config) {
		super();

		this.index     = 0;
		this.selected  = false;
		this.canvas    = jQuery(selector);
		this.shortcuts = [];
		this.commands  = {};
		this.shapes    = {};
		this.events    = {};
		this.clipboard = [];
		this.state 	   = typeof config === 'object' ? config : {};
		this.inspector = inspector.attach(this);
		this.toolbar   = toolbar;

		// use default status bar (console)
		this.statusBar = new UIStatusBar();

		// create stack actions (max 50 redoable actions per time)
		this.stackActions = new UIState(50);

		// define canvas grid
		this.grid = new UIGrid(this);

		// define static properties
		if (UICanvas.defaultEvents === undefined) {
			UICanvas.defaultEvents = ['click', 'mousedown', 'mouseup'];
		}

		// bind default events
		this.bindEvent(this, this.canvas, UICanvas.defaultEvents);

		// bind window events
		this.bindEvent(this, window, ['keydown', 'mouseup']);

		// prepare canvas with current state
		this.init();
	}

	/**
	 * Sets the canvas status bar.
	 *
	 * @param 	UIStatusBar  bar 	The status bar object.
	 *
	 * @return 	self 	This object to support chaining
	 */
	setStatusBar(bar) {
		if (!(bar instanceof UIStatusBar)) {
			console.error(bar);
			throw 'Invalid [' + typeof bar + '] status bar.';
		}

		this.statusBar = bar;

		return this;
	}

	/**
	 * Binds the specified event(s).
	 *
	 * @param 	mixed 	element  The instance of the element.
	 * @param 	mixed 	source 	 The source for which the events should be registered.
	 * @param 	mixed 	events 	 The list of events to bind (or a single event).
	 *
	 * @return 	void
	 */
	bindEvent(element, source, events) {
		if (typeof events === 'string') {
			events = [events];
		}

		var _this = this;

		// get source ID
		var id = jQuery(source).attr('id');

		if (id === undefined) {
			id = source.toString();
		}

		// define events list if empty
		if (!this.events.hasOwnProperty(id)) {
			this.events[id] = [];
		}

		// iterate all the events
		for (var i = 0; i < events.length; i++) {
			
			// make sure the event is not yet registered
			if (this.events[id].indexOf(events[i]) === -1) {
				// register the event
				jQuery(source).on(events[i], function(e) {
					// dispatch the event to all the subscribed commands
					_this.handleEvent(e, element);
				});

				// mark the event as registered
				this.events[id].push(events[i]);
			}
		}
	}

	/**
	 * Unbinds the specified event(s).
	 *
	 * @param 	mixed 	source 	The source for which the events should be unregistered.
	 * @param 	mixed 	events 	The list of events to unbind (or a single event).
	 * 							If not provided, all the events will be unregistered.
	 *
	 * @return 	void
	 */
	unbindEvent(source, events) {
		if (typeof events === 'string') {
			events = [events];
		}

		var _this = this;

		// get source ID
		var id = jQuery(source).attr('id');

		if (id === undefined) {
			id = source.toString();
		}

		// define events list if empty
		if (!this.events.hasOwnProperty(id)) {
			// do nothing, empty list
			return;
		}

		if (events === undefined) {
			// unbind all events
			events = this.events[id];
		}

		// iterate all the events
		for (var i = 0; i < events.length; i++) {
			// make sure the event is not yet registered
			var index = this.events[id].indexOf(events[i]);	

			if (index !== -1) {
				// unregister the event
				jQuery(source).off(events[i]);

				// remove event from the list
				this.events[id].splice(index, 1);
			}
		}
	}

	/**
	 * Dispatches the event to all the subscribed elements.
	 *
	 * @param 	Event 	event 	 The triggered event.
	 * @param 	mixed 	element  The instance of the element.
	 *
	 * @return 	boolean  True if the whole chain ended properly, otherwise false.
	 */
	handleEvent(event, element) {
		var type = event.type;
		var res;

		// iterate the subscribed elements
		for (var id in this.commands) {
			if (!this.commands.hasOwnProperty(id)) {
				continue;
			}

			// make sure the command can handle the event
			if (this.commands[id][type] !== undefined) {
				// trigger the event
				res = this.commands[id][type](event, element);

				if (res === false) {
					// break all if the command returned false
					event.preventDefault();
					event.stopPropagation();
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Registers a command.
	 *
	 * @param 	UICommand 	cmd  The command handler.
	 *
	 * @return 	void
	 */
	registerCommand(cmd) {
		if (!(cmd instanceof UICommand)) {
			console.error(cmd);
			throw 'Invalid [' + typeof cmd + '] command.';
		}

		// register command
		this.commands[cmd.id] = cmd;

		// initialise command
		this.commands[cmd.id].init(this);

		var _this = this;

		// handle the command activation event
		jQuery(cmd.source).on('click', function(event) {
			if (_this.commands[cmd.id].active) {
				// do not proceed if the command is already active
				return;
			}

			// deactivate any other command
			for (var k in _this.commands) {
				if (!_this.commands.hasOwnProperty(k)) {
					continue;
				}

				if (k != cmd.id && _this.commands[k].active) {
					_this.commands[k].deactivate(_this);
				}
			}
			// activate clicked button
			_this.commands[cmd.id].activate(_this);
			// setup toolbar with command config
			_this.toolbar.build(_this.commands[cmd.id]);
		});
	}

	/**
	 * Unregisters a command.
	 *
	 * @param 	UICommand 	cmd  The command handler.
	 *
	 * @return 	void
	 */
	unregisterCommand(cmd) {
		if (cmd instanceof UICommand) {
			cmd = cmd.id;
		}

		if (this.commands.hasOwnProperty(cmd)) {
			// destroy command before deleting it
			this.commands[cmd.id].destroy();

			// remove command from the list
			delete this.commands[cmd];
		}
	}

	/**
	 * Triggers the given command.
	 *
	 * @param 	string 	id 	The command identifier.
	 *
	 * @return 	void
	 */
	triggerCommand(id) {
		jQuery('#' + id).trigger('click');
	}

	/**
	 * Registers a shortcut.
	 *
	 * @param 	UIShortcut 	shortcut  The shortcut handler.
	 *
	 * @return 	void
	 */
	registerShortcut(shortcut) {
		if (!(shortcut instanceof UIShortcut)) {
			console.error(shortcut);
			throw 'Invalid [' + typeof shortcut + '] shortcut.';
		}

		// initialise shortcut
		shortcut.init(this);

		// register shortcut
		this.shortcuts.push(shortcut);
	}

	/**
	 * Unregisters a shortcut.
	 *
	 * @param 	UIShortcut 	shortcut  The shortcut handler.
	 *
	 * @return 	void
	 */
	unregisterShortcut(shortcut) {
		if (!(shortcut instanceof UIShortcut)) {
			console.error(shortcut);
			throw 'Invalid [' + typeof shortcut + '] shortcut.';
		}

		// search within the shortcuts list
		for (var i = 0; i < this.shortcuts.length; i++) {
			// check if we found the shortcut
			if (this.shortcuts[i] == shortcut) {
				// destroy shortcut before deleting it
				this.shortcuts[i].destroy();

				// remove shortcut from the list
				this.shortcuts.splice(i, 1);

				// stop iterating
				return;
			}
		}
	}

	/**
	 * Draws a shape into the canvas.
	 *
	 * @param 	UIShape  shape 	The shape to draw.
	 * @param 	object 	 shape  The rendering options object.
	 *
	 * @return 	mixed 	 The added element.
	 */
	add(shape, options) {
		if (!(shape instanceof UIShape)) {
			console.error(shape);
			throw 'Invalid [' + typeof shape + '] shape.';
		}

		var id = 'shape-';

		if (shape.id === undefined) {
			// new shape
			this.index++;

		 	id += this.index;
		} else {
			// use the id specified by the shape
			id = shape.id;
		}

		if (options === undefined) {
			options = {};
		}

		options.id = id;

		// render shape
		var el = shape.render(options);
		// push the shape within the canvas
		this.addElement(el);

		// get source
		el = jQuery('#' + id);

		// set constraints
		shape.setConstraints(0, 0, parseInt(this.canvas.attr('width')), parseInt(this.canvas.attr('height')));

		// keep shape reference
		this.shapes[id] = shape;

		// bind shape events
		this.bindEvent(shape, el, UICanvas.defaultEvents);

		// snap only according to the setting of the canvas
		if (this.state.gridSnap) {
			this.snapToGrid(this.shapes[id]);
		}

		/**
		 * @translate
		 */
		this.statusBar.display('Shape added', {translate: true});

		// register ADD action within the stack
		this.stackActions.register(new UIStateActionAdd(this, shape));

		return el;
	}

	/**
	 * Erases the shape from the canvas.
	 *
	 * @param 	mixed  shape 	The shape or a list of shapes to erase.
	 *
	 * @return 	boolean  True on success, otherwise false.
	 */
	remove(shape) {
		var shapes = shape;

		// always treat the shape as an array
		if (!Array.isArray(shape)) {
			shapes = [shape];
		}

		if (!shapes.length) {
			// nothing to remove
			return false;
		}

		for (var i = 0; i < shapes.length; i++) {
			var _shape = shapes[i];

			// search for the given shape
			var k = this.find(_shape);

			if (k === false) {
				// nothing to remove, shape not found
				return false;
			}

			// unbind events
			this.unbindEvent(_shape.getSource(), UICanvas.defaultEvents);

			// remove shape from canvas
			jQuery(_shape.getSource()).remove();

			if (_shape.selected) {
				// unbind selection resize events
				this.unbindEvent('#' + _shape.selected.id, UICanvas.defaultEvents);

				// delete selection from events
				delete this.events[_shape.selected.id];

				// deselect element before remove it
				_shape.select(false);

				// close inspector
				this.inspector.close();
			}

			// remove shape from instance
			delete this.shapes[k];
			// delete shape from events
			delete this.events[k];

		}

		/**
		 * @translate
		 */
		if (shapes.length > 1) {
			this.statusBar.display(UILocale.getInstance().get('%d shapes removed', shapes.length));
		} else {
			this.statusBar.display('Shape removed', {translate: true});
		}

		// register REMOVE action within the stack
		this.stackActions.register(new UIStateActionRemove(this, shape));

		return true;
	}

	/**
	 * Searches the specified shape within the canvas.
	 *
	 * @param 	UIShape  shape 	The shape to search.
	 *
	 * @return 	mixed 	 The shape key on success, otherwise false.
	 */
	find(shape) {
		for (var k in this.shapes) {
			if (!this.shapes.hasOwnProperty(k)) {
				continue;
			}

			if (this.shapes[k] == shape) {
				return k;
			}
		}

		return false;
	}

	/**
	 * Adds an element within the canvas.
	 *
	 * @param 	mixed 	el 	The element to add.
	 *
	 * @return 	void
	 */
	addElement(el) {
		this.canvas[0].appendChild(el);
	}

	/**
	 * Adds a definition of an element within the canvas.
	 *
	 * @param 	mixed 	el 	The element to define.
	 *
	 * @return 	void
	 */
	defElement(el) {
		jQuery(this.canvas).find('defs')[0].appendChild(el);
	}

	/**
	 * Selects the given element.
	 *
	 * @param 	mixed 	 shape 	The shape to select.
	 * @param 	boolean  reset  True to reset the selection of the 
	 * 							other elements, if any.
	 * @param 	boolean  build  True to build the inspector. Default 
	 * 							if not provided.
	 *
	 * @return 	void
	 */
	select(shape, reset, build) {
		if (reset === undefined) {
			reset = true;
		}

		if (build === undefined) {
			build = true;
		}	

		if (reset) {
			for (var k in this.shapes) {
				if (!this.shapes.hasOwnProperty(k)) {
					continue;
				}

				// check if the shape is currently selected
				if (this.shapes[k].selected && this.shapes[k] !== shape) {
					// find shape ID
					var id = this.shapes[k].selected.id;

					// unbind selection resize events
					this.unbindEvent('#' + id, UICanvas.defaultEvents);

					// delete selection from events
					delete this.events[id];

					// deselect shape
					this.shapes[k].select(false);
				}
			}
		}

		if (shape === this || shape === undefined) {
			// select only if no shape is selected
			if (this.getSelection().length == 0) {
				// mark the canvas as selected
				this.selected = true;

				// build inspector with canvas settings
				this.inspector.build(this);
			}

			return;
		}

		// otherwise unselect the canvas
		this.selected = false;

		if (!(shape instanceof UIShape) || shape.selected) {
			// Do not proceed as we may have clicked
			// an element that cannot be selected.
			// Skip also whether the shape is already selected.
				
			if (shape.selected && build) {
				// re-build inspector as we may need to refresh the settings
				this.inspector.build(this.getSelection());
			}
			
			return;
		}

		// get shape selection bounds
		var el = new UISelectionShape(shape);

		// select shape
		shape.select(el);

		// add selection to canvas
		this.canvas[0].appendChild(el.render());

		// get selection source
		var selSource = jQuery('#' + el.id)[0];

		// bind selection resize events
		this.bindEvent(el, selSource, UICanvas.defaultEvents);

		// get selection
		var selection = this.getSelection();

		if (build) {
			// build inspector with selected shapes
			this.inspector.build(selection);
		}

		/**
		 * @translate
		 */
		if (selection.length > 1) {
			this.statusBar.display(UILocale.getInstance().get('%d shapes selected', selection.length));
		} else {
			this.statusBar.display('Shape selected', {translate: true});
		}
	}

	/**
	 * Unselects the given element.
	 *
	 * @param 	mixed 	 shape 	The shape to unselect.
	 *
	 * @return 	void
	 */
	unselect(shape) {
		if (shape === this || shape === undefined) {
			// mark the canvas as unselected
			this.selected = false;
			return;
		}

		if (!(shape instanceof UIShape) || !shape.selected) {
			// Do not proceed as we may have clicked
			// an element that cannot be unselected.
			// Skip also whether the shape is already unselected.
			return;
		}

		// find shape ID
		var id = shape.selected.id;

		// unbind selection resize events
		this.unbindEvent('#' + id, UICanvas.defaultEvents);

		// delete selection from events
		delete this.events[id];

		// deselect shape
		shape.select(false);

		// get all selected shapes
		var all = this.getSelection();

		if (all.length) {
			this.inspector.build(all);
		} else {
			this.inspector.close();
		}
	}

	/**
	 * Selects all the elements.
	 *
	 * @return 	void
	 */
	selectAll() {
		// iterate all the shapes
		for (var k in this.shapes) {
			if (!this.shapes.hasOwnProperty(k)) {
				continue;
			}

			// select the shape without resetting the existing ones
			// and without building the inspector
			this.select(this.shapes[k], false, false);
		}

		// get selection
		var selected = this.getSelection();

		if (selected.length) {
			// build inspector only if we selected something
			this.inspector.build(selected);
		}
	}

	/**
	 * Returns a list of all the selected elements.
	 *
	 * @return 	array
	 */
	getSelection() {
		var tmp = [];

		for (var k in this.shapes) {
			if (!this.shapes.hasOwnProperty(k)) {
				continue;
			}

			if (this.shapes[k].selected) {
				tmp.push(this.shapes[k]);
			}
		}

		return tmp;
	}

	/**
	 * Checks if the given element is selected.
	 *
	 * @param 	mixed 	The element to check.
	 *
	 * @return 	boolean
	 */
	isSelected(element) {
		if (element === this) {
			return this.selected;
		}

		// get selected elements and check if the 
		// instance is contained within the list
		return this.getSelection().indexOf(element) !== -1;
	}

	/**
	 * Enables the multi-selection feature by creating a new instance
	 * only if it doesn't exist yet.
	 *
	 * @param 	integer  x 	The selection left position.
	 * @param 	integer  y 	The selection top position.
	 * @param 	integer  w 	The selection width.
	 * @param 	integer  h 	The selection height.
	 *
	 * @return 	void
	 */
	enableMultiSelection(x, y, w, h) {
		if (!this.selectionRect) {
			// create instance
			this.selectionRect = new UISelectionRect(x, y, w, h);

			// get rendering element
			var el = this.selectionRect.render();

			// push element within the canvas
			this.canvas[0].appendChild(el);
		}
	}

	/**
	 * Disables the multi-selection feature by deleting the created instance, if any.
	 *
	 * @return 	void
	 */
	disableMultiSelection() {
		if (this.selectionRect) {
			// destory canvas selection, if any
			this.selectionRect.destroy();
			// delete instance
			delete this.selectionRect;
		}
	}

	/**
	 * Returns the multi-selection instance.
	 *
	 * @return 	mixed 	The instance if exists, otherwise null.
	 */
	getMultiSelection() {
		return this.selectionRect ? this.selectionRect : null;
	}

	/**
	 * Checks if multi-selection is currently active.
	 *
	 * @return 	boolean  True if active, otherwise false.
	 */
	hasMultiSelection() {
		return this.getMultiSelection() !== null;
	}

	/**
	 * Copies the provided elements.
	 *
	 * @param 	mixed 	shapes 	A list of shapes to copy. Leave empty to
	 * 							copy all the selected shapes.
	 *
	 * @return 	void
	 */
	copy(shapes) {
		// use selected shapes if not provided
		if (shapes === undefined) {
			shapes = this.getSelection();
		}

		if (!Array.isArray(shapes) || !shapes.length) {
			// nothing to copy
			return;
		}

		// copy the shapes within the clipboard
		this.clipboard = shapes;

		/**
		 * @translate
		 */
		if (shapes.length > 1) {
			this.statusBar.display(UILocale.getInstance().get('%d shapes copied', shapes.length));
		} else {
			this.statusBar.display('Shape copied', {translate: true});
		}
	}

	/**
	 * Pastes the elements from clipboard to the canvas.
	 *
	 * @param 	mixed 	shapes 	A list of shapes to paste. Leave empty to
	 * 							paste the shapes within the clipboard.
	 *
	 * @return 	array 	The pasted elements.
	 */
	paste(shapes) {
		// use selected shapes if not provided
		if (shapes === undefined) {
			shapes = this.clipboard;
		}

		if (!Array.isArray(shapes) || !shapes.length) {
			// nothing to paste
			return;
		}

		// freeze stack actions
		this.stackActions.freeze();

		// deselect all shapes
		this.select();

		var tmp = [];

		// iterate all the shapes to paste
		for (var i = 0; i < shapes.length; i++) {
			// obtain a copy of the shape
			var clone = Object.cloneInstance(shapes[i]);

			// move a bit the clone
			clone.addX(40).addY(40);

			// add new shape into the canvas
			this.add(clone);

			// auto-select new shape without resetting the selection
			this.select(clone, false, false);

			tmp.push(clone);
		}

		// get new selection
		var selection = this.getSelection();

		// unfreeze stack actions and create a new command
		// to undo all the added shapes at once
		this.stackActions.unfreeze();
		this.stackActions.register(new UIStateActionAdd(this, selection));

		// build inspector
		this.inspector.build(selection);

		/**
		 * @translate
		 */
		if (shapes.length > 1) {
			this.statusBar.display(UILocale.getInstance().get('%d shapes pasted', shapes.length));
		} else {
			this.statusBar.display('Shape pasted', {translate: true});
		}

		return tmp;
	}

	/**
	 * Returns the shapes that intersect the specified rectangle.
	 *
	 * @param 	integer  x 	The rectangle x position.
	 * @param 	integer  y 	The rectangle y position.
	 * @param 	integer  w 	The rectangle width.
	 * @param 	integer  h 	The rectangle height.
	 *
	 * @return 	array 	 A list of shapes.
	 */
	getIntersection(x, y, w, h) {
		if (w === undefined) {
			w = 0;
		}

		if (h === undefined) {
			h = 0;
		}

		var arr = [];

		// iterate the shapes
		for (var k in this.shapes) {
			if (!this.shapes.hasOwnProperty(k)) {
				continue;
			}

			// check if the current shape intersects the rectangle
			if (this.shapes[k].intersect(x, y, w, h)) {
				// push the shape within the list
				arr.push(this.shapes[k]);
			}
		}

		return arr;
	}

	/**
	 * Snaps the selected items to the grid.
	 *
	 * @param 	mixed 	shapes 	A list of shapes (or a single item) to rearrange.
	 * 							If not specified, all the shapes will be rearranged.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	snapToGrid(shapes) {
		// check if shape is an array of objects
		if (shapes && !Array.isArray(shapes)) {
			// create an array
			shapes = [shapes];
		}

		// get grid size
		var gridSize = this.grid.getSize();

		// iterate shapes
		for (var k in this.shapes) {
			if (!this.shapes.hasOwnProperty(k)) {
				continue;
			}

			var shape = this.shapes[k];

			// check if the current shape needs to be rearranged
			if (shapes === undefined || shapes.indexOf(shape) !== -1) {
				shape.setX(Math.round(shape.x / gridSize) * gridSize);
				shape.setY(Math.round(shape.y / gridSize) * gridSize);

				shape.setWidth(Math.round(shape.w / gridSize) * gridSize);
				shape.setHeight(Math.round(shape.h / gridSize) * gridSize);
			}
		}

		return this;
	}

	/**
	 * Snaps the selected items to the constraints.
	 * Used to snap the selected shapes (and the other selected) to
	 * the constraints of other shapes that are within the same axis.
	 *
	 * @param 	array 	 list 	 A list of shapes to rearrange.
	 * @param 	string 	 action  The snap action (move or resize).
	 *
	 * @return 	self 	 This object to support chaining.
	 */
	snapToConstraints(list, action) {
		// the threshold to use to bind the shape
		var threshold = this.state.constraintsAccuracy ? this.state.constraintsAccuracy : 15;

		// always clear existing constraints
		this.clearConstraints();

		for (var index = 0; index < list.length; index++) {
			// fetch current shape
			var shape = list[index];

			// init snaps
			var snaps = {};

			// iterate shapes
			for (var k in this.shapes) {
				if (!this.shapes.hasOwnProperty(k)) {
					continue;
				}

				var tmp = this.shapes[k];

				var ctr = {source: tmp, val: null};

				// make sure the shape is not within the list
				if (list.indexOf(tmp) === -1) {

					// check for X axis
					// if (shape.x - threshold <= tmp.x && tmp.x <= shape.x + threshold) {
					if (Math.abs(shape.x - tmp.x) <= threshold) {

						if (action == 'move' || action.match(/resize-[ns]?w/)) {
							// snap X to X position
							ctr.val = tmp.x;
							snaps.x = Object.assign({}, ctr);

							if (action != 'move') {
								// adjust width too
								ctr.val = shape.w + shape.x - ctr.val;
								snaps.w = Object.assign({}, ctr);
							}
						}

					// } else if (shape.x - threshold <= tmp.x + tmp.w && tmp.x + tmp.w <= shape.x + threshold) {
					} else if (Math.abs(shape.x - (tmp.x + tmp.w)) <= threshold) {

						if (action == 'move' || action.match(/resize-[ns]?w/)) {
							// snap X to X + WIDTH position
							ctr.val = tmp.x + tmp.w;
							snaps.x = Object.assign({}, ctr);

							if (action != 'move') {
								// adjust width too
								ctr.val = shape.w + shape.x - ctr.val;
								snaps.w = Object.assign({}, ctr);
							}
						}

					}

					// if ((shape.x + shape.w) - threshold <= tmp.x && tmp.x <= shape.x + shape.w + threshold) {
					if (Math.abs((shape.x + shape.w) - tmp.x) <= threshold) {
						
						if (action == 'move') {
							// snap X + WIDTH to X
							ctr.val = tmp.x - shape.w;
							snaps.x = Object.assign({}, ctr);
						} else if (action.match(/^resize/)) {
							// snap WIDTH to X
							ctr.val = Math.abs(tmp.x - shape.x);
							snaps.w = Object.assign({}, ctr);
						}

					// } else if ((shape.x + shape.w) - threshold <= tmp.x + tmp.w && tmp.x + tmp.w <= shape.x + shape.w + threshold) {
					} else if (Math.abs((shape.x + shape.w) - (tmp.x + tmp.w)) <= threshold) {					
						
						if (action == 'move') {
							// snap X + WIDTH to X + WIDTH
							ctr.val = tmp.x - shape.w + tmp.w;
							snaps.x = Object.assign({}, ctr);
						} else if (action.match(/^resize/)) {
							// snap WIDTH to X + WIDTH
							ctr.val = Math.abs((tmp.x + tmp.w) - shape.x);
							snaps.w = Object.assign({}, ctr);
						}

					}

					// check for Y axis
					// if (shape.y - threshold <= tmp.y && tmp.y <= shape.y + threshold) {
					if (Math.abs(shape.y - tmp.y) <= threshold) {
						
						if (action == 'move' || action.match(/resize-n[we]?/)) {
							// snap to Y position
							ctr.val = tmp.y;
							snaps.y = Object.assign({}, ctr);

							if (action != 'move') {
								// adjust height too
								ctr.val = shape.h + shape.y - ctr.val;
								snaps.h = Object.assign({}, ctr);
							}
						}

					// } else if (shape.y - threshold <= tmp.y + tmp.h && tmp.y + tmp.h <= shape.y + threshold) {
					} else if (Math.abs(shape.y - (tmp.y + tmp.h)) <= threshold) {

						if (action == 'move' || action.match(/resize-n[we]?/)) {
							// snap to Y + HEIGHT position
							ctr.val = tmp.y + tmp.h;
							snaps.y = Object.assign({}, ctr);

							if (action != 'move') {
								// adjust height too
								ctr.val = shape.h + shape.y - ctr.val;
								snaps.h = Object.assign({}, ctr);
							}
						}

					}

					// if ((shape.y + shape.h) - threshold <= tmp.y && tmp.y <= shape.y + shape.h + threshold) {
					if (Math.abs((shape.y + shape.h) - tmp.y) <= threshold) {

						if (action == 'move') {
							// snap Y + HEIGHT to Y
							ctr.val = tmp.y - shape.h;
							snaps.y = Object.assign({}, ctr);
						} else if (action.match(/^resize/)) {
							// snap HEIGHT to Y
							ctr.val = Math.abs(tmp.y - shape.y);
							snaps.h = Object.assign({}, ctr);
						}

					// } else if ((shape.y + shape.h) - threshold <= tmp.y + tmp.h && tmp.y + tmp.h <= shape.y + shape.h + threshold) {
					} else if (Math.abs((shape.y + shape.h) - (tmp.y + tmp.h)) <= threshold) {

						if (action == 'move') {
							// snap Y + HEIGHT to Y + HEIGHT
							ctr.val = tmp.y - shape.h + tmp.h;
							snaps.y = Object.assign({}, ctr);
						} else if (action.match(/^resize/)) {
							// snap HEIGHT to Y + HEIGHT
							ctr.val = Math.abs((tmp.y + tmp.h) - shape.y);
							snaps.h = Object.assign({}, ctr);
						}

					}

				}
			}

			var constraints = [];
			
			// check X
			if (snaps.x !== undefined) {
				shape.setX(snaps.x.val);

				constraints.push(snaps.x.source);
			}

			// check Y
			if (snaps.y !== undefined) {
				shape.setY(snaps.y.val);

				constraints.push(snaps.y.source);
			}

			// check WIDTH
			if (snaps.w !== undefined) {
				shape.setWidth(snaps.w.val);

				constraints.push(snaps.w.source);
			}

			// check HEIGHT
			if (snaps.h !== undefined) {
				shape.setHeight(snaps.h.val);

				constraints.push(snaps.h.source);
			}

			// add constraints only after altering all the attributes
			for (var i = 0; i < constraints.length; i++) {
				this.addConstraint(shape, constraints[i]);
			}

		}

		return this;
	}

	/**
	 * Adds a line to mark the constraints between the given shapes.
	 *
	 * @param 	UIShape  a
	 * @param 	UIShape  b
	 *
	 * @return 	void
	 */
	addConstraint(a, b) {
		// create constraint
		var constraint = new UIConstraint(a, b);

		// render constraint
		var g = constraint.render();

		// add constraing to canvas
		this.canvas[0].appendChild(g);
	}

	/**
	 * Clears all the existing constraints.
	 *
	 * @return 	void
	 */
	clearConstraints() {
		jQuery('.line-constraint').remove();
	}

	/**
	 * Returns the mouse position according to the canvas bounds.
	 * The position is calculated by subtracting the offset of the canvas
	 * from the clientX and clientY properties.
	 *
	 * @param 	Event 	event 	The mouse event.
	 *
	 * @return 	object 	The mouse position.
	 */
	getMousePosition(event) {
		if (this.canvasScrollParent === undefined) {
			// register canvas scroll parent if not set
			this.canvasScrollParent = this.canvas.scrollParent();
		}

		if (this.canvasOffset === undefined) {
			// register canvas offset only once as we need to use
			// the absolute value by ignoring internal scroll
			this.canvasOffset = this.canvas.offset();

			// get current left and top scroll
			var scrollLeft = this.canvasScrollParent.scrollLeft();
			var scrollTop  = this.canvasScrollParent.scrollTop();

			// if the map is horizontally scrolled, we need
			// to adjust the canvas left offset as we probably have a
			// negative value
			if (scrollLeft > 0) {
				this.canvasOffset.left += scrollLeft;
			}

			// if the map is vertically scrolled, we need
			// to adjust the canvas top offset as we probably have a
			// negative value
			if (scrollTop > 0) {
				this.canvasOffset.top += scrollTop;
			}
		}

		return {
			x: event.clientX - this.canvasOffset.left + this.canvasScrollParent.scrollLeft(),
			y: event.clientY - this.canvasOffset.top + this.canvasScrollParent.scrollTop(),
		};
	}

	/**
	 * Checks if the design area has changed and requires to be saved.
	 *
	 * @return 	boolean
	 */
	hasChanged() {
		return this.areThereChanges === true;
	}

	/**
	 * Marks the design area as changed.
	 *
	 * @return 	void
	 */
	changed() {
		this.areThereChanges = true;
	}

	/**
	 * Marks the pending changes as applied.
	 *
	 * @return 	void
	 */
	commitChanges() {
		this.areThereChanges = false;
	}

	/**
	 * Prepares the canvas with the current internal state.
	 *
	 * @return 	void
	 */
	init() {
		// init canvas
		if (this.state.width) {
			this.canvas.attr('width', this.state.width);
		}

		if (this.state.height) {
			this.canvas.attr('height', this.state.height);
		}

		if (this.state.showGrid) {
			this.grid.enable();
		} else {
			this.grid.disable();
		}

		if (this.state.gridSize) {
			this.grid.setSize(this.state.gridSize);
		}

		if (this.state.gridColor) {
			this.grid.setStrokeColor('#' + this.state.gridColor);
		}

		if (this.state.background == 'color') {
			this.canvas.css('background-color', '#' + this.state.bgColor);
		} else if (this.state.background == 'image') {
			this.canvas.css('background-image', 'url(' + this.state.bgImage + ')');

			if (this.state.bgImageMode == 'repeat') {
				this.canvas.css('background-repeat', 'repeat');
			} else if (this.state.bgImageMode == 'repeatx') {
				this.canvas.css('background-repeat', 'repeat-x');
			} else if (this.state.bgImageMode == 'repeaty') {
				this.canvas.css('background-repeat', 'repeat-y');
			} else if (this.state.bgImageMode == 'cover') {
				this.canvas.css('background-size', 'cover');
			} else {
				this.canvas.css('background-repeat', 'no-repeat');
			}
		} else {
			this.canvas.css('background', 'none');
		}
	}

	/**
	 * Adds an image into the canvas, which could be used
	 * by any field to display/select a collection of media files.
	 *
	 * @param 	mixed 	media 	An image or a list of images.
	 *
	 * @return 	self 	This object to support chaining.
	 *
	 * @deprecated  Use UIFileDialog::addMedia() instead.
	 */
	addImage(media) {
		UIFileDialog.addMedia(media);

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

		return {

			// CANVAS FIELDSET

			// canvas width
			width: {
				label: 'Width',
				type: 'number',
				min: 256,
				max: 4096,
				default: 2048,
				fieldset: 'Canvas',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('gridSize').data;

					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					// update canvas width
					jQuery(_this.canvas).attr('width', value);

					// update height too if proportional size is checked
					if (inspector.form.getField('propSize').getValue()) {
						inspector.getField('height').val(value);
						jQuery(_this.canvas).attr('height', value);
					}

					// refresh shapes constraints
					for (var k in _this.shapes) {
						if (!_this.shapes.hasOwnProperty(k)) {
							continue;
						}

						_this.shapes[k].setConstraints(0, 0, parseInt(_this.canvas.attr('width')), parseInt(_this.canvas.attr('height')));
					}
				},
			},

			// canvas height
			height: {
				label: 'Height',
				type: 'number',
				min: 256,
				max: 4096,
				default: 2048,
				fieldset: 'Canvas',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('gridSize').data;

					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					// update canvas height
					jQuery(_this.canvas).attr('height', value);

					// update width too if proportional size is checked
					if (inspector.form.getField('propSize').getValue()) {
						inspector.getField('width').val(value);
						jQuery(_this.canvas).attr('width', value);
					}

					// refresh shapes constraints
					for (var k in _this.shapes) {
						if (!_this.shapes.hasOwnProperty(k)) {
							continue;
						}

						_this.shapes[k].setConstraints(0, 0, parseInt(_this.canvas.attr('width')), parseInt(_this.canvas.attr('height')));
					}
				},
			},

			// proportional size
			propSize: {
				label: 'Proportional Size',
				description: 'When checked, the width and height will have always the same value.',
				type: 'checkbox',
				checked: false,
				fieldset: 'Canvas',
			},

			// add separator
			separator1: {
				type: 'separator',
				fieldset: 'Canvas',
			},

			// background image
			background: {
				label: 'Background',
				type: 'list',
				options: {
					none: 'None',
					image: 'Image',
					color: 'Color',
				},
				default: 'none',
				translate: true,
				fieldset: 'Canvas',
				onChange: function(value, inspector) {
					// hide any background child
					jQuery('.bg-child').each(function() {
						// get input name
						var childName = jQuery(this).attr('name');
						// get input control and hide it
						inspector.form.getControl(childName).hide();
					});

					// remove background from canvas
					jQuery(_this.canvas).css('background', 'none');

					if (value == 'image') {
						inspector.form.getControl('bgImage').show();
						inspector.form.getControl('bgImageMode').show();

						// trigger change to display last image set
						inspector.form.getField('bgImage').trigger('change', inspector);
					} else if (value == 'color') {
						inspector.form.getControl('bgColor').show();

						// trigger change to display last color set
						inspector.form.getField('bgColor').trigger('change', inspector);
					}
				},
			},

			// bg image repeat
			bgImageMode: {
				label: 'Mode',
				type: 'list',
				options: {
					none: 'None',
					repeat: 'Repeat',
					repeatx: 'Repeat Horizontally',
					repeaty: 'Repeat Vertically',
					cover: 'Cover',
				},
				class: 'bg-child',
				visible: this.state.background == 'image',
				default: 'repeat',
				translate: true,
				fieldset: 'Canvas',
				onChange: function(value, inspector) {
					// trigger image change
					inspector.form.getField('bgImage').trigger('change', inspector);
				},
			},

			// bg image
			bgImage: {
				label: 'Image',
				description: 'Select the background image you need to use from the collection below.',
				type: 'media',
				options: UIFileDialog.getMedia(),
				searchbar: true,
				upload: true,
				class: 'bg-child',
				visible: this.state.background == 'image',
				fieldset: 'Canvas',
				onChange: function(value, inspector) {
					if (!value) {
						// remove background from canvas
						jQuery(_this.canvas).css('background', 'none');
						return;
					}

					jQuery(_this.canvas).css('background-image', 'url(' + value + ')');

					// get image mode
					var mode = inspector.getField('bgImageMode').val();

					jQuery(_this.canvas).css('background-size', 'initial');
					jQuery(_this.canvas).css('background-repeat', 'initial');

					if (mode == 'repeat') {
						jQuery(_this.canvas).css('background-repeat', 'repeat');
					} else if (mode == 'repeatx') {
						jQuery(_this.canvas).css('background-repeat', 'repeat-x');
					} else if (mode == 'repeaty') {
						jQuery(_this.canvas).css('background-repeat', 'repeat-y');
					} else if (mode == 'cover') {
						jQuery(_this.canvas).css('background-size', 'cover');
					} else {
						jQuery(_this.canvas).css('background-repeat', 'no-repeat');
					}
				},
			},

			// bg color
			bgColor: {
				label: 'Color',
				type: 'color',
				class: 'bg-child',
				visible: this.state.background == 'color',
				default: 'FFFFFF',
				fieldset: 'Canvas',
				onChange: function(value, inspector) {
					// use color
					jQuery(_this.canvas).css('background-color', '#' + value);
				},
			},

			// LAYOUT FIELDSET

			// display grid
			showGrid: {
				label: 'Display Grid',
				type: 'checkbox',
				checked: false,
				fieldset: 'Layout',
				onChange: function(value, inspector) {
					if (value) {
						// trigger grid size change in order to update the value set
						inspector.form.getField('gridSize').trigger('change', inspector);

						_this.grid.enable();
					} else {
						_this.grid.disable();

						// always disable gridsnap in case grid is unchecked,
						// then trigger change to toggle related fields
						inspector.form.getField('gridSnap').setValue(false);
						inspector.form.getField('gridSnap').trigger('change', inspector);
					}

					jQuery('.grid-child').each(function() {
						// get input name
						var childName = jQuery(this).attr('name');
						// get input control and hide it
						var control = inspector.form.getControl(childName);

						if (value) {
							control.show();
						} else {
							control.hide();
						}
					});
				}
			},

			// grid rect size
			gridSize: {
				label: 'Size',
				type: 'number',
				class: 'grid-child',
				visible: this.state.showGrid ? true : false,
				min: 10,
				max: 4096,
				default: 40,
				fieldset: 'Layout',
				onChange: function(value, inspector) {
					var fieldData = inspector.form.getField('gridSize').data;

					value = Math.abs(value);
					value = Math.max(fieldData.min, value);
					value = Math.min(fieldData.max, value);

					_this.grid.setSize(value);

					// trigger grid snap change as we need to rearrange the shapes
					inspector.form.getField('gridSnap').trigger('change', inspector);
				},
			},

			// grid border color
			gridColor: {
				label: 'Color',
				type: 'color',
				class: 'grid-child',
				visible: this.state.showGrid ? true : false,
				default: '808080',
				fieldset: 'Layout',
				onChange: function(value, inspector) {
					// use color
					_this.grid.setStrokeColor('#' + value);
				},
			},

			// align to grid
			gridSnap: {
				label: 'Snap to Grid',
				description: 'Enable this value to align/snap the shapes to the grid.',
				type: 'checkbox',
				checked: false,
				class: 'grid-child',
				visible: this.state.showGrid ? true : false,
				fieldset: 'Layout',
				onChange: function(value, inspector) {
					// check if the shapes should be rearranged
					if (value) {
						_this.snapToGrid();

						// hide shape constraints
						inspector.form.getField('shapeConstraints').setValue(false);
						inspector.form.getControl('shapeConstraints').hide();
					} else {
						// show shape constraints
						inspector.form.getControl('shapeConstraints').show();
					}

					inspector.form.getField('shapeConstraints').trigger('change', inspector);
				},
			},

			// shape constraints
			shapeConstraints: {
				label: 'Enable Constraints',
				description: 'Constraints help shapes aligning on the grid.',
				type: 'checkbox',
				checked: false,
				visible: this.state.gridSnap ? false : true,
				fieldset: 'Layout',
				onChange: function(value, inspector) {
					// show/hide children
					jQuery('.constraints-child').each(function() {
						// get input name
						var childName = jQuery(this).attr('name');
						// get input control and hide it
						var control = inspector.form.getControl(childName);

						if (value) {
							control.show();
						} else {
							control.hide();
						}
					});
				},
			},

			// shape constraints accuracy
			constraintsAccuracy: {
				label: 'Constraints Accuracy',
				description: 'The lower the accuracy, the easier the alignment of the shapes.',
				type: 'list',
				options: {
					6: 'High',
					15: 'Normal',
					24: 'Low',
				},
				default: 15,
				class: 'constraints-child',
				visible: this.state.shapeConstraints && !this.state.gridSnap ? true : false,
				translate: true,
				fieldset: 'Layout',
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
		return this.state;
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
		// validate configuration before save
		if (!data.showGrid) {
			// unset grid snap to avoid rearranging
			// the shapes to a non visible grid
			data.gridSnap = false;
		}

		if (data.gridSnap) {
			// unset shape constraints when grid snap is on
			data.shapeConstraints = false;
		}

		// update configuration
		this.state = data;
	}

	/**
	 * Method used to return a serialized version
	 * of the object so that it can be saved externally.
	 *
	 * @return 	string
	 */
	serialize() {
		var object = {
			canvas: 	this.getConfig(),
			shapes: 	[],
			commands: 	{},
		};

		// iterate all shapes
		for (var k in this.shapes) {
			if (!this.shapes.hasOwnProperty(k)) {
				continue;
			}

			// get shape class name
			var className = this.shapes[k].constructor.name;

			// get shape configuration
			var shape = this.shapes[k].getConfig();
			// inject shape class name
			shape.class = className;
			shape.tmpId = k;

			// push shape within the list
			object.shapes.push(shape);
		}

		// iterate all commands
		for (var i in this.commands) {
			if (!this.commands.hasOwnProperty(i)) {
				continue;
			}

			// get commands class name
			var className = this.commands[i].constructor.name;

			// register command
			object.commands[className] = this.commands[i].getConfig();
		}

		return JSON.stringify(object);
	}

	/**
	 * Method used to unserialize the provided string
	 * and to populate the object.
	 *
	 * @param 	mixed 	json 	The JSON string or the plain object.
	 */
	unserialize(json) {
		// parse JSON string
		var object = json;

		if (typeof object !== 'object') {
			try {
				object = JSON.parse(json);
			} catch (err) {
				console.log(err, json);
				object = {};
			}
		}

		// freeze undo/redo stack
		this.stackActions.freeze();
		// suppress status bar messages
		this.statusBar.disable();

		// populate canvas configuration
		if (typeof object.canvas === 'object') {
			// restore canvas settings
			this.save(object.canvas);
			this.init();
		}

		// populate commands configuration
		if (typeof object.commands === 'object') {
			// iterate all commands
			for (var i in this.commands) {
				if (!this.commands.hasOwnProperty(i)) {
					continue;
				}

				// get command class name
				var className = this.commands[i].constructor.name;

				// check if we have a configuration object
				if (typeof object.commands[className] === 'object') {
					// restore command settings
					this.commands[i].save(object.commands[className]);
				}
			}
		}

		// populate shapes configuration
		if (Array.isArray(object.shapes)) {

			// itarate all rects/circles/images
			for (var i = 0; i < object.shapes.length; i++) {

				var data = object.shapes[i];
				var shape;

				if (data.class === undefined) {
					// use default class is not set
					data.class = 'UIShapeRect';
				}

				// instantiate shape
				switch (data.class) {
					case 'UIShapeRect':
						shape = new UIShapeRect();
						break;

					case 'UIShapeCircle':
						shape = new UIShapeCircle();
						break;

					case 'UIShapeImage':
						shape = new UIShapeImage();
						break;

					default:
						console.warn('Shape [' + className + '] not supported');
				}

				if (shape) {
					// draw shape
					this.add(shape);

					// update shape properties
					shape.save(data);
				}
			}
			
		}

		// enable status bar messages
		this.statusBar.enable();
		// unfreeze undo/redo stack
		this.stackActions.unfreeze();
	}
}
