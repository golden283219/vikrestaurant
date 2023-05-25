/**
 * UIFileDialog class.
 * Static class used to handle a file dialog.
 * Uses the observer pattern to notify all the subscribed clients.
 */
class UIFileDialog {

	/**
	 * Abstract method used to display the file dialog.
	 *
	 * @return 	void
	 */
	static open() {
		// inherit in children classes
		alert('File dialog not supported!');
	}

	/**
	 * Abstract method used to dismiss the file dialog.
	 *
	 * @return 	void
	 */
	static close() {
		// inherit in children classes
		alert('File dialog not supported!');
	}

	/**
	 * Subscribes callback to register the observers.
	 *
	 * @param 	string 	  id 		The observer identifier.
	 * @param 	function  callback 	The callback to invoke every
	 * 								time a new image is uploaded.
	 *
	 * @return 	void
	 */
	static subscribe(id, callback) {
		// define observers list if empty
		if (UIFileDialog.observers === undefined) {
			UIFileDialog.observers = {};
		}

		// push callback within the list
		UIFileDialog.observers[id] = callback;
	}

	/**
	 * Unsubscribes the registered callback.
	 *
	 * @param 	string 	  id 		The observer identifier.
	 *
	 * @return 	void
	 */
	static unsubscribe(id) {
		// remove callback from the list, if exists
		if (UIFileDialog.observers !== undefined && UIFileDialog.observers.hasOwnProperty(id)) {
			delete UIFileDialog.observers[id];
		}
	}

	/**
	 * Notifies all the subscribed observers.
	 *
	 * @param 	mixed 	value 	The uploaded media(s).
	 *
	 * @return 	void
	 */
	static notify(value) {
		// update global media
		var result = UIFileDialog.addMedia(value);

		// proceed only if we have at least an observer
		if (UIFileDialog.observers === undefined) {
			return;
		}

		// iterate observers
		for (var observer in UIFileDialog.observers) {
			if (!UIFileDialog.observers.hasOwnProperty(observer)) {
				continue;
			}

			UIFileDialog.observers[observer](result);
		}
	}

	/**
	 * Adds an image into the static pool, which could be used
	 * by any field to display/select a collection of media files.
	 *
	 * @param 	mixed 	media 	An image or a list of images.
	 *
	 * @return 	object 	The updated medias.
	 */
	static addMedia(media) {
		// always treat media as an array
		if (!Array.isArray(media)) {
			media = [media];
		}

		// create media pool if undefined
		if (UIFileDialog.media === undefined) {
			UIFileDialog.media = {};
		}

		var result = {};

		// iterate the medias
		for (var i = 0; i < media.length; i++) {
			// use full URI as key
			var k = media[i];
			// use media name as value
			var v = k.split('/').pop();

			// extract image name
			var matches = v.match(/([^\/]+)\.([a-z0-9]{2,})$/i);

			if (matches) {
				v = matches[1];
			}

			UIFileDialog.media[k] = v;

			// preload image
			UITiles.addTile('media-' + v, k);

			// update result object too
			result[k] = v;
		}

		return result;
	}

	/**
	 * Returns the media collection as an object of key/val pairs.
	 *
	 * @return 	object
	 */
	static getMedia() {
		// create media pool if undefined
		if (UIFileDialog.media === undefined) {
			UIFileDialog.media = {};
		}

		// always return a copy of the object
		return Object.assign({}, UIFileDialog.media);
	}
	
}
