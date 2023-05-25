/**
 * UIConstraint class.
 * Used to draw the constraint fetched between
 * the given shapes.
 */
class UIConstraint {

	/**
	 * Class constructor.
	 *
	 * @param 	UIShape  a
	 * @param 	UIShape  b
	 */
	constructor(a, b) {
		if (!(a instanceof UIShape) || !(b instanceof UIShape)) {
			throw 'It is possible to bind UIShape objects only';
		}

		this.a = a;
		this.b = b;
	}

	/**
	 * Method used to render the shape.
	 *
	 * @return 	mixed 	The shape element.
	 */
	render() {
		// inherit in children classes
		var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');

		line.setAttribute('stroke', '#ffffff');
		line.setAttribute('class', 'line-constraint');

		var coords = {};

		/**
		 *  xx
		 *  xx
		 * |
		 *  xxx
		 *  xxx
		 */
		if (this.a.x == this.b.x) {
			coords.x1 = this.a.x + 1;
			coords.x2 = this.b.x + 1;
		}

		/**
		 *  xx
		 *  xx
		 *   |
		 *    xxx
		 *    xxx
		 */
		else if (this.a.x + this.a.w == this.b.x) {
			coords.x1 = this.a.x + this.a.w;
			coords.x2 = this.b.x;
		}

		/**
		 *     xx
		 *     xx
		 *    |
		 *  xxx
		 *  xxx
		 */
		else if (this.a.x == this.b.x + this.b.w) {
			coords.x1 = this.a.x;
			coords.x2 = this.b.x + this.b.w;
		}

		/**
		 *   xx
		 *   xx
		 *    \
		 *  xxx
		 *  xxx
		 */
		else if (this.a.x + this.a.w == this.b.x + this.b.w) {
			coords.x1 = this.a.x + this.a.w - 1;
			coords.x2 = this.b.x + this.b.w - 1;
		}

		/**
		 *  -------
		 *  xx  xxx
		 *  xx  xxx
		 *      xxx
		 */
		else if (this.a.y == this.b.y) {
			coords.y1 = this.a.y + 1;
			coords.y2 = this.b.y + 1;
		}

		/**
		 *  xx
		 *  xx
		 *  ----xxx
		 *      xxx
		 */
		else if (this.a.y + this.a.h == this.b.y) {
			coords.y1 = this.a.y + this.a.h;
			coords.y2 = this.b.y;
		}

		/**
		 *      xxx
		 *      xxx
		 *  xx-----
		 *  xx
		 */
		else if (this.a.y == this.b.y + this.b.h) {
			coords.y1 = this.a.y;
			coords.y2 = this.b.y + this.b.h;
		}

		/**
		 *      xxx
		 *  xx  xxx
		 *  xx  xxx
		 *  -------
		 */
		else if (this.a.y + this.a.h == this.b.y + this.b.h) {
			coords.y1 = this.a.y + this.a.h - 1;
			coords.y2 = this.b.y + this.b.h - 1;
		}

		// fill missing coordinates
		if (coords.x1 !== undefined) {
			// X-axis found
			if (this.a.y > this.b.y) {
				coords.y1 = this.a.y;
				coords.y2 = this.b.y + this.b.h;
			} else {
				coords.y1 = this.a.y + this.a.h;
				coords.y2 = this.b.y;
			}
		} else {
			// Y-axis found
			if (this.a.x > this.b.x) {
				coords.x1 = this.a.x;
				coords.x2 = this.b.x + this.b.w;
			} else {
				coords.x1 = this.a.x + this.a.w;
				coords.x2 = this.b.x;
			}
		}
		
		// apply coordinates fetched to line
		for (var attr in coords) {
			if (!coords.hasOwnProperty(attr)) {
				continue;
			}

			line.setAttribute(attr, coords[attr]);
		}

		return line;
	}
}
