/**
 * Class used to handle screen messages displayed 
 * using a "toast" layout.
 *
 * In case the system is able to display several messages, 
 * it is suggested to always enqueue the messages instead
 * of immediately dispatching them.
 *
 * How to init the toast message: 
 *
 *   ToastMessage.create();
 *
 *   ToastMessage.create(ToastMessage.POSITION_BOTTOM_RIGHT);
 *
 * How to dispatch/enqueue a message:
 *
 *   ToastMessage.dispatch('This is a message');
 *
 *   ToastMessage.enqueue({
 *       text:   'This is a successful message',
 *       status: 1,	
 *       delay:  2000,
 *   });
 */
var ToastMessage = class ToastMessage {

	/**
	 * Initiliazes the class for being used.
	 * Creates the HTML of the toast.
	 * This method is executed only once.
	 *
	 * In case this method is invoked in the head of the
	 * document, it must be placed within a "onready" statement.
	 *
	 * @param 	string  position   The position of the toast.
	 * @param 	string  container  The container to which append the toast.
	 *
	 * @return 	void
	 *
	 * @see 	changePosition() in case it is needed to change the 
	 * 							 position of the toast if it has been
	 * 							 already loaded.
	 */
	static create(position, container) {
		// check if the toast message has been already created
		if (jQuery('#toast-wrapper').length == 0) {

			if (!position) {
				// use default position in case the parameter was not specified
				position = ToastMessage.POSITION_BOTTOM_CENTER;
			}

			if (!container || jQuery(container).length == 0) {
				// fallback to body
				container = 'body';
			}

			// append toast HTML to body
			jQuery(container).append(
				'<div class="toast-wrapper ' + position + '" id="toast-wrapper">\n'+
				'	<div class="toast-message">\n'+
				'		<div class="toast-message-content"></div>\n'+
				'	</div>\n'+
				'</div>\n'
			);

			// handle hover/leave events to prevent the toast
			// disposes itself when the mouse is focusing it
			jQuery('#toast-wrapper').hover(function() {
				// register flag when hovering the mouse
				// above the toast message
				ToastMessage.mouseHover = true;
			}, function() {
				if (ToastMessage.mouseHover) {
					// reset timeout
					clearTimeout(ToastMessage.timerHandler);

					// schedule timeout again to dispose the toast
					ToastMessage.timerHandler = setTimeout(ToastMessage.dispose, ToastMessage.disposeDelay);
				}

				// clear flag
				ToastMessage.mouseHover = false;
			});
		}
	}

	/**
	 * Changes the position of the toast.
	 *
	 * @param 	string  position  The position in which the toast
	 * 							  message will be displayed. See
	 * 							  class constants to check all the
	 * 							  supported positions.
	 *
	 * @return 	void
	 */
	static changePosition(position) {
		if (position) {
			jQuery('#toast-wrapper').attr('class', 'toast-wrapper ' + position);
		}
	}

	/**
	 * Immediately displays the message.
	 * In case the toast was already visible when calling
	 * this method, it will perform a shake effect.
	 *
	 * @param 	mixed 	message  The message to display or an object with the data to use:
	 * 							 - text      string    The message to display;
	 * 							 - status    string    The message status: 0 for error, 
	 * 												   1 for success, 2 for warning, 3 for notice;
	 * 							 - delay     integer   The time for which the toast remains open;
	 * 							 - action    function  If specified, the function to invoke when
	 * 												   clicking the toast box;
	 * 							 - callback  function  If specified, the callback to invoke after
	 * 												   displaying the toast message.
	 * 							 - style     mixed     Either a string or an object of styles to be
	 * 												   applied to the toast message content box.
	 *
	 * @return 	void
	 */
	static dispatch(message) {
		var toast   = jQuery('#toast-wrapper');
		var content = toast.find('.toast-message-content');

		// clear any action previously set
		toast.off('click');

		// create message object in case a string was passed
		if (typeof message === 'string') {
			message = {
				text: message,
				status: 1,
			};
		}

		// attach click event to toast message if specified
		if (message.hasOwnProperty('action') && typeof message.action === 'function') {
			toast.addClass('clickable').on('click', message.action);
		} else {
			toast.removeClass('clickable');
		}

		// perform a "shake" effect in case the toast is already visible
		if (ToastMessage.timerHandler) {
			clearTimeout(ToastMessage.timerHandler);

			toast.removeClass('do-shake').delay(200).queue(function(next){
				jQuery(this).addClass('do-shake');
				next();
			});
		}

		try {
			// try to append specified text as HTML
			content.html(message.text);
		} catch (err) {
			// an error occurred, display generic message
			console.log('toast.dispatch.sethtml', err);
			content.html('Unknown error.');
			message.status = 0;
		}

		// remove all classes that might have been previosuly set
		content.removeClass('error');
		content.removeClass('success');
		content.removeClass('warning');
		content.removeClass('notice');

		var delay = 0;

		// fetch status class and related delay
		switch (message.status) {
			case ToastMessage.ERROR_STATUS:
				content.addClass('error');
				delay = 4500;
				break;

			case ToastMessage.SUCCESS_STATUS:
				content.addClass('success');
				delay = 2500;
				break;

			case ToastMessage.WARNING_STATUS:
				content.addClass('warning');
				delay = 3500;
				break;

			case ToastMessage.NOTICE_STATUS:
				content.addClass('notice');
				delay = 3500;
				break;
		}

		// fetch message content style
		var style = '';

		if (message.hasOwnProperty('style')) {
			// check if we received an object
			if (typeof message.style === 'object') {
				// iterate style properties
				style = [];

				for (var k in message.style) {
					if (message.style.hasOwnProperty(k)) {
						// append rule to string
						style.push(k + ':' + message.style[k] + ';');
					}
				}

				// implode the style array
				style = style.join(' ');
			}
			// otherwise cast to string what we received
			else {
				style = message.style.toString();
			}
		}

		content.attr('style', style);

		// slide in the toast message
		toast.addClass('toast-slide-in');

		// overwrite delay in case it was specified
		if (message.hasOwnProperty('delay')) {
			delay = message.delay;
		}

		// execute callback, if specified
		if (message.hasOwnProperty('callback') && typeof message.callback === 'function') {
			message.callback(message);
		}

		// register delay
		ToastMessage.disposeDelay = delay;

		// register timer to dispose the toast message once the specified
		// delay is passed
		ToastMessage.timerHandler = setTimeout(ToastMessage.dispose, delay);
	}

	/**
	 * Enqueues the message for being displayed once the
	 * current queue of messages is dispatched.
	 * In case the queue is empty, the message will be
	 * immediately displayed.
	 *
	 * @param 	mixed  message  The message to display or an object
	 * 							with the data to use.
	 *
	 * @return 	void
	 */
	static enqueue(message) {
		if (ToastMessage.timerHandler == null) {
			// dispatch directly as there is no active messages
			ToastMessage.dispatch(message);
			return;
		}

		// push the message within the queue
		ToastMessage.queue.push(message);
	}

	/**
	 * Disposes the current visible message.
	 * Once a message is closed, it will pop the
	 * first message in the queue, if any, for
	 * being immediately displayed.
	 *
	 * @param 	boolean  force  True to force the closure.
	 * 
	 * @return 	void
	 */
	static dispose(force) {
		// do not dispose in case the mouse is above the toast
		if (!ToastMessage.mouseHover || force) {
			// fade out the toast message
			jQuery('#toast-wrapper').removeClass('toast-slide-in').removeClass('do-shake');
			// reset handler
			ToastMessage.timerHandler = null;

			// check if the queue is not empty
			if (ToastMessage.queue.length) {
				// wait some time before displaying the new message
				ToastMessage.timerHandler = setTimeout(function() {
					// get first message added
					var message = ToastMessage.queue.shift();

					// unset timer to avoid adding shake effect
					ToastMessage.timerHandler = null;

					// dispatch the message
					ToastMessage.dispatch(message);
				}, 1000);
			}
		}
	}
}

/**
 * Environment variables.
 */
ToastMessage.timerHandler = null;
ToastMessage.mouseHover   = false;
ToastMessage.disposeDelay = 0;
ToastMessage.queue 		  = [];

/**
 * Toast positions constants.
 */
ToastMessage.POSITION_TOP_LEFT      = 'top-left';
ToastMessage.POSITION_TOP_CENTER    = 'top-center';
ToastMessage.POSITION_TOP_RIGHT     = 'top-right';
ToastMessage.POSITION_BOTTOM_LEFT   = 'bottom-left';
ToastMessage.POSITION_BOTTOM_CENTER = 'bottom-center';
ToastMessage.POSITION_BOTTOM_RIGHT  = 'bottom-right';

/**
 * Toast status constants.
 */
ToastMessage.ERROR_STATUS   = 0;
ToastMessage.SUCCESS_STATUS = 1;
ToastMessage.WARNING_STATUS = 2;
ToastMessage.NOTICE_STATUS  = 3;
