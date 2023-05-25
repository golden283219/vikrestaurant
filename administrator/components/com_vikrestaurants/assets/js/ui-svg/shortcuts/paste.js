/**
 * UIShortcutPaste class.
 * This shortcut is used to take the shapes of the clipboard and
 * paste them within the canvas, if any.
 */
class UIShortcutPaste extends UIShortcut {

	/**
	 * @override
	 * Activates the shortcut.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// paste elements in clipboard, if any
		canvas.paste();
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
			return ['meta', 'v'];
		}

		// shortcut for win and linux
		return ['ctrl', 'v'];
	}

}
