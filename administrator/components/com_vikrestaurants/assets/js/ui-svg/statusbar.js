/**
 * UIStatusBar class.
 * This class is used to display status messages within the
 * bottom status bar.
 */
class UIStatusBar {

	/**
	 * Class constructor.
	 *
	 * @param 	string  source 	The element source.
	 */
	constructor(source) {
		this.source   = source ? jQuery(source) : null;
		this.timeout  = null;
		this.enabled  = true;
	}

	/**
	 * Enables the status bar.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	enable() {
		this.enabled = true;

		return this;
	}

	/**
	 * Disables the status bar.
	 * Any messages will be suppressed.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	disable() {
		this.enabled = false;

		return this;
	}

	/**
	 * Displays the specified text within the status bar.
	 *
	 * @param 	string 	text 	 The text to show.
	 * @param 	object  options  A configuration object.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	display(text, options) {
		if (!text || !this.enabled) {
			// do nothing if text is empty or status bar is disabled
			return this;
		}

		if (options === undefined) {
			options = {};
		}

		if (!this.source) {
			// status bar is not attached to any element, use the console
			return this;
		}

		// check if the text should be translated
		if (options.translate) {
			// translate using default browser language (if supported)
			text = UILocale.getInstance().get(text);
		}

		// append the text to the element
		this.source.html(text);

		if (this.timeout) {
			// clear timeout if currently set
			clearTimeout(this.timeout);
			this.timeout = null;
		}

		// check if the message should stay
		if (!options.keep) {
			var _this = this;

			// setup timeout duration
			var duration = options.duration ? options.duration : 'auto';

			// if 'auto' calculate duration based on text length
			if (duration == 'auto') {
				// use 1000 ms by default
				duration = 1000;

				var punctuation = text.match(/[,\.;:?!]/g);
				var spaces 		= text.match(/[\s]+/g);
				var letters 	= text.match(/[^,\.;:?!\s]/g);

				if (punctuation) {
					// increase the duration by 100 ms per each punctuation symbol
					duration += 100 * punctuation.length;
				}

				if (spaces) {
					// increase the duration by 50 ms per each space
					duration += 50 * spaces.length;
				}

				if (letters) {
					// increase the duration by 200 ms per any other character
					duration += 200 * letters.length;
				}
			}

			// register new timeout callback to hide the message automatically
			this.timeout = setTimeout(function() {
				// unset source
				_this.source.html('');
				// unset timeout
				_this.timeout = null;
			}, duration);
		}

		return this;
	}

}
