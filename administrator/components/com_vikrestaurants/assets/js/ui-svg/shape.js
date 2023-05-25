/**
 * UIShape class.
 * Base class used to handle a shape and the related table.
 */
class UIShape extends UIObject {

	/**
	 * Class constructor.
	 *
	 * @param 	float 	x 	The shape X position.
	 * @param 	float 	y 	The shape Y position.
	 * @param 	float 	w 	The shape width.
	 * @param 	float 	h 	The shape height.
	 */
	constructor(x, y, w, h) {
		super();

		this.x = x ? x : 0;
		this.y = y ? y : 0;
		this.w = w ? w : 0;
		this.h = h ? h : 0;

		this.rotation = 0;

		this.selected = false;

		// initialise table
		this.table = new UITable(this);

		// ignore constraints
		this.constraints = null;
	}

	/**
	 * Sets the x position of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	x 	The shape left position.
	 *
	 * @return 	self
	 */
	setX(x) {
		this.x = parseFloat(x);

		if (this.constraints) {
			this.x = Math.max(this.x, this.constraints.x);

			if (this.x + this.w > this.constraints.w + this.constraints.x) {
				this.x = this.constraints.w + this.constraints.x - this.w;
			}
		}

		// refresh shape
		this.refresh();

		return this;
	}

	/**
	 * Increases the x position of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	x 	The unit to increase. If not specifed,
	 * 						it will be increased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	addX(x) {
		if (x === undefined) {
			x = 1;
		}

		return this.setX(this.x + x);
	}

	/**
	 * Decreases the x position of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	x 	The unit to decrease. If not specifed,
	 * 						it will be decreased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	subX(x) {
		if (x === undefined) {
			x = 1;
		}
		
		return this.setX(this.x - x);
	}

	/**
	 * Sets the center x position.
	 *
	 * @param 	float 	x 	The shape center x position.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	setCenterX(x) {
		return this.setX(x - this.w / 2);
	}

	/**
	 * Sets the y position of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	y 	The shape top position.
	 *
	 * @return 	self
	 */
	setY(y) {
		this.y = parseFloat(y);

		if (this.constraints) {
			this.y = Math.max(this.y, this.constraints.y);

			if (this.y + this.h > this.constraints.h + this.constraints.y) {
				this.y = this.constraints.h + this.constraints.y - this.h;
			}
		}

		// refresh shape
		this.refresh();

		return this;
	}

	/**
	 * Increases the y position of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	y 	The unit to increase. If not specifed,
	 * 						it will be increased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	addY(y) {
		if (y === undefined) {
			y = 1;
		}
		
		return this.setY(this.y + y);
	}

	/**
	 * Decreases the y position of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	y 	The unit to decrease. If not specifed,
	 * 						it will be decreased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	subY(y) {
		if (y === undefined) {
			y = 1;
		}
		
		return this.setY(this.y - y);
	}

	/**
	 * Sets the center y position.
	 *
	 * @param 	float 	y 	The shape center y position.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	setCenterY(y) {
		return this.setY(y - this.h / 2);
	}

	/**
	 * Sets the x and y positions of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	x 	The shape left position.
	 * @param 	float 	y 	The shape top position.
	 *
	 * @return 	self
	 */
	setLocation(x, y) {
		return this.setX(x).setY(y);
	}

	/**
	 * Returns the shape center.
	 *
	 * @return 	object
	 */
	getCenter() {
		return {
			x: this.x + this.w / 2,
			y: this.y + this.h / 2,
		};
	}

	/**
	 * Sets the width of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	w 	The shape width.
	 *
	 * @return 	self
	 */
	setWidth(w) {
		this.w = Math.max(1, parseFloat(w));

		if (this.constraints) {
			if (this.x + this.w > this.constraints.w + this.constraints.x) {
				this.w = this.constraints.w + this.constraints.x - this.x;
			}
		}

		if (this.state.propSize) {
			this.h = this.w;
		}

		// refresh shape
		this.refresh();

		return this;
	}

	/**
	 * Increases the width of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	w 	The unit to increase. If not specifed,
	 * 						it will be increased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	addWidth(w) {
		if (w === undefined) {
			w = 1;
		}

		return this.setWidth(this.w + w);
	}

	/**
	 * Decreases the width of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	w 	The unit to decrease. If not specifed,
	 * 						it will be decreased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	subWidth(w) {
		if (w === undefined) {
			w = 1;
		}
		
		return this.setWidth(this.w - w);
	}

	/**
	 * Sets the height of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	h 	The shape height.
	 *
	 * @return 	self
	 */
	setHeight(h) {
		this.h = Math.max(1, parseFloat(h));

		if (this.constraints) {
			if (this.y + this.h > this.constraints.h + this.constraints.y) {
				this.h = this.constraints.h + this.constraints.y - this.y;
			}
		}

		if (this.state.propSize) {
			this.w = this.h;
		}

		// refresh shape
		this.refresh();

		return this;
	}

	/**
	 * Increases the height of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	h 	The unit to increase. If not specifed,
	 * 						it will be increased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	addHeight(h) {
		if (h === undefined) {
			h = 1;
		}
		
		return this.setHeight(this.h + h);
	}

	/**
	 * Decreases the height of the shape by the given amount.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	h 	The unit to decrease. If not specifed,
	 * 						it will be decreased by 1 px.
	 * 						
	 *
	 * @return 	self
	 */
	subHeight(h) {
		if (h === undefined) {
			h = 1;
		}
		
		return this.setHeight(this.h - h);
	}

	/**
	 * Sets the width and height of the shape.
	 * The source, if any, will be updated too.
	 *
	 * @param 	float 	w 	The shape width.
	 * @param 	float 	h 	The shape height.
	 *
	 * @return 	self
	 */
	setSize(w, h) {
		return this.setWidth(w).setHeight(h);
	}

	/**
	 * Rotates the shape by the specified degrees.
	 *
	 * @param 	float 	deg  The rotation degrees.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	rotate(deg) {
		this.rotation = parseFloat(deg) % 360;

		if (this.rotation < 0) {
			// always use positive degrees
			// -45deg = 315deg (360 + (-45))
			this.rotation = Math.abs(360 + this.rotation);
		}

		// refresh shape
		this.refresh();

		return this;
	}

	/**
	 * Sets the shape constraints.
	 * The shape must be within the specified bounds.
	 *
	 * @param 	float 	x 	The constraints X position.
	 * @param 	float 	y 	The constraints Y position.
	 * @param 	float 	w 	The constraints width.
	 * @param 	float 	h 	The constraints height.
	 */
	setConstraints(x, y, w, h) {
		this.constraints = {
			x: x,
			y: y,
			w: w,
			h: h,
		};

		// re-set current values to be adapted to the new bounds
		this.setX(this.x)
			.setY(this.y)
			.setWidth(this.w)
			.setHeight(this.h);
	}

	/**
	 * Selects or deselects the shape.
	 *
	 * @param 	boolean  s 	True to select the shape, otherwise false.
	 *
	 * @return 	void
	 */
	select(s) {
		// if the shape was selected, destroy it before
		// turning off the selection
		if (this.selected instanceof UISelection) {
			this.selected.destroy();
		}

		this.selected = s ? s : false;
	}

	/**
	 * Checks if this shape intersects the specified rectangle.
	 *
	 * @param 	integer  x 	The rectangle x position.
	 * @param 	integer  y 	The rectangle y position.
	 * @param 	integer  w 	The rectangle width.
	 * @param 	integer  h 	The rectangle height.
	 *
	 * @return 	boolean  True if there is an intersection, otherwise false.
	 */
	intersect(x, y, w, h) {
		if (w === undefined) {
			w = 0;
		}

		if (h === undefined) {
			h = 0;
		}

		return (
			// check horizontal placement
			((x <= this.x && this.x <= x + w)
				|| (this.x <= x && x <= this.x + this.w))
			&&
			// check vertical placement
			((y <= this.y && this.y <= y + h)
				|| (this.y <= y && y <= this.y + this.h))
		);
	}

	/**
	 * Abstract method used to render the shape.
	 *
	 * @param 	object 	options  A configuration object.
	 *
	 * @return 	mixed 	The shape element.
	 */
	render(options) {
		// inherit in children classes
	}

	/**
	 * Abstract method used to refresh the shape.
	 *
	 * @return 	void
	 */
	refresh() {
		if (this.selected instanceof UISelection) {
			// refresh selection too
			this.selected.refresh();
		}
	}

	/**
	 * Abstract method used to access the source of the shape.
	 *
	 * @return 	mixed 	The shape source.
	 */
	getSource() {
		// inherit in children classes
	}

	/**
	 * @override
	 * Clones this object by returning a new instance
	 * with the same properties.
	 *
	 * @return 	UIClonable
	 */
	clone() {
		// clone object
		var clone = super.clone();

		// clone table too
		clone.table = clone.table.clone();

		// re-attach new shape to clone table
		clone.table.shape = clone;

		// unset ID from shape
		delete clone.id;

		return clone;
	}
}
