function vreOpenPopup(link) {
	return jQuery.fancybox.open({
		src:  link,
		type: 'iframe',
		opts: {
			iframe: {
				css: {
					width:  '95%',
					height: '95%',
				},
			},
		},
	});
}

function vreOpenModalImage(link) {
	var data = null;

	if (Array.isArray(link)) {
		data = [];
		// display gallery
		for (var i = 0; i < link.length; i++) {
			// extract caption
			var caption = link[i].split('/').pop();
			var match   = caption.match(/(.*?)\.[a-z0-9.]{2,}/i)

			if (match) {
				caption = match[1];
			}

			// make caption human readable
			caption = caption.replace(/[-_]+/, ' ').split(' ').filter(function(word) {
				return word.length ? true : false;
			}).map(function(word) {
				return word[0].toUpperCase() + word.substr(1);
			}).join(' ');

			data.push({
				src:  link[i],
				type: 'image',
				opts : {
					caption : caption,
					thumb   : link[i].replace(/\/media\//, '/media@small/'),
				},
			});
		}
	} else {
		// display single image
		data = {
			src:  link,
			type: 'image',
		};
	}

	return jQuery.fancybox.open(data);
}

/**
 * DEBOUNCE
 */

function __debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

/**
 * OVERLAYS
 */

function openLoadingOverlay(lock, message) {

	var _html = '';

	if( message !== undefined ) {
		_html += '<div class="vr-loading-box-message">'+message+'</div>';
	}

	jQuery('body').append('<div class="vr-loading-overlay'+(lock ? ' lock' : '')+'">'+_html+'<div class="vr-loading-box"></div></div>');
}

function closeLoadingOverlay() {
	jQuery('.vr-loading-overlay').remove();
}

/**
 * TAKE-AWAY
 */

function vrIsCartPublished() {
	return typeof VIKRESTAURANTS_CART_INSTANCE !== 'undefined';
}
