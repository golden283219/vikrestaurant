jQuery.fn.viksortablelist = function(data) {
	// create default object
	data = jQuery.extend({
		customDragClass: 'vik-sortable-placeholder',
		handleSelector:  '.sortable-handler',
		handleCursor:    'move',
		direction:       'asc',
	}, data);

	// define dragging handler
	var handle = jQuery(this).find(data.handleSelector).length ? data.handleSelector : '';

	// avoid selection of the table rows
	jQuery(this).find('tr').disableSelection();

	var root = this;

	// make list sortable
	jQuery(this).sortable({
		// properties
		axis:   'y',
		cursor: data.handleCursor,
		handle: handle,
		items:  'tr',
		revert: false,
		// methods
		helper: function(e, ui) {
			// copy columns width from table in order to 
			// display them in full-size
			ui.children().each(function () {
				jQuery(this).width(jQuery(this).width());
			});

			// add class to stylize rows when dragged
			jQuery(ui).addClass(data.customDragClass);

			return ui;
		},
		start: function(e, ui) {
			// register the current position of the element
			root.originalElementIndex = jQuery(root).find('tr').index(jQuery(ui.item));
		},
		stop: function(e, ui) {
			jQuery(ui.item).removeClass(data.customDragClass);

			if (!data.inputSelector) {
				// do not go ahead as the ordering inputs are not available
				return;
			}

			// get list of ordering inputs
			var list = jQuery(data.form).find(data.inputSelector);
			// fetch the new position of the moved element
			var index = jQuery(root).find('tr').index(jQuery(ui.item));

			if (index == root.originalElementIndex) {
				// same position, do nothing
				return;
			}

			var delta;

			// rearrange ordering input fields
			if (index > root.originalElementIndex) {
				// Fetch delta to choose whether to increase or
				// decrease the ordering when rearranging the records.
				// In case of ASCENDING direction, ordering have to be
				// decreased when moved down.
				delta = data.direction == 'asc' ? 1 : -1;

				// get ordering to use
				var ordering = parseInt(jQuery(list[index - 1]).val());
				jQuery(list[index]).val(ordering);

				// the element was moved down, we should switch the ordering
				// with all the previous elements
				for (var i = index - 1; i >= root.originalElementIndex; i--) {
					if (jQuery(list[i]).val() == ordering) {
						// Decrease ordering by one as long as the elements are contiguous.
						// In case of DESCENDING ordering, the amount will be increased instead.
						ordering -= delta;

						jQuery(list[i]).val(ordering);
					} else {
						// decrease the value as there is one or
						// more breaks between the elements
						jQuery(list[i]).val(parseInt(jQuery(list[i]).val()) - delta);
					}
				}
			} else {
				// Fetch delta to choose whether to increase or
				// decrease the ordering when rearranging the records.
				// In case of ASCENDING direction, ordering have to be
				// increased when moved up.
				delta = data.direction == 'asc' ? -1 : 1;

				// get ordering to use
				var ordering = parseInt(jQuery(list[index + 1]).val());
				jQuery(list[index]).val(ordering);

				// the element was moved up, we should switch the ordering
				// with all the next elements
				for (var i = index + 1; i <= root.originalElementIndex; i++) {
					if (jQuery(list[i]).val() == ordering) {
						// Increase ordering by one as long as the elements are contiguous.
						// In case of DESCENDING ordering, the amount will be decreased instead.
						ordering -= delta;

						jQuery(list[i]).val(ordering);
					} else {
						// increase the value as there is one or
						// more breaks between the elements
						jQuery(list[i]).val(parseInt(jQuery(list[i]).val()) - delta);
					}
				}
			}

			if (!data.saveUrl) {
				// do not go ahead as there is no end-point to save the ordering
				return;
			}

			// fetch ID => ordering map
			var order = {};

			jQuery(root).find('tr').each(function() {
				var id    = jQuery(this).find('input[name="cid[]"]').val();
				var value = jQuery(this).find('input[name="order[]"]').val();

				order[id] = value;
			});

			// create post data
			var post = {
				order: order,
				// associative array of filters
				filters: data.filters,
			};

			// make request to save ordering
			UIAjax.do(data.saveUrl, post);
		},
	});
}
