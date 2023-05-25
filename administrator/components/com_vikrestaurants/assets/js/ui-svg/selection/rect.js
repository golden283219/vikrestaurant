/**
 * UISelectionRect class.
 * Class used to handle the common "click-drag-select" rectangle.
 */
class UISelectionRect extends UISelection {

	/**
	 * Class constructor.
	 *
	 * @param 	integer  x 	The selection X position.
	 * @param 	integer  y 	The selection Y position.
	 * @param 	integer  w 	The selection width.
	 * @param 	integer  h 	The selection height.
	 */
	constructor(x, y, w, h) {
		super();
		
		this.x = x ? x : 0;
		this.y = y ? y : 0;
		this.w = w ? w : 0;
		this.h = h ? h : 0;

		this.id = 'selection-rectangle';
	}

	/**
	 * @override
	 * Renders the selection.
	 *
	 * @return 	mixed 	The selection element.
	 */
	render() {
		var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');

		g.setAttribute('fill', '#4F80FF55');
		g.setAttribute('stroke', '#4F80FF');
		g.setAttribute('stroke-width', 2);
		g.setAttribute('id', this.id);

		var rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');

		rect.setAttribute('x',      this.x);
		rect.setAttribute('y',      this.y);
		rect.setAttribute('width',  this.w);
		rect.setAttribute('height', this.h);

		g.appendChild(rect);

		return g;
	}

	/**
	 * @override
	 * Refreshes the selection.
	 *
	 * @return 	void
	 */
	refresh() {
		// get real bounds
		var bounds = this.getBounds();

		jQuery('#' + this.id)
			.find('rect')
				.attr('x', bounds.x)
				.attr('y', bounds.y)
				.attr('width', bounds.w)
				.attr('height', bounds.h);
	}

	/**
	 * @override
	 * Destroys the selection.
	 *
	 * @return 	void
	 */
	destroy() {
		jQuery('#' + this.id).remove();
	}

	/**
	 * Returns an object with the real bounds.
	 *
	 * @return 	object
	 */
	getBounds() {
		// create bounds
		var bounds = {
			x: this.x,
			y: this.y,
			w: this.w,
			h: this.h,
		};

		if (bounds.w < 0) {
			// switch left position and width
			bounds.x += bounds.w;
			bounds.w *= -1;
		}

		if (bounds.h < 0) {
			// switch top position and height
			bounds.y += bounds.h;
			bounds.h *= -1;
		}

		return bounds;
	}
}
