/**
 * UIShortcutCopy class.
 * This shortcut is used to copy all the selected shapes, if any.
 * The shapes will be copied within the canvas clipboard.
 */
class UIShortcutCopy extends UIShortcut {

	/**
	 * @override
	 * Activates the shortcut.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// copy all selected elements, if any
		canvas.copy();
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
		if (navigator.isMac()) {
			// shortcut for mac
			return ['meta', 'c'];
		}

		// shortcut for win and linux
		return ['ctrl', 'c'];
	}

}
