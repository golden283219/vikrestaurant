/**
 * UIState class.
 * This class acts as a stack (LIFO) of actions and implements the Undo pattern.
 */
class UIState {

	/**
	 * Class constructor.
	 *
	 * @param 	integer  max 	The maximum number of actions that can cohexist.
	 */
	constructor(max) {
		this.undoable = [];
		this.redoable = [];

		this.max 	 = max ? max : 20;
		this.freezed = false;
	}

	/**
	 * Registers a new action within the stack that could be 
	 * cancelled and restored.
	 *
	 * @param 	UIStateAction 	action 	The state action to freeze.
	 *
	 * @return 	boolean 		True if registered, otherwise false.
	 */
	register(action) {
		if (!(action instanceof UIStateAction)) {
			console.error(action);
			throw 'Invalid [' + typeof action + '] action.';
		}

		// check if we can register new actions
		if (this.freezed) {
			// we are probably restoring the state of a command
			return false;
		}

		// reset redoable
		this.redoable = [];

		// push the action within the list
		this.undoable.push(action);

		// make sure the length didn't exceed
		if (this.undoable.length > this.max) {
			this.undoable.shift();
		}

		return true;
	}

	/**
	 * Removes the current undoable action.
	 *
	 * @return 	UIStateAction 	The discarded action.
	 */
	discard() {
		return this.undoable.pop();
	}

	/**
	 * Empties the list of the registered actions.
	 *
	 * @return 	void
	 */
	flush() {
		this.undoable = [];
		this.redoable = [];
	}

	/**
	 * Freezes the actions stack. When the stack is freezed, 
	 * it is no more possible to register actions. Since undo/redo
	 * commands may invoke functions that registers new actions,
	 * we need to freeze the stack every time we restore something.
	 *
	 * @return 	void
	 */
	freeze() {
		this.freezed = true;
	}

	/**
	 * Unfrezees the actions stack.
	 *
	 * @return 	void
	 */
	unfreeze() {
		this.freezed = false;
	}

	/**
	 * Checks if we can unexecute the last command made.
	 * Returns false only in case the list is empty.
	 *
	 * @return 	boolean
	 */
	canUndo() {
		return this.undoable.length > 0;
	}

	/**
	 * Unexecutes the current action to restore the previous state.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	undo() {
		if (!this.canUndo()) {
			return false;
		}

		// get last registered action
		var action = this.undoable.pop();

		// the process dispatched by UNDO command
		// won't be able to register new actions
		this.freeze();

		// execute undo command
		action.unexecute();

		// register action within redoable commands
		this.redoable.push(action);

		// unfreeze the stack
		this.unfreeze();

		return true;
	}

	/**
	 * Checks if we can re-execute the last command made.
	 * Returns false only in case the list is empty.
	 *
	 * @return 	boolean
	 */
	canRedo() {
		return this.redoable.length > 0;
	}

	/**
	 * Executes the current action to restore its encapsulated state.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	redo() {
		if (!this.canRedo()) {
			return false;
		}

		// get last registered action
		var action = this.redoable.pop();

		// the process dispatched by REDO command
		// won't be able to register new actions
		this.freeze();

		// execute redo command
		action.execute();

		// register action within undoable commands (as last)
		this.undoable.push(action);

		// unfreeze the stack
		this.unfreeze();

		return true;
	}

}
