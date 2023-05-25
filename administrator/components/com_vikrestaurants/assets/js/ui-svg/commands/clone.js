/**
 * UICommandClone class.
 * This command is used to clone existing shapes after clicking them.
 */
class UICommandClone extends UICommand {

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
		return 'Clone a shape';
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
		return ['c'];
	}

	/**
	 * Clones the selected shape, if any.
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

		// make sure the element is a shape
		if (!(element instanceof UIShape)) {
			// go ahead
			return;
		}

		// paste the element automatically
		var pasted = this.canvas.paste([element]);

		// check if auto select cloning mode
		if (this.config.cloningMode == 'select') {
			// trigger selection command
			this.canvas.triggerCommand('cmd-select');

			// dispatch event again, which will be caught by
			// the select command this time
			this.canvas.handleEvent(event, pasted[0]);
		}

		// probably something has changed
		this.canvas.changed();

		// always stop propagating the event
		return false;
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

			// cloning mode
			cloningMode: {
				type: 'radio',
				options: {
					keep: 'Keep cloning',
					select: 'Auto select',
				},
				default: 'keep',
				translate: true,
			},

		};

		// end of method
	}

}
