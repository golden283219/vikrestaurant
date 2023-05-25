/**
 * UIShortcut class.
 * Shortcut interface used to handle the event related
 * to a specific keyboard combination.
 */
class UIShortcut {

	/**
	 * Class constructor.
	 */
	constructor() {
		// do nothing here
	}

	/**
	 * Abstract method used to initialize the shortcut.
	 * The canvas is passed as argument in order to
	 * extend the default functionalities, such as a new event.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	init(canvas) {
		// inherit in children classes
	}

	/**
	 * Abstract method used to destroy the shortcut.
	 *
	 * @return 	void
	 */
	destroy() {
		// inherit in children classes
	}

	/**
	 * Activates the shortcut.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// inherit in children classes
	}

	/**
	 * Returns the shortcut that can be used to activate the command.
	 * The array must contain one and only one character or symbol.
	 * The array may contain one ore more modifiers, which must be specified first.
	 *
	 * @return 	array 	A list of modifiers and characters.
	 */
	getShortcut() {
		// inherit in children classes
	}

}
