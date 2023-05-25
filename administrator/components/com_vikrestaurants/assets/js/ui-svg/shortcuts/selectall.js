/**
 * UIShortcutSelectAll class.
 * This shortcut is used to select all the shapes in the canvas.
 */
class UIShortcutSelectAll extends UIShortcut {

	/**
	 * @override
	 * Activates the shortcut.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// select all shapes in canvas
		canvas.selectAll();
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
			return ['meta', 'a'];
		}

		// shortcut for win and linux
		return ['ctrl', 'a'];
	}

}
