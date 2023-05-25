/**
 * Checks if the KeyBoard event matches the given shortcut.
 *
 * @param 	array 	 keys 	The shortcut representation.
 *
 * @return 	boolean  True if matches, otherwise false.
 */
KeyboardEvent.prototype.shortcut = function(keys) {
	// get modifiers list
	var modifiers = keys.slice(0);
	// pop character from modifiers
	var keyCode = modifiers.pop();

	if (typeof keyCode === 'string') {
		// get ASCII
		keyCode = keyCode.toUpperCase().charCodeAt(0);
	}

	// make sure the modifiers are lower case
	modifiers = modifiers.map(function(mod) {
		return mod.toLowerCase();
	});

	var ok = false;

	// validate key code
	if (this.keyCode == keyCode) {
		// validate modifiers
		ok = true;
		var lookup = ['meta', 'shift', 'alt', 'ctrl'];

		for (var i = 0; i < lookup.length && ok; i++) {
			// check if modifiers is pressed
			var mod = this[lookup[i] + 'Key'];

			if (mod) {
				// if pressed, the shortcut must specify it
				ok &= modifiers.indexOf(lookup[i]) !== -1;
			} else {
				// if not pressed, the shortcut must not include it
				ok &= modifiers.indexOf(lookup[i]) === -1;
			}
		}
	}

	return ok;
}

/**
 * Checks if the current platform is Mac.
 *
 * @return 	boolean  True if Mac, otherwise false.
 */
Navigator.prototype.isMac = function() {
	return this.platform.toUpperCase().indexOf('MAC') === 0;
};

/**
 * Checks if the current platform is Windows.
 *
 * @return 	boolean  True if Windows, otherwise false.
 */
Navigator.prototype.isWin = function() {
	return this.platform.toUpperCase().indexOf('WIN') === 0;
};

/**
 * Clones the given object by checking UIClonable interface.
 *
 * @param 	mixed 	obj  The object to clone.
 *
 * @return 	mixed 	The cloned object.
 */
Object.cloneInstance = function(obj) {
	var clone;

	if (obj instanceof UIClonable) {
		// clone by using the internal method
		clone = obj.clone();
	} else {
		// clone by using default method
		clone = Object.createClone(obj);
	}

	return clone;
}

/**
 * Clones the given object.
 *
 * @param 	mixed 	obj  The object to clone.
 *
 * @return 	mixed 	The cloned object.
 */
Object.createClone = function(obj) {
	return Object.assign(Object.create(Object.getPrototypeOf(obj)), obj);
}

/**
 * Escapes single quotes and double quotes by converting
 * them in the related HTML entity.
 *
 * @return 	string 	The escaped string
 */
String.prototype.escape = function() {
	return this.toString().replace(/"/g, '&quot;');
}

/**
 * Add support for sprintf.
 * You can pass an undefined number of arguments, which must be
 * equals (or higher) to the number of wildcards contained into the string.
 *
 * @return 	string 	The resulting string.
 */
String.prototype.sprintf = function() {
	if (!arguments.length) {
		throw 'String::sprintf() requires at least an argument.';
	}

	// get internal string
	var text = this.toString();

	// count wildcard occurrences
	var wildcards = text.match(/%[dsf]/g);

	// no wildcards in text, return plain string
	if (!wildcards) {
		return text;
	}

	// make sure there are enough arguments
	if (wildcards.length > arguments.length) {
		throw 'Too few arguments. Expected ' + wildcards.length + ', given ' + arguments.length + '.';
	}

	var index = 0;

	// iterate until the wildcards are empty
	while (wildcards.length) {
		// get first wildcard
		var w = wildcards.shift();

		var chunk = '';

		switch (w) {
			// cast to int
			case '%d':
				chunk = parseInt(arguments[index]);
				break;

			// cast to float
			case '%f':
				chunk = parseFloat(arguments[index]);
				break;

			// leave as is
			default:
				chunk = arguments[index];
		}

		// replace it with the current argument
		text = text.replace(w, chunk);

		// increase the index
		index++;
	}

	return text;
}
