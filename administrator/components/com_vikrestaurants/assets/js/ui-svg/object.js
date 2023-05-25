/**
 * UIObject class.
 * Interface used for inspectable classes.
 */
class UIObject extends UIClonable {

	/**
	 * Abstract method used to obtain the inspector form.
	 *
	 * @return 	object 	The inspector form.
	 */
	getInspector() {
		// inherit in children classes
		return {};
	}

	/**
	 * Abstract method used to obtain the object configuration.
	 *
	 * @return 	object 	The configuration state.
	 */
	getConfig() {
		// inherit in children classes
		return {};
	}

	/**
	 * Abstract method used to save the object state.
	 *
	 * @param 	object 	data 	The form data.
	 *
	 * @return 	void
	 */
	save(data) {
		// inherit in children classes
	}
	
}
