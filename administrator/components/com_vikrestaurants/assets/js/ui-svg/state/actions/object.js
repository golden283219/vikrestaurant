/**
 * UIStateActionObject class.
 * After changing the state of an object, the unexecute (UNDO) method
 * can be used to restore the previous properties. Execute (REDO)
 * could be used to apply again the changes made.
 */
class UIStateActionObject extends UIStateAction {

	/**
	 * Class constructor.
	 *
	 * @param 	UIShape[] 	 subject  	A list of shapes.
	 * @param 	object[] 	 state    	A list of configurations.
	 * @param 	UIInspector  inspector  The inspector.
	 */
	constructor(subject, state, inspector) {
		// pass state as copy
		var data = state.map(function(elem) {
			return Object.assign({}, elem);
		});

		// invoke parent constructor
		super(subject, data);

		this.inspector = inspector;
	}

	/**
	 * @override
	 * Method used to restore the subject 
	 * to its initial state. Alias for REDO.
	 *
	 * @return 	void
	 */
	execute() {
		for (var i = 0; i < this.subject.length; i++) {
			// keep current state
			var current = Object.assign({}, this.subject[i].getConfig());

			// close state object as we don't want its reference
			var data = Object.assign({}, this.state[i]);

			// restore previous state
			this.subject[i].save(data);

			// overwrite state
			this.state[i] = current;
		}

		// re-build inspector from scratch
		this.inspector.build(this.inspector.elements);

		// check if we are observing the canvas
		if (this.subject.length == 1 && this.subject[0] instanceof UICanvas) {
			// init canvas as save() method doesn't apply changes to the map
			this.subject[0].init();
		}

		/**
		 * @translate
		 */
		if (this.subject.length > 1) {
			this.inspector.canvas.statusBar.display(UILocale.getInstance().get('%d elements restored', this.subject.length));
		} else {
			this.inspector.canvas.statusBar.display('Element restored', {translate: true});
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
		this.execute();
	}

}
