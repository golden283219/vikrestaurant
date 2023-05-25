/**
 * UISelection interface.
 * Interface used for the selection handlers.
 */
class UISelection {

	/**
	 * Abstract method used to render the selection.
	 *
	 * @return 	mixed 	The selection element.
	 */
	render() {
		// inherit in children classes
	}

	/**
	 * Abstract method used to refresh the selection.
	 *
	 * @return 	void
	 */
	refresh() {
		// inherit in children classes
	}

	/**
	 * Abstract method used to destroy the selection.
	 *
	 * @return 	void
	 */
	destroy() {
		// inherit in children classes
	}
}
