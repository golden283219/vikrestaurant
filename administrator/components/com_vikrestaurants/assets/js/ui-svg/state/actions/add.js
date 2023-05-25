/**
 * UIStateActionAdd class.
 * After adding a shape to the canvas, the unexecute (UNDO) method
 * can be used to remove the shape from the canvas. Execute (REDO)
 * could be used to restore the table after deleting it.
 */
class UIStateActionAdd extends UIStateAction {

	/**
	 * @override
	 * Method used to restore the subject 
	 * to its initial state. Alias for REDO.
	 *
	 * @return 	void
	 */
	execute() {
		// subject is canvas
		// state is the shape to re-add
		var shapes = this.state;

		// always treat state as a list of shapes
		if (!Array.isArray(this.state)) {
			shapes = [this.state];
		}

		for (var k = 0; k < shapes.length; k++) {
			this.subject.add(shapes[k]);
		}
	}

	/**
	 * @override
	 * Method used to restore the subject 
	 * to its previous state. Alias for UNDO.
	 *
	 * @return 	void
	 */
	unexecute() {
		// subject is canvas
		// state is the shape to remove
		this.subject.remove(this.state);
	}

}
