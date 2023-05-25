/**
 * Wizard helper class.
 */
class VREWizard {

	/**
	 * Returns the document step element.
	 *
	 * @param 	string  id  The step identifier.
	 *
	 * @return 	mixed 	The DOM element.
	 */
	static getStep(id) {
		return jQuery('.wizard-step-outer[data-id="' + id + '"]');
	}

	/**
	 * Returns the document form element.
	 *
	 * @return 	mixed  The DOM element.
	 */
	static getForm() {
		// look for the default form ID
		var form = jQuery('#adminForm');

		if (form.length == 0) {
			// lets try with the first available form
			form = jQuery('form').first();
		}

		return form;
	}

	/**
	 * Returns the form data of the related step.
	 *
	 * @param 	string   id       An optional step ID.
	 * @param 	boolean  asArray  True to serialize as array.
	 *
	 * @return 	mixed    The serialized form data.
	 */
	static getFormData(id, asArray) {
		// get form element
		var form = VREWizard.getForm();

		if (id) {
			// a step was passed, take only the fields 
			// that belong to that step
			form = form.find(':input,select,textarea')
				.filter('[name^="wizard[' + id + ']"]');	
		}

		if (asArray) {
			// serialize as array
			return form.serializeArray();
		}

		// return serialized form
		return form.serialize();
	}

	/**
	 * Searches for a temporary data registered
	 * by the wizard step.
	 *
	 * @param 	string  id   The step identifier.
	 * @param 	string  key  The data key.
	 * @param 	mixed   def  An optional default value.
	 *
	 * @return 	mixed   The data value.
	 */
	static getData(id, key, def) {
		if (!VREWizard.DATA.hasOwnProperty(id)) {
			// no registered data for this step
			return def;
		}

		if (!VREWizard.DATA[id].hasOwnProperty(key)) {
			// no registered key for the step data
			return def;
		}

		// return registered data
		return VREWizard.DATA[id][key];
	}

	/**
	 * Registers a temporary data for the specified
	 * wizard step.
	 *
	 * @param 	string  id   The step identifier.
	 * @param 	string  key  The data key.
	 * @param 	mixed   val  The value to set.
	 *
	 * @return 	void
	 */
	static setData(id, key, val) {
		if (!VREWizard.DATA.hasOwnProperty(id)) {
			// register pool
			VREWizard.DATA[id] = {};
		}

		// register data
		VREWizard.DATA[id][key] = val;
	}

	/**
	 * Registers a preflight callback for the
	 * specified wizard step ID.
	 *
	 * @param 	string    id        The step identifier.
	 * @param 	function  callback  The callback to invoke.
	 *
	 * @return 	void
	 */
	static addPreflight(id, callback) {
		// register callback within the pool
		VREWizard.PREFLIGHTS[id] = callback;
	}

	/**
	 * Unregisters the preflight callback for the
	 * specified wizard step ID.
	 *
	 * @param 	string  id  The step identifier.
	 *
	 * @return 	void
	 */
	static removePreflight(id) {
		if (VREWizard.PREFLIGHTS.hasOwnProperty(id)) {
			// unregister callback from the pool
			delete VREWizard.PREFLIGHTS[id];
		}
	}

	/**
	 * Executes the preflight of the specified
	 * wizard step, if registered.
	 *
	 * @param 	string  id     The step identifier.
	 * @param 	mixed 	step   The step DOM element.
	 * @param 	mixed   role   The role to execute (ignore, dismiss, process).
	 *
	 * @return 	mixed   The data to post.
	 */
	static doPreflight(id, step, role) {
		var data;

		// look for a registered step
		if (VREWizard.PREFLIGHTS.hasOwnProperty(id)) {
			// process preflight (return false to abort request)
			data = VREWizard.PREFLIGHTS[id](role, step);
		}

		// check whether the preflight returned post data
		if (data === undefined || data === null || data === true) {
			// extract post data from step
			data = VREWizard.getFormData(id);
		}

		return data;
	}

	/**
	 * Registers a postflight callback for the
	 * specified wizard step ID.
	 *
	 * @param 	string    id        The step identifier.
	 * @param 	function  callback  The callback to invoke.
	 *
	 * @return 	void
	 */
	static addPostflight(id, callback) {
		// register callback within the pool
		VREWizard.POSTFLIGHTS[id] = callback;
	}

	/**
	 * Unregisters the postflight callback for the
	 * specified wizard step ID.
	 *
	 * @param 	string  id  The step identifier.
	 *
	 * @return 	void
	 */
	static removePostflight(id) {
		if (VREWizard.POSTFLIGHTS.hasOwnProperty(id)) {
			// unregister callback from the pool
			delete VREWizard.POSTFLIGHTS[id];
		}
	}

	/**
	 * Executes the postflight of the specified
	 * wizard step, if registered.
	 *
	 * @param 	string  id    The step identifier.
	 * @param 	mixed 	step  The step DOM element.
	 * @param 	mixed   role  The role to execute (ignore, dismiss, process).
	 * @param 	mixed 	error  An optional error in case of failure.
	 *
	 * @return 	void
	 */
	static doPostflight(id, step, role, error) {
		// look for a registered step
		if (VREWizard.POSTFLIGHTS.hasOwnProperty(id)) {
			// process postflight
			VREWizard.POSTFLIGHTS[id](role, step, error);
		}
	}

	/**
	 * Ignores the wizard step.
	 *
	 * @param 	mixed  caller  Either the step identifier or the exec button.
	 *
	 * @return 	Promise
	 */
	static ignore(caller) {
		// execute ignore role
		return VREWizard.execute(caller, 'ignore');
	}

	/**
	 * Dismisses the wizard step after completion.
	 *
	 * @param 	mixed  caller  Either the step identifier or the exec button.
	 *
	 * @return 	Promise
	 */
	static dismiss(caller) {
		// execute dismiss role
		return VREWizard.execute(caller, 'ignore');
	}

	/**
	 * Processes the wizard step for completion.
	 *
	 * @param 	mixed  caller  Either the step identifier or the exec button.
	 *
	 * @return 	Promise
	 */
	static process(caller) {
		// execute process role
		return VREWizard.execute(caller, 'process');
	}

	/**
	 * Executes the specified role for the wizard step.
	 *
	 * @param 	mixed  caller  Either the step identifier or the exec button.
	 * @param 	mixed  role    The role to execute (ignore, dismiss, process).
	 *
	 * @return 	Promise
	 */
	static execute(caller, role) {
		var id, btn;

		if (typeof caller === 'undefined') {
			// missing step
			throw 'Wizard step is missing';
		} else if (typeof caller === 'string') {
			// a step ID assumed
			id = caller;
		} else {
			// register button
			btn = caller;

			// otherwise exec button assumed, find the ID
			id = jQuery(btn).closest('.wizard-step-outer[data-id]').data('id');

			if (typeof role === 'undefined') {
				// extract role from button data
				role = jQuery(btn).data('role');
			}
		}

		// create promise
		return new Promise((resolve, reject) => {
			// get step element
			var step = VREWizard.getStep(id);

			if (!step.length) {
				// step not found, raise error
				reject('Wizard step [%s] not found'.replace(/%s/, id));

				return false;
			}

			if (!btn) {
				// find button from step
				btn = step.find('[data-role="' + role + '"]');
			}

			// make sure the button hasn't been disabled
			if (jQuery(btn).prop('disabled')) {
				reject(false);

				return false;
			}

			// launch preflight and get post data
			var data = VREWizard.doPreflight(id, step, role);

			// check whether the preflight prevented the request
			if (data === false) {
				reject(false);

				return false;
			}

			// disable button to avoid multiple executions
			jQuery(btn).prop('disabled', true);

			// make request
			UIAjax.do(
				'index.php?option=com_vikrestaurants&task=wizard.' + role + '&id=' + id,
				data,
				function(resp) {
					// JSON decode response
					resp = JSON.parse(resp);

					// iterate all steps in response
					for (var k in resp.steps) {
						// get temporary step
						var tmp = VREWizard.getStep(k);

						// toggle visibility according to received response
						if (resp.steps[k]) {
							tmp.show();
						} else {
							tmp.hide();
						}

						// refresh step HTML
						tmp.html(resp.steps[k]);

						// launch postflight
						VREWizard.doPostflight(k, tmp, role, false);
					}

					// resolve promise on success
					resolve(resp);
				},
				function(error) {
					// enable button again to retry
					jQuery(btn).prop('disabled', false);

					// launch postflight on failure too
					VREWizard.doPostflight(id, step, role, error);

					console.error(error);

					// reject on failure
					reject(error.responseText);
				}
			);
		});
	}
}

/**
 * A lookup of temporary data that the steps can use
 * to register their own details during preflights.
 *
 * @var object
 */
VREWizard.DATA = {};

/**
 * A lookup of preflights to be used before refreshing
 * the contents of the steps.
 *
 * If needed, a step can register its own callback
 * to be executed before the AJAX request is started.
 *
 * The property name MUST BE equals to the ID of 
 * the step that is registering its callback.
 *
 * @var object
 */
VREWizard.PREFLIGHTS = {};

/**
 * A lookup of callbacks to be used after refreshing
 * the contents of the steps.
 *
 * If needed, a step can register its own callback
 * to be executed once the AJAX request is completed.
 *
 * The property name MUST BE equals to the ID of 
 * the step that is registering its callback.
 *
 * @var object
 */
VREWizard.POSTFLIGHTS = {};
