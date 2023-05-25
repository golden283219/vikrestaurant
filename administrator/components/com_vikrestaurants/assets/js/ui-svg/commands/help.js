/**
 * UICommandHelp class.
 * This command is used to display the documentation.
 */
class UICommandHelp extends UICommand {

	/**
	 * @override
	 * Activates the command.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// activate command
		super.activate(canvas);

		// open table maps documentation on https://e4j.com
		window.open('https://extensionsforjoomla.com/documentation/vik-restaurants-tables-map', '_blank');

		setTimeout(function() {
			canvas.triggerCommand('cmd-select');
		}, 32);
	}

	/**
	 * @override
	 * Returns the command title.
	 *
	 * @return string
	 */
	getTitle() {
		return 'Help';
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
		return ['h'];
	}

}
