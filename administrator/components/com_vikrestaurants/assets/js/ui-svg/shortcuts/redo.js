/**
 * UIShortcutRedo class.
 * This shortcut is used to redo the last undone action.
 */
class UIShortcutRedo extends UIShortcut {

	/**
	 * @override
	 * Activates the shortcut.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// redo the last undoed command
		var res = canvas.stackActions.redo();

		if (!res) {
			// inform that it is no more possible to redo
			/**
			 * @translate
			 */
			canvas.statusBar.display('Nothing to redo', {translate: true});
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
			return ['meta', 'shift', 'z'];
		}

		// shortcut for win and linux
		return ['ctrl', 'y'];
	}

}
