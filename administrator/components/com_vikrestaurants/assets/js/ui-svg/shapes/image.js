/**
 * UIShapeImage class.
 * Class used to display an image.
 */
class UIShapeImage extends UIShapeRect {

	/**
	 * @override
	 * Method used to render a rectangle.
	 *
	 * @param 	object 	options  A configuration object.
	 *
	 * @return 	mixed 	The rectangle element.
	 */
	render(options) {
		// populate options with state
		var g = super.render(options);

		// populate options with state
		options.href = options.href	? options.href : this.state.bgHref;

		var image = document.createElementNS('http://www.w3.org/2000/svg', 'image');

		image.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', options.href ? options.href : '');

		image.setAttribute('preserveAspectRatio', 'none');

		image.setAttribute('class', 'shape-bg');

		image.setAttribute('x',      this.x + options.strokeWidth / 2);
		image.setAttribute('y',      this.y + options.strokeWidth / 2);
		image.setAttribute('width',  this.w - options.strokeWidth);
		image.setAttribute('height', this.h - options.strokeWidth);

		if (!this.table.published) {
			image.setAttribute('opacity', 0.6);
		}

		// replace rectangle with image
		g.childNodes[0].replaceWith(image);

		// always update bg href state for optimal copy
		this.state.bgHref = options.href;

		return g;
	}

	/**
	 * @override
	 * Method used to refresh the shape.
	 *
	 * @return 	void
	 */
	refresh() {
		// refresh parent too
		super.refresh();

		jQuery(this.getSource())
			.find('image.shape-bg')
				.attr('x', this.x)
				.attr('y', this.y)
				.attr('width', this.w)
				.attr('height', this.h)
				.attr('opacity', this.table.published ? 1 : 0.6);
	}

	/**
	 * @override
	 * Method used to obtain the inspector form.
	 *
	 * @return 	object 	The inspector form.
	 */
	getInspector(include) {
		var _this = this;

		var inspector = {
			bgHref: {
				label: 'Background Image',
				type: 'media',
				options: UIFileDialog.getMedia(),
				searchbar: true,
				upload: true,
				fieldset: 'Shape',
			},
		};
		
		// get inspector from parent
		var parentInspector = super.getInspector();

		// unset background
		delete parentInspector.bgColor;
		// unset roundness
		delete parentInspector.roundness;

		// merge parent inspector with the current one
		inspector = Object.assign(parentInspector, inspector);

		return inspector;
	}

	/**
	 * @override
	 * Method used to save the object state.
	 *
	 * @param 	object 	data 	The form data.
	 *
	 * @return 	void
	 */
	save(data) {
		// keep current background
		var bgHref = this.state.bgHref;

		// update parent (background is always replaced)
		super.save(data);

		if (this.state.bgHref && this.state.bgHref.length > 0) {
			// refresh background image (for undo/redo)
			jQuery('#' + this.id)
				.find('image.shape-bg')
					.attr('xlink:href', this.state.bgHref);
		} else {
			// restore previous background
			this.state.bgHref = bgHref;
		}
	}
}
