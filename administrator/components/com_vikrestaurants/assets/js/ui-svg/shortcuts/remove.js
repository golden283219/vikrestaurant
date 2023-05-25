/**
 * UIShortcutRemove class.
 * This shortcut is used to remove all the selected shapes, if any.
 */
class UIShortcutRemove extends UIShortcut {

	/**
	 * @override
	 * Activates the shortcut.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// get selected shapes
		var shapes = canvas.getSelection();

		if (shapes.length) {
			// remove selected shapes from canvas
			canvas.remove(shapes);

			// probably something has changed
			canvas.changed();
		}
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
		// backspace
		return [8];
	}

}
