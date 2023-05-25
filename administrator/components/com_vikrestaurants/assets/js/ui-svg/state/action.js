/**
 * UIStateAction class.
 * This is an interface for an action that can be undone/redone.
 */
class UIStateAction {

	/**
	 * Class constructor.
	 *
	 * @param 	mixed 	subject  The object to observe.
	 * @param 	mixed 	state 	 The state of the subject.
	 */
	constructor(subject, state) {
		this.subject = subject;
		this.state   = state;
	}

	/**
	 * Abstract method used to restore the subject 
	 * to its initial state. Alias for REDO.
	 *
	 * @return 	void
	 */
	execute() {
		// inherit in children classes
	}

	/**
	 * Abstract method used to restore the subject 
	 * to its previous state. Alias for UNDO.
	 *
	 * @return 	void
	 */
	unexecute() {
		// inherit in children classes
	}

}
