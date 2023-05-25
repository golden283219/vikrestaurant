/**
 * UICommandRubber class.
 * This command is used to delete existing shapes after clicking them.
 * Leave the mouse pressed to delete all the shapes that intersect
 * the mouse movements.
 */
class UICommandRubber extends UICommand {

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
		return 'Remove shapes';
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
		return ['r'];
	}

	/**
	 * Erases the clicked shape.
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

		var _this = this;

		// Delay binding as the mouse may trigger move event while pressing it.
		this.mousemoveTimeout = setTimeout(function() {
			// change cursor
			jQuery('#canvas-grid').css('cursor', 'crosshair');

			// add support for mousemove event as long as the mouse remains down
			_this.canvas.bindEvent(_this.canvas, _this.canvas.canvas, 'mousemove');
		}, 128);

		// make sure we clicked a shape
		if (!(element instanceof UIShape)) {
			// do nothing
			return false;
		}

		// remove the element
		if (!this.canvas.remove(element)) {
			// an error occurred while erasing the shape
			return false;
		}

		// probably something has changed
		this.canvas.changed();

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

		// get intersected shapes
		var shapes = this.canvas.getIntersection(event.offsetX, event.offsetY);

		if (shapes.length == 0) {
			// no intersected shapes, go ahead
			return;
		}

		// remove shapes from canvas
		this.canvas.remove(shapes);

		// probably something has changed
		this.canvas.changed();

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

		// unregister mouse move event from canvas
		this.canvas.unbindEvent(this.canvas.canvas, 'mousemove');

		// restore cursor
		jQuery('#canvas-grid').css('cursor', '');

		// always stop propagating the event
		return false;
	}

}
