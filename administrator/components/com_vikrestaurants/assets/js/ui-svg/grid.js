/**
 * UIGrid class.
 * Draws a grid within the canvas.
 */
class UIGrid {

	/**
	 * Class constructor.
	 *
	 * @param 	UICanvas 	canvas  The canvas to which the grid should be attached.
	 */
	constructor(canvas) {
		var pattern = document.createElementNS('http://www.w3.org/2000/svg', 'pattern');

		pattern.setAttribute('id', 'canvas-grid-texture');
		pattern.setAttribute('width', 40);
		pattern.setAttribute('height', 40);
		pattern.setAttribute('patternUnits', 'userSpaceOnUse');

		var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

		path.setAttribute('d', 'M 40 0 L 0 0 0 40');
		path.setAttribute('fill', 'transparent');
		path.setAttribute('stroke', 'gray');
		path.setAttribute('stroke-width', 1);

		pattern.appendChild(path);

		// push the grid definition
		canvas.defElement(pattern);

		var rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');

		rect.setAttribute('width', '100%');
		rect.setAttribute('height', '100%');
		rect.setAttribute('fill', 'transparent');
		rect.setAttribute('id', 'canvas-grid');

		// push the grid shape
		canvas.addElement(rect);
	}

	/**
	 * Enables the grid texture.
	 *
	 * @return 	void
	 */
	enable() {
		jQuery('#canvas-grid').attr('fill', 'url(#canvas-grid-texture)');
	}

	/**
	 * Disables the grid texture.
	 *
	 * @return 	void
	 */
	disable() {
		jQuery('#canvas-grid').attr('fill', 'transparent');
	}

	/**
	 * Sets the color of the grid.
	 *
	 * @param 	string 	color 	A default color or the hex value (# included).
	 *
	 * @return 	void
	 */
	setStrokeColor(color) {
		jQuery('#canvas-grid-texture path').attr('stroke', color);
	}

	/**
	 * Sets the size of the rects within the grid.
	 *
	 * @param 	integer  s	The rect width and height.
	 *
	 * @return 	void
	 */
	setSize(s) {
		// only integer values are accepted
		s = Math.round(s);
		
		// change rect size and path
		jQuery('#canvas-grid-texture')
			.attr('width', s)
			.attr('height', s)
			.find('path')
				.attr('d', 'M ' + s + ' 0 L 0 0 0 ' + s);
	}

	/**
	 * Returns the current size of the grid rects.
	 *
	 * @return 	integer  The rect size.
	 */
	getSize() {
		return parseInt(jQuery('#canvas-grid-texture').attr('width'));
	}
}
