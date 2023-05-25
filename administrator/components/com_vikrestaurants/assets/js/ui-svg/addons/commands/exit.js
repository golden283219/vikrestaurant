/**
 * UICommandExit class.
 */
class UICommandExit extends UICommand {

	/**
	 * @override
	 * Activates the command.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		Joomla.submitbutton('map.cancel');
	}

	/**
	 * @override
	 * Returns the command title.
	 *
	 * @return string
	 */
	getTitle() {
		return UILocale.getInstance().get('Exit');
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
		return ['q'];
	}

}

// add language overrides (Joomla)
UILocale.override('joomla', 'Exit', 'VRE_UISVG_EXIT');
