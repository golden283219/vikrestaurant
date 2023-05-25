/**
 * UIShortcutUndo class.
 * This shortcut is used to undo the last action made.
 */
class UIShortcutUndo extends UIShortcut {

	/**
	 * @override
	 * Activates the shortcut.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// undo the last command
		var res = canvas.stackActions.undo();

		if (!res) {
			// inform that it is no more possible to undo
			/**
			 * @translate
			 */
			canvas.statusBar.display('Nothing to undo', {translate: true});
		} else {
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
		if (navigator.isMac()) {
			// shortcut for mac
			return ['meta', 'z'];
		}

		// shortcut for win and linux
		return ['ctrl', 'z'];
	}

}
