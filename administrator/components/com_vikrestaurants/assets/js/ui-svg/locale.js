/**
 * UILocale class.
 * Interface used to translate strings.
 */
class UILocale {

	/**
	 * Returns a new instance of this object,
	 * only creating it if it doesn't exist yet.
	 *
	 * @param 	string 	locale 	The locale. If not set, it will be
	 * 							retrieved from the browser configuration.
	 *
	 * @return 	UILocale
	 */
	static getInstance(locale) {
		// Make sure the class doesn't exist.
		// We don't need to check the locale as only
		// one language per time should be fetched.
		if (UILocale.instance === undefined) {
			// get locale if not specified
			if (locale === undefined) {
				locale = navigator.language || navigator.userLanguage
			}

			// use default class
			var _class = UILocale;

			// make sure the class exists
			if (!UILocale.classMap.hasOwnProperty(locale)) {
				console.warn('Locale [' + locale + '] not supported.');
			} else {
				// find class
				var _class = UILocale.classMap[locale];
			}

			// instantiate locale
			UILocale.instance = new _class();
		}

		return UILocale.instance;
	}

	/**
	 * Class constructor.
	 */
	constructor() {
		// define empty strings object
		this.strings = {};
	}

	/**
	 * Base method used to translate the given string.
	 * In case more than one argument is passed, the string
	 * will be fetched by a sprintf.
	 *
	 * @param 	string 	text 	The text to translate.
	 *
	 * @return 	string 	The translated string.
	 */
	get(text) {
		// check if text is supported and not empty
		if (this.strings.hasOwnProperty(text) && this.strings[text].length) {
			// used localised string
			text = this.strings[text];
		}

		// check if we have only one argument
		if (arguments.length <= 1) {
			// return plain text
			return text;
		}

		var args = [];

		// iterate arguments (skip string to translate)
		for (var i = 1; i < arguments.length; i++) {
			args.push(arguments[i]);
		}

		// use sprintf
		return String.prototype.sprintf.apply(text, args);
	}

	/**
	 * Registers a new translation.
	 * 
	 * @param 	string 	key  The original text.
	 * @param 	string 	val  The translated text.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	register(key, val) {
		// push new translation (overwrite if already exists)
		this.strings[key] = val;

		return this;
	}

	/**
	 * Overrides the given translation, only for
	 * the specified locale.
	 *
	 * @param 	string 	 locale  The language locale.
	 * @param 	string 	 key 	 The original text.
	 * @param 	string 	 val 	 The translated text.
	 *
	 * @return 	boolean  True if added, otherwise false.
	 */
	static override(locale, key, val) {
		// get locale
		var instance = UILocale.getInstance();

		// make sure the locale is supported and matches
		// the current one
		if (UILocale.classMap.hasOwnProperty(locale) 
			&& instance.constructor === UILocale.classMap[locale]) {
			// register new translation
			instance.register(key, val);

			return true;
		}

		return false;
	}
	
}

/**
 * Locale classes lookup.
 */
UILocale.classMap = {};
