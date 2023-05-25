/**
 * UICommandSelect class.
 * This command is used to select and manipulate the shapes.
 * By clicking the canvas or a shape, the related configuration
 * will be inspected. Shapes can be moved, resized and rotated.
 * Keep SHIFT pressed to select multiple shapes at once.
 * It is also possible to use the keyboard arrows to move all the
 * selected shapes.
 */
class UICommandSelect extends UICommand {

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

		// flag used to check whether the mouse was pressed or clicked
		this.isMousePressed = false;
		// flag used to check if we should unselect the clicked element
		this.shouldUnselect = false;
	}

	/**
	 * @override
	 * Returns the command title.
	 *
	 * @return string
	 */
	getTitle() {
		return 'Selection';
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
		return ['s'];
	}

	/**
	 * Selects the clicked target.
	 *
	 * @param 	Event 	event
	 * @param 	mixed 	element
	 *
	 * @return 	boolean
	 */
	mousedown(event, element) {
		if (!this.active) {
			// ignore if inactive
			return;
		}

		// define action
		var action = 'move';

		// check if we clicked within the selection
		if (element instanceof UISelection) {
			// check if we should resize the element
			var _act = this.checkAction(event, element);

			if (_act) {
				// overwrite action
				action = _act;
			}

			// use the element wrapped by the selection
			element = element.shape;
		}

		// check if we should reset the selection
		var resetSelection = !event.ctrlKey && !event.metaKey && !event.shiftKey;

		if (resetSelection) {
			// remove selection from any other target
			jQuery('.element-selected').removeClass('element-selected');
		}

		// mark the element as selected
		jQuery(event.currentTarget).addClass('element-selected');

		// attach focus to this command
		this.source.focus();
		this.canvas.inspector.loseFocus();
		this.canvas.toolbar.loseFocus();

		if (!event.shiftKey || !this.canvas.isSelected(element)) {
			// select the element if it is not yet selected
			// or if we haven't pressed SHIFT key
			this.canvas.select(element, resetSelection);
		} else {
			this.shouldUnselect = true;
		}

		// change action if element is the canvas
		if (element instanceof UICanvas) {
			action = 'selection';
		}

		if (element instanceof UIShape || element instanceof UICanvas) {
			
			// make sure the left button was pressed
			if (event.which == 1) {
				// define origin of the mouse
				this.origin = {
					action: action,
					mouse: this.canvas.getMousePosition(event),
					source: element,
					initial: Object.assign({}, element),
					shapes: {},
				};

				var _this = this;

				// Delay binding as the mouse may trigger move event while pressing it.
				this.mousemoveTimeout = setTimeout(function() {
					// change cursor on move
					if (action == 'move') {
						jQuery(event.currentTarget).css('cursor', 'grabbing');
					}

					if (element instanceof UIShape) {
						// instantiate UNDO/REDO action
						_this.createUndoRedoAction();
					} else if (element instanceof UICanvas) {
						// turn on multi-selection feature
						_this.canvas.enableMultiSelection(_this.origin.mouse.x, _this.origin.mouse.y);
					}
				
					// add support for mousemove event as long as the mouse remains down
					_this.canvas.bindEvent(element, event.currentTarget, 'mousemove');

					// bind also mousemove on canvas
					_this.canvas.bindEvent(_this.canvas, _this.canvas.canvas, 'mousemove');

					// mouse was pressed
					_this.isMousePressed = true;
					// waited too many time, do not unselect anymore
					_this.shouldUnselect = false;
				}, 128);
			}
		}

		// always stop propagating the event
		return false;
	}

	/**
	 * Handles mouse move event for resizing and dragging.
	 *
	 * @param 	Event 	event
	 * @param 	mixed 	element
	 *
	 * @return 	void
	 */
	mousemove(event, element) {
		if (!this.active) {
			// ignore if inactive
			return;
		}

		// make sure we are not dragging by shape name
		if (event.target.tagName == 'text') {
			// do not handle move event
			return false;
		}

		// get the mouse position related to the canvas
		var mouse = this.canvas.getMousePosition(event);
			
		// handle multi-selection
		if (this.origin.action == 'selection') {
			// get multi selection rectangle
			var selectionRect = this.canvas.getMultiSelection();

			selectionRect.w = mouse.x - selectionRect.x;
			selectionRect.h = mouse.y - selectionRect.y;

			selectionRect.refresh();

			// always stop propagating the event
			return false;
		}

		if (!(element instanceof UIShape)) {
			// we are probably moving the mouse within the canvas,
			// so we need to update the last fetched element
			element = this.origin.source;
		}

		// get selected elements
		var selected = this.canvas.getSelection();

		// iterate the selected shapes
		for (var i = 0; i < selected.length; i++) {
			if (!this.origin.shapes.hasOwnProperty(i)) {
				// if not set, keep origin for shape elements
				this.origin.shapes[i] = {
					x: selected[i].x,
					y: selected[i].y,
					w: selected[i].w,
					h: selected[i].h,
				};
			}

			if (this.origin.action == 'move') {
				
				// update the position of this shape proportionally
				selected[i].setX(this.origin.shapes[i].x + (mouse.x - this.origin.mouse.x))
					.setY(this.origin.shapes[i].y + (mouse.y - this.origin.mouse.y));

			} else if (this.origin.action.indexOf('resize-') === 0) {

				// update the width of this shape proportionally (SE, NE or EAST)
				if (this.origin.action == 'resize-se' || this.origin.action == 'resize-e' || this.origin.action == 'resize-ne') {
					selected[i].setWidth(this.origin.shapes[i].w + (mouse.x - this.origin.mouse.x));
				}

				// update the height of this shape proportionally (SE, SW or SOUTH)
				if (this.origin.action == 'resize-se' || this.origin.action == 'resize-s' || this.origin.action == 'resize-sw') {
					selected[i].setHeight(this.origin.shapes[i].h + (mouse.y - this.origin.mouse.y));
				}

				// update the x position and width of this shape proportionally (SW, NW or WEST)
				if (this.origin.action == 'resize-sw' || this.origin.action == 'resize-nw' || this.origin.action == 'resize-w') {
					// calc position difference
					var diff = this.origin.mouse.x - mouse.x;
					// decrease x position by the calculated difference
					selected[i].setX(this.origin.shapes[i].x - diff);
					// increase the width by the calculated difference
					selected[i].setWidth(this.origin.shapes[i].w + diff);
				}

				// update the y position and height of this shape proportionally (NW, NE or NORTH)
				if (this.origin.action == 'resize-nw' || this.origin.action == 'resize-ne' || this.origin.action == 'resize-n') {
					// calc position difference
					var diff = this.origin.mouse.y - mouse.y;
					// decrease y position by the calculated difference
					selected[i].setY(this.origin.shapes[i].y - diff);
					// increase the height by the calculated difference
					selected[i].setHeight(this.origin.shapes[i].h + diff);
				}

			} else if (this.origin.action.indexOf('rotate-') === 0) {

				var diffAngle = 0;

				// adjust the resulting degrees by considering the angle
				// of the selected handler
				if (this.origin.action == 'rotate-se') {
					diffAngle = 45;
				} else if (this.origin.action == 'rotate-sw') {
					diffAngle = 135;
				} else if (this.origin.action == 'rotate-nw') {
					diffAngle = 225;
				} else if (this.origin.action == 'rotate-ne') {
					diffAngle = 315;
				}

				// get shape center of origin element, because we cannot
				// calculate the rotation proportionally
				var center = this.origin.source.getCenter();

				// The atan2() method returns the arctangent of the quotient of its arguments, 
				// as a numeric value between PI and -PI radians.
				// The number returned represents the counterclockwise angle in radians
				// between the positive X axis and the point (x, y).
				// Note: With atan2(), the y coordinate is passed as the first argument and 
				// the x coordinate is passed as the second argument.
				var rad = Math.atan2((mouse.y - center.y) * -1, mouse.x - center.x);
				// convert radians in degrees
				var deg = (rad * (180 / Math.PI)) % 360;

				// subtract the degrees found to a full circumference
				// as SVG rotates using a clockwise angle
				deg = Math.round(360 - (deg + diffAngle));

				// update rotation
				selected[i].rotate(deg);
			}
		}

		// snap to grid only according to the setting of the canvas
		if (this.canvas.state.gridSnap) {
			this.canvas.snapToGrid(selected);
		}

		// snap to constraints only according to the settings of the canvas
		if (this.canvas.state.shapeConstraints) {
			this.canvas.snapToConstraints(selected, this.origin.action);
		}

		// refresh inspector data
		this.canvas.inspector.refresh();

		// always stop propagating the event
		return false;
	}

	/**
	 * Handles mouse up event to stop resizing and dragging.
	 *
	 * @param 	Event 	event
	 * @param 	mixed 	element
	 *
	 * @return 	void
	 */
	mouseup(event, element) {
		// check if we are going to define mouse move event
		if (!this.active || !this.mousemoveTimeout) {
			// go ahead, we don't need to handle mouse up here
			return;
		}

		// abort definition
		clearTimeout(this.mousemoveTimeout);
		// detach timeout
		delete this.mousemoveTimeout;

		// clear constraints
		this.canvas.clearConstraints();

		// if we were in group-selection mode, we need to select
		// all the shapes that intersect the selection rectangle
		if (this.origin && this.origin.action == 'selection' && this.canvas.hasMultiSelection()) {
			// get multi selection rectangle bounds
			var bounds = this.canvas.getMultiSelection().getBounds();

			// get shapes that intersect the rectangle
			var intersection = this.canvas.getIntersection(bounds.x, bounds.y, bounds.w, bounds.h);

			// disable multi selection
			this.canvas.disableMultiSelection();

			// check selection mode
			if (this.config.selectionMode == 'reverse') {
				// if we have a reverse selection, we need to 
				// select all the elements that are not contained
				// within the intersection
				var tmp = [];

				// iterate all the shapes in canvas
				for (var k in this.canvas.shapes) {
					if (!this.canvas.shapes.hasOwnProperty(k)) {
						continue;
					}

					// check if the element is contained within the intersection
					if (intersection.indexOf(this.canvas.shapes[k]) === -1) {
						// push shape within the new list
						tmp.push(this.canvas.shapes[k]);
					}
				}

				// reverse intersection
				intersection = tmp;
			}

			if (intersection.length) {
				// auto-select intersected shapes
				for (var i = 0; i < intersection.length; i++) {
					this.canvas.select(intersection[i], false, false);
				}

				// rebuild inspector
				this.canvas.inspector.build(this.canvas.getSelection());
			}
		}
		// check if we should unselect the shape (ignore if the mouse was pressed and not clicked)
		else if (this.origin && this.origin.source && this.shouldUnselect) {
			// try unselecting the element
			this.canvas.unselect(this.origin.source);
		}

		// get all the selected shapes
		var selectedShapes = this.canvas.getSelection();

		// unregister mouse move event as we released the mouse
		for (var i = 0; i < selectedShapes.length; i++) {
			// get shape source
			var source = selectedShapes[i].getSource();
			// unbind event
			this.canvas.unbindEvent(source, 'mousemove');

			// unbind selections too
			this.canvas.unbindEvent('#' + selectedShapes[i].selected.id, 'mousemove');
		}

		// unregister mouse move event from canvas too
		this.canvas.unbindEvent(this.canvas.canvas, 'mousemove');

		// Commit undo/redo action, if any.
		this.commitUndoRedoAction();

		if (this.origin) {
			if (this.origin.source instanceof UIShape) {
				// restore cursor
				jQuery(this.origin.source.getSource()).css('cursor', '');
			}

			// delete origin
			delete this.origin;
		}

		// mouse no more pressed
		this.isMousePressed = false;
		// turn off unselection
		this.shouldUnselect = false;

		// always stop propagating the event
		return false;
	}

	/**
	 * Handles keydown event.
	 * When an arrow is pressed, moves all the current
	 * selected shapes.
	 *
	 * @param 	Event 	event
	 * @param 	mixed 	element
	 *
	 * @return 	void 
	 */
	keydown(event, element) {
		if (this.canvas.inspector.hasFocus() || this.canvas.toolbar.hasFocus()) {
			// canvas doesn't have focus, do not perform any action
			return;
		}

		// check if we pressed an arrow
		if (event.keyCode < 37 || event.keyCode > 40) {
			// do nothing
			return;
		}

		// get all the selected shapes
		var selectedShapes = this.canvas.getSelection();

		// make sure we have something to handle
		if (!selectedShapes.length) {
			// no selection
			return;
		}

		// create undo/redo action
		this.createUndoRedoAction();

		// Find the number of pixel to add/sub.
		// In case the SHIFT key is pressed, use 10 px,
		// otherwise use only 1 px.
		var unit = event.shiftKey ? 10 : 1;

		if (this.canvas.state.gridSnap) {
			// grid snap on, move the tables by the grid size
			unit = this.canvas.grid.getSize();
		}

		for (var i = 0; i < selectedShapes.length; i++) {

			// detect the pressed arrow
			switch (event.keyCode) {
				// left arrow
				case 37:
					// move shape 1px to the left
					selectedShapes[i].subX(unit);
					break;

				// up arrow
				case 38:
					// move shape 1px to the top
					selectedShapes[i].subY(unit);
					break;

				// right arrow
				case 39:
					// move shape 1px to the right
					selectedShapes[i].addX(unit);
					break;

				// down arrow
				case 40:
					// move shape 1px to the bottom
					selectedShapes[i].addY(unit);
					break;
			}

		}

		// refresh inspector values
		this.canvas.inspector.refresh();

		// register undo/redo action
		this.canvas.stackActions.register(this.objectStateAction);
		// trigger changes
		this.canvas.changed();

		// delete temporary property
		delete this.objectStateAction;

		// always stop propagating the event
		return false;
	}

	/**
	 * Checks if we clicked a resize/rotate handler.
	 *
	 * @param 	Event 	 event
	 * @param 	mixed 	 element
	 *
	 * @return 	boolean  True if we should handle resizing/rotation, otherwise false. 
	 */
	checkAction(event, element) {
		// get clicked element class
		var _class = jQuery(event.target).attr('class');

		// find resize handler
		var handler = _class.match(/(resize|rotate)\-([a-z]{1,2})/);
		
		// check if target has a class that starts with resize-
		if (handler) {
			return handler.shift();
		}

		// no resize
		return false;
	}

	/**
	 * Initialiases the undo/redo action for the selected elements.
	 *
	 * @return 	void
	 */
	createUndoRedoAction() {
		var elements = this.canvas.getSelection();
		var states   = elements.map(function(elem) {
			return elem.getConfig();
		});

		this.objectStateAction = new UIStateActionObject(elements, states, this.canvas.inspector);
	}

	/**
	 * Registers the undo/redo action for the selected elements.
	 *
	 * @return 	void
	 */
	commitUndoRedoAction() {
		if (this.objectStateAction && this.origin) {
			// make sure something has changed
			if (this.origin.initial.x != this.origin.source.x
				|| this.origin.initial.y != this.origin.source.y
				|| this.origin.initial.w != this.origin.source.w
				|| this.origin.initial.h != this.origin.source.h
				|| this.origin.initial.rotation != this.origin.source.rotation) {

				// register undo/redo action
				this.canvas.stackActions.register(this.objectStateAction);

				var len = this.objectStateAction.subject.length;

				// update status bar
				if (this.origin.action == 'move') {
					
					/**
					 * @translate
					 */
					if (len > 1) {
						this.canvas.statusBar.display(UILocale.getInstance().get('%d shapes moved', len));
					} else {
						this.canvas.statusBar.display('Shape moved', {translate: true});
					}

				} else if (this.origin.action.indexOf('resize') === 0) {

					/**
					 * @translate
					 */
					if (len > 1) {
						this.canvas.statusBar.display(UILocale.getInstance().get('%d shapes resized', len));
					} else {
						this.canvas.statusBar.display('Shape resized', {translate: true});
					}

				} else if (this.origin.action.indexOf('rotate') === 0) {

					/**
					 * @translate
					 */
					if (len > 1) {
						this.canvas.statusBar.display(UILocale.getInstance().get('%d shapes rotated', len));
					} else {
						this.canvas.statusBar.display('Shape rotated', {translate: true});
					}

				}

				// trigger changes
				this.canvas.changed();
			}

			// delete temporary property
			delete this.objectStateAction;
		}
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

			// selection mode
			selectionMode: {
				type: 'radio',
				options: {
					simple: 'Simple selection',
					reverse: 'Reverse selection',
				},
				default: 'simple',
				translate: true,
			},

		};

		// end of method
	}

}
