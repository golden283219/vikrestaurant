/**
 * UICommandSave class.
 */
class UICommandSave extends UICommand {

	/**
	 * @override
	 * Activates the command.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		// serialize data to save
		var json = canvas.serialize();

		UIAjax.do(
			'index.php?option=com_vikrestaurants&task=map.save&tmpl=component',
			{
				id: this.config.id,
				json: json,
			},
			// success callback
			function(resp) {
				// decode response
				try {
					resp = JSON.parse(resp);
				} catch (err) {
					console.log(err, resp);
					resp = {};
				}

				// display message
				/**
				 * @translate
				 */
				canvas.statusBar.display('Saved', {translate: true});

				/**
				 * Display toast message.
				 * 
				 * @translate
				 * @since 1.8
				 */
				ToastMessage.dispatch(UILocale.getInstance().get('Saved'));

				// commit changes
				canvas.commitChanges();

				// always display response message
				console.log(resp);

				// iterate all new elements to inject inserted table ID
				for (var tmpId in resp.newMap) {
					if (!resp.newMap.hasOwnProperty(tmpId)) {
						continue;
					}

					// make sure the canvas owns the shape
					if (canvas.shapes.hasOwnProperty(tmpId)) {
						// update table ID
						canvas.shapes[tmpId].table.id = resp.newMap[tmpId];
					}
				}

				// refresh inspector as we need to update the field ID
				// of the table that is currently selected (if any)
				canvas.inspector.refresh();
			},
			// failure callback
			function(err) {
				// display alert
				alert(UILocale.getInstance().get('An error has occurred.'));

				// display error within status bar
				/**
				 * @translate
				 */
				if (err.statusText) {
					canvas.statusBar.display(UILocale.getInstance().get('Could not save data. Error: %s', err.status + ' ' + err.statusText));
				} else {
					canvas.statusBar.display('An error has occurred.', {translate: true});
				}
			}
		);
	}

	/**
	 * @override
	 * Returns the command title.
	 *
	 * @return string
	 */
	getTitle() {
		return UILocale.getInstance().get('Save');
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
			return ['meta', 's'];
		}

		// shortcut for win and linux
		return ['ctrl', 's'];
	}

}

// add language overrides (Joomla)
UILocale.override('joomla', 'Save', 'JTOOLBAR_APPLY');
UILocale.override('joomla', 'Saved', 'VRE_UISVG_SAVED');
UILocale.override('joomla', 'Could not save data. Error: %s', 'JERROR_SAVE_FAILED');
UILocale.override('joomla', 'An error has occurred.', 'JERROR_AN_ERROR_HAS_OCCURRED');
