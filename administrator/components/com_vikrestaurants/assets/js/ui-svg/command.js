/**
 * UICommand class.
 * Inherits the shortcut class as a command (a button) needs to 
 * implement the same methods provided by the parent (init, activate, shortcut).
 */
class UICommand extends UIShortcut {

	/**
	 * Class constructor.
	 *
	 * @param 	string 	selector 	The selector used to access the DOM element.
	 * @param 	object 	config  	The configuration object.
	 */
	constructor(selector, config) {
		super();

		this.id     = selector;
		this.source = jQuery('#' + selector);
		this.active = false;

		// configuration state
		this.config = typeof config === 'object' ? config : {};
	}

	/**
	 * @override
	 * Abstract method used to initialize the command.
	 * The canvas is passed as argument in order to
	 * extend the default functionalities, such as a new event.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	init(canvas) {
		super.init(canvas);

		// setup title
		var title = this.getFullTitle();

		if (title && this.source) {
			this.source.attr('title', title);
		}
	}

	/**
	 * @override
	 * Activates the command.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	activate(canvas) {
		this.active = true;

		// mark as active
		this.source.addClass('active');
	}

	/**
	 * Deactivates the command.
	 *
	 * @param 	UICanvas  canvas
	 *
	 * @return 	void
	 */
	deactivate(canvas) {
		this.active = false;

		// unmark active class
		this.source.removeClass('active');
	}

	/**
	 * Returns the button title, including the shortcut.
	 *
	 * @param 	string 	title 	A temporary title if specified.
	 * 							Otherwise the command title will be used.
	 *
	 * @return 	string
	 */
	getFullTitle(title)
	{
		title   = title ? title : this.getTitle();
		var cmd = this.getShortcut();

		// translate title
		title = UILocale.getInstance().get(title);

		if (title && cmd && cmd.length)
		{
			cmd = cmd.map(function(k) {
				switch (k) {
					case 'alt':   k = "&#8997;"; break;
					case 'ctrl':  k = "&#8963;"; break;
					case 'shift': k = "&#8679;"; break;
					case 'meta':  k = "&#8984;"; break;
					default: 	  k = k.toUpperCase();
				}

				return k;
			});

			cmd = cmd.join('');

			if (cmd.length == 1) {
				cmd = '(' + cmd + ')';
			}

			title += '  ' + cmd;

			// capture HTML entities
			var d = document.createElement("div");
			d.innerHTML = title;
			title = d.innerText || d.text || d.textContent;
		}

		return title;
	}

	/**
	 * Returns the command title.
	 *
	 * @return string
	 */
	getTitle() {
		// inherit in children classes
		return '';
	}

	/**
	 * @override
	 * Returns the shortcut that can be used to activate the command.
	 * The array must contain one and only one character or symbol.
	 * The array may contain one ore more modifiers, which must be specified first.
	 *
	 * @return 	array 	A list of modifiers and characters.
	 */
	getShortcut() {
		// no shortcut by default
		return [];
	}

	/**
	 * Abstract method used to obtain the toolbar form.
	 *
	 * @return 	object 	The toolbar form.
	 */
	getToolbar() {
		// inherit in children classes
		return {};
	}

	/**
	 * Method used to obtain the object configuration.
	 *
	 * @return 	object 	The configuration state.
	 */
	getConfig() {
		return this.config;
	}

	/**
	 * Method used to save the object state.
	 *
	 * @param 	object 	data 	The form data.
	 *
	 * @return 	void
	 */
	save(data) {
		this.config = data;
	}
	
}
