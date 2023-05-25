/**
 * UICommandShortcut class.
 * This command is used to handle the registered shortcut events.
 * It is just a bridge between the keyboard and the canvas.
 */
class UICommandShortcut extends UICommand {

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
		
		// do not need to keep a reference to the canvas
		// this.canvas = canvas;
	}

	/**
	 * Handles keydown event to trigger shortcuts.
	 *
	 * @param 	Event 	event
	 * @param 	mixed 	element
	 *
	 * @return 	void 
	 */
	keydown(event, element) {
		if (!(element instanceof UICanvas)) {
			// check only if the dispatched element is the canvas
			return;
		}

		if (element.inspector.hasFocus() || element.toolbar.hasFocus()) {
			// canvas as focus, do not perform any action
			return;
		}

		var keyboard = event.originalEvent;

		// iterate all the commands
		for (var k in element.commands) {
			if (!element.commands.hasOwnProperty(k)) {
				continue;
			}

			// get command object
			var cmd = element.commands[k];

			// get shortcut
			var shortcut = cmd.getShortcut();

			// make sure we have a shortcut to fetch
			if (Array.isArray(shortcut) && shortcut.length && keyboard.shortcut(shortcut)) {
				// trigger command
				element.triggerCommand(cmd.id);

				// stop propagating the event
				return false;
			}
		}

		// iterate all the shortcuts
		for (var k = 0; k < element.shortcuts.length; k++) {
			// get shortcut object
			var cmd = element.shortcuts[k];

			// get shortcut
			var shortcut = cmd.getShortcut();

			// make sure we have a shortcut to fetch
			if (Array.isArray(shortcut) && shortcut.length && keyboard.shortcut(shortcut)) {
				// activate shortcut
				cmd.activate(element);

				// stop propagating the event
				return false;
			}
		}

		// go ahead without stopping the propagation
	}

}
