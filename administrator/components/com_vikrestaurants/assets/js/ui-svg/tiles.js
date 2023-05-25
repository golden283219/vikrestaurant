/**
 * UITiles class.
 * Factory class used to access the loaded icons globally.
 */
class UITiles {

	/**
	 * Adds a tile within the pool.
	 *
	 * @param 	string 	  name  	The icon identifier.	
	 * @param 	string 	  icon 	 	The icon URI.
	 * @param 	function  onload 	The callback to invoke once the image is ready.
	 *
	 * @return 	object 	 A reference to the added tile.
	 */
	static addTile(name, icon, onload) {
		var tile = UITiles.getTile(name);

		if (tile) {
			// image already loaded
			return tile;
		}

		// preload image
		var img = new Image();
		img.src = icon;

		// push tile within the pool
		UITiles.pool[name] = img;

		// check if the img was loaded
		if ((!img.width || !img.height) && typeof onload === 'function') {
			// register 'on load' callback
			UITiles.onLoad(name, onload);
		}

		// return new tile object
		return UITiles.pool[name];
	}

	/**
	 * Returns the specified tile.
	 *
	 * @param 	string 	name  The icon identifier.
	 *
	 * @return 	mixed 	The tile object if exists, otherwise false.
	 */
	static getTile(name) {
		// initialise pool if undefined
		if (UITiles.pool === undefined) {
			UITiles.pool = {};
		}

		if (UITiles.pool.hasOwnProperty(name)) {
			return UITiles.pool[name];
		}

		return false;
	}

	/**
	 * Registers a callback to invoke once the image is loaded.
	 * The callback is invoked only if it is not immediately ready.
	 *
	 * @param 	string 	  name  	The icon identifier.	
	 * @param 	function  onload 	The callback to invoke once the image is ready.
	 *
	 * @return 	void
	 */
	static onLoad(name, callback) {
		// get tile
		var tile = UITiles.getTile(name);

		if (!tile) {
			throw 'Tile [' + name + '] not found';
		}

		// register 'on load' callback
		tile.onload = callback;
	}

}
