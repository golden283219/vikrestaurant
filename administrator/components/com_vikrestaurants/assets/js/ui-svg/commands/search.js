/**
 * UICommandSearch class.
 * This command is used to search the shapes by name within the canvas.
 * It is possible to search the tables also by:
 * - table identifier (e.g. id: 1)
 * - table min capacity (e.g. min: 2)
 * - table max capacity (e.g. max: 4)
 */
class UICommandSearch extends UICommand {

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

		// append transparent overlay
		jQuery('body').append('<div class="ui-transparent-overlay"></div>');

		var html = '';

		html += '<div class="ui-finder">';
		html += '<div class="ui-finder-box">';
		html += '<i class="fas fa-search"></i>';
		/**
		 * @translate
		 */
		html += '<input type="text" value="' + (this.lastSearch ? this.lastSearch : '') + '" placeholder="' + UILocale.getInstance().get('Type something') + '" />';
		html += '</div>';
		html += '</div>';

		// append search bar
		jQuery('body').append(html);

		// grab focus and handle events
		jQuery('.ui-finder input')
			.focus()
			.on('change', function() {
				var column = 'tableName';
				var search = jQuery(this).val().toLowerCase();

				if (search.indexOf('min:') === 0) {
					column = 'minCapacity';
					search = search.substr(4);
				} else if (search.indexOf('max:') === 0) {
					column = 'maxCapacity';
					search = search.substr(4);
				} else if (search.indexOf('id:') === 0) {
					column = 'id';
					search = search.substr(3);
				} else {
					column = 'name';
				}

				search = search.trim();

				// deselect all selected shapes
				canvas.select();

				// iterate the shapes
				for (var i in canvas.shapes) {
					if (!canvas.shapes.hasOwnProperty(i)) {
						continue;
					}

					// get shape object
					var shape = canvas.shapes[i];
					var tmp   = '';

					// evaluate property to compare
					tmp = shape.table[column];

					// check if property contains the searched value
					if (('' + tmp).toLowerCase().indexOf(search) !== -1) {
						// select shape without reset (do not build inspector)
						canvas.select(shape, false, false);
					}
				}

				// get selection
				var selection = canvas.getSelection();

				if (selection.length) {
					// rebuild inspector
					canvas.inspector.build(selection);
				} else {
					/**
					 * @translate
					 */
					canvas.statusBar.display('No tables found', {translate: true});
				}

				// dispose search
				canvas.triggerCommand('cmd-select');
			})
			.on('keydown', function(event) {
				// check if we pressed ESC button
				if (event.keyCode == 27) {
					// dispose search
					canvas.triggerCommand('cmd-select');
				}
			});

		// attach focus to toolbar in order to deactivate shortcuts
		canvas.toolbar.grabFocus();

		jQuery('.ui-transparent-overlay').on('click', function() {
			// activate select tool when overlay is clicked
			canvas.triggerCommand('cmd-select');
		});
	}

	/**
	 * @override
	 * Deactivates the command.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	deactivate(canvas) {
		super.deactivate(canvas);

		// remove finder and overlay
		jQuery('.ui-finder, .ui-transparent-overlay').remove();

		// reactivate shortcuts
		canvas.toolbar.loseFocus();
	}

	/**
	 * @override
	 * Returns the command title.
	 *
	 * @return string
	 */
	getTitle() {
		return 'Search tables';
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
			return ['meta', 'f'];
		}

		// shortcut for win and linux
		return ['ctrl', 'f'];
	}

}
