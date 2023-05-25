/**
 * UIStateActionRemove class.
 * After removing a shape from the canvas, the unexecute (UNDO) method
 * can be used to re-add the shape into the canvas. Execute (REDO)
 * could be used to remove the table again after restoring it.
 */
class UIStateActionRemove extends UIStateAction {

	/**
	 * @override
	 * Method used to restore the subject 
	 * to its initial state. Alias for REDO.
	 *
	 * @return 	void
	 */
	execute() {
		// subject is canvas
		// state is the shape to remove after restoring it
		this.subject.remove(this.state);
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

}
