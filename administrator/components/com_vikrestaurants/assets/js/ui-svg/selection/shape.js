/**
 * UISelectionShape class.
 * Class used to handle the the selection of a shape.
 * The selection is always applied to the bounds of the shape.
 */
class UISelectionShape extends UISelection {

	/**
	 * Class constructor.
	 *
	 * @param 	UIShape  shape 	The shape to select.
	 */
	constructor(shape) {
		super();
		
		if (!(shape instanceof UIShape)) {
			console.error(shape);
			throw 'The selection can be made only to UIShape elements';
		}

		// keep a reference to the shape
		this.shape = shape;

		// create an ID for the selection
		this.id = this.shape.getSource().id;

		if (!this.id) {
			console.error(this.shape);
			throw 'The shape source does not own an ID';
		}

		this.id += '-selection';
	}

	/**
	 * Renders the selection.
	 *
	 * @return 	mixed 	The selection element.
	 */
	render() {
		// render the selection as a rectangle shape
		var el = document.createElementNS('http://www.w3.org/2000/svg', 'g');

		var transform = 'rotate(%f %f %f)'.sprintf(this.shape.rotation, this.shape.x + this.shape.w / 2, this.shape.y + this.shape.h / 2);

		el.setAttribute('class', 'selection-bounds');
		el.setAttribute('id', this.id);
		el.setAttribute('transform', transform);

		// create path
		var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

		path.setAttribute('fill', 'none');
		path.setAttribute('stroke', '#4F80FF');
		path.setAttribute('shape-rendering', 'crispEdges');
		path.setAttribute('style', 'pointer-events:none');

		// defines the path to be drawn
		var d = '';

		// move to x,y
		var d = 'M' + this.shape.x + ',' + this.shape.y + ' ';
		// draw lines to
		d += 'L' + this.shape.x + ',' + (this.shape.y + this.shape.h);
		d += ' ' + (this.shape.x + this.shape.w) + ',' + (this.shape.y + this.shape.h);
		d += ' ' + (this.shape.x + this.shape.w) + ',' + this.shape.y;
		// close path
		d += ' z';

		path.setAttribute('d', d);

		// add path to element
		el.appendChild(path);

		// groups handlers
		var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');

		g.setAttribute('display', 'inline');

		// define base handler
		var rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');

		rect.setAttribute('width', 8);
		rect.setAttribute('height', 8);
		rect.setAttribute('fill', '#4F80FF');
		rect.setAttribute('stroke-width', 0);
		rect.setAttribute('pointer-events', 'all');

		/**
		 * RESIZE
		 */
		
		// push NW handler
		var nw = rect.cloneNode();

		nw.setAttribute('class', 'resize-nw');
		nw.setAttribute('style', 'cursor:nw-resize');
		nw.setAttribute('x', this.shape.x - 3);
		nw.setAttribute('y', this.shape.y - 3);

		g.appendChild(nw);

		// push N handler
		var n = rect.cloneNode();

		n.setAttribute('class', 'resize-n');
		n.setAttribute('style', 'cursor:n-resize');
		n.setAttribute('x', this.shape.x + this.shape.w / 2 - 4);
		n.setAttribute('y', this.shape.y - 3);

		g.appendChild(n);

		// push NE handler
		var ne = rect.cloneNode();

		ne.setAttribute('class', 'resize-ne');
		ne.setAttribute('style', 'cursor:ne-resize');
		ne.setAttribute('x', this.shape.x + this.shape.w - 5);
		ne.setAttribute('y', this.shape.y - 3);

		g.appendChild(ne);

		// push W handler
		var w = rect.cloneNode();

		w.setAttribute('class', 'resize-w');
		w.setAttribute('style', 'cursor:w-resize');
		w.setAttribute('x', this.shape.x - 3);
		w.setAttribute('y', this.shape.y + this.shape.h / 2 - 4);

		g.appendChild(w);

		// push SW handler
		var sw = rect.cloneNode();

		sw.setAttribute('class', 'resize-sw');
		sw.setAttribute('style', 'cursor:sw-resize');
		sw.setAttribute('x', this.shape.x - 3);
		sw.setAttribute('y', this.shape.y + this.shape.h - 5);

		g.appendChild(sw);

		// push S handler
		var s = rect.cloneNode();

		s.setAttribute('class', 'resize-s');
		s.setAttribute('style', 'cursor:s-resize');
		s.setAttribute('x', this.shape.x + this.shape.w / 2 - 4);
		s.setAttribute('y', this.shape.y + this.shape.h - 5);

		g.appendChild(s);

		// push SE handler
		var se = rect.cloneNode();

		se.setAttribute('class', 'resize-se');
		se.setAttribute('style', 'cursor:se-resize');
		se.setAttribute('x', this.shape.x + this.shape.w - 5);
		se.setAttribute('y', this.shape.y + this.shape.h - 5);

		g.appendChild(se);

		// push E handler
		var e = rect.cloneNode();

		e.setAttribute('class', 'resize-e');
		e.setAttribute('style', 'cursor:e-resize');
		e.setAttribute('x', this.shape.x + this.shape.w - 5);
		e.setAttribute('y', this.shape.y + this.shape.h / 2 - 4);

		g.appendChild(e);

		/**
		 * ROTATION
		 */

		// add handlers to selection
		el.appendChild(g);

		// group rotation handlers
		var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');

		g.setAttribute('display', 'inline');

		rect.setAttribute('width', 12);
		rect.setAttribute('height', 12);
		rect.setAttribute('fill', 'transparent');
		rect.setAttribute('style', 'cursor:alias');

		// push rotation NW handler
		var nw = rect.cloneNode();

		nw.setAttribute('class', 'rotate-nw');
		nw.setAttribute('x', this.shape.x - 20);
		nw.setAttribute('y', this.shape.y - 18);

		g.appendChild(nw);

		// push rotation NE handler
		var ne = rect.cloneNode();

		ne.setAttribute('class', 'rotate-ne');
		ne.setAttribute('x', this.shape.x + this.shape.w + 10);
		ne.setAttribute('y', this.shape.y - 18);

		g.appendChild(ne);

		// push rotation SW handler
		var sw = rect.cloneNode();

		sw.setAttribute('class', 'rotate-sw');
		sw.setAttribute('x', this.shape.x - 20);
		sw.setAttribute('y', this.shape.y + this.shape.h + 8);

		g.appendChild(sw);

		// push rotation SE handler
		var se = rect.cloneNode();

		se.setAttribute('class', 'rotate-se');
		se.setAttribute('x', this.shape.x + this.shape.w + 10);
		se.setAttribute('y', this.shape.y + this.shape.h + 8);

		g.appendChild(se);

		// add rotation handlers to selection
		el.appendChild(g);

		return el;
	}

	/**
	 * Refreshes the selection.
	 *
	 * @return 	void
	 */
	refresh() {
		// get selection element
		var el = this.render();
		
		var selection = jQuery('#' + this.id);

		// update path
		var d = el.children[0].attributes.d.value;
		selection.find('path').attr('d', d);

		var transform = 'rotate(%f %f %f)'.sprintf(this.shape.rotation, this.shape.x + this.shape.w / 2, this.shape.y + this.shape.h / 2);
		selection.attr('transform', transform);

		// update handlers
		jQuery.each(el.children[1].children, function(k, v) {
			// handle with jQuery
			var tmp = jQuery(v);

			selection.find('.' + tmp.attr('class'))
				.attr('x', tmp.attr('x'))
				.attr('y', tmp.attr('y'));
		});

		// update handlers
		jQuery.each(el.children[2].children, function(k, v) {
			// handle with jQuery
			var tmp = jQuery(v);

			selection.find('.' + tmp.attr('class'))
				.attr('x', tmp.attr('x'))
				.attr('y', tmp.attr('y'));
		});
	}

	/**
	 * Destroys the selection.
	 *
	 * @return 	void
	 */
	destroy() {
		jQuery('#' + this.id).remove();
	}
}
