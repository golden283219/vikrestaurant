<?php
/** 
 * @package     VikRestaurants
 * @subpackage  com_vikrestaurants
 * @author      Matteo Galletti - e4j
 * @copyright   Copyright (C) 2021 e4j - Extensionsforjoomla.com. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://extensionsforjoomla.com
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('vrehtml.sitescripts.animate');
JHtml::_('vrehtml.assets.fancybox');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.toast', 'bottom-center');

$itemid = JFactory::getApplication()->input->get('Itemid', null, 'uint');

$vik = VREApplication::getInstance();

?>

<div class="vrtkitemspagediv">

	<?php
	/**
	 * Displays the top section of the page, containing
	 * the front-end take-away notes, the menus filter
	 * and the date selection.
	 */
	echo $this->loadTemplate('head');
	?>

	<div class="vrtkitemsdiv">

		<?php
		foreach ($this->menus as $menu)
		{
			// keep a reference of the current menu for
			// being used in a sub-template
			$this->forMenu = $menu;

			/**
			 * Displays the current menu as a section and
			 * the list of all its children products.
			 */
			echo $this->loadTemplate('menu');
		}
		?>

	</div>

	<div class="vrtkgotopaydiv">
		<a href="<?php echo JRoute::_('index.php?option=com_vikrestaurants&view=takeawayconfirm' . ($itemid ? '&Itemid=' . $itemid : '')); ?>" class="vrtkgotopaybutton">
			<?php echo JText::_('VRTAKEAWAYORDERBUTTON'); ?>
		</a>
	</div>

</div>

<?php
if (count($this->attributes))
{
	?>
	<div class="vrtk-attributes-legend">
		<?php
		foreach ($this->attributes as $attr)
		{ 
			?>
			<div class="vrtk-attribute-box">
				<img src="<?php echo VREMEDIA_URI . $attr->icon; ?>" />
				<span><?php echo $attr->name; ?></span>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}

/**
 * Creates the popup that will be used to display the details
 * of the products that are going to be added.
 *
 * The popup will be used according to the "Use Items Overlay"
 * setting in the Take-Away configuration.
 */
echo $this->loadTemplate('overlay');

JText::script('VRTKADDITEMSUCC');
JText::script('VRTKADDITEMERR2');
?>

<script>

	var GALLERY_DATA = <?php echo json_encode($this->getGalleryData()); ?>;

	jQuery(function() {
		// adjust gallery data for being used by FontAwesome
		for (var id in GALLERY_DATA.images) {
			if (GALLERY_DATA.images.hasOwnProperty(id)) {
				// iterate images
				for (var i = 0; i < GALLERY_DATA.images[id].length; i++) {
					var img = GALLERY_DATA.images[id][i];

					GALLERY_DATA.images[id][i] = {
						src:  img.uri,
						type: 'image',
						opts: {
							caption: img.caption,
							thumb:   img.thumb,
						},
					};
				}
			}
		}
	});

	function vreOpenGallery(link) {
		// get clicked image
		var img = jQuery(link).find('img');

		if (GALLERY_DATA.groupBy == 'menu') {
			// get menu ID
			var id_menu = img.data('menu');

			if (!GALLERY_DATA.images.hasOwnProperty(id_menu)) {
				return false;
			}

			// open fancybox to show only the items that belong to this menu
			var instance = jQuery.fancybox.open(GALLERY_DATA.images[id_menu]);

			// get clicked image index
			var index = jQuery('a.vremodal img[data-menu="' + id_menu + '"]').index(img);

			if (index > 0) {
				// jump to selected image ('0' turns off the animation)
				instance.jumpTo(index, 0);
			}
		} else {
			// get product ID
			var id_prod = img.data('prod');

			if (!GALLERY_DATA.images.hasOwnProperty(id_prod)) {
				return false;
			}

			// open fancybox to show only the items that belong to this product
			jQuery.fancybox.open(GALLERY_DATA.images[id_prod]);
		}
	}

	function showMoreDesc(id) {
		setDescriptionVisible(id, true);
	}

	function showLessDesc(id) {
		setDescriptionVisible(id, false);
	}

	function setDescriptionVisible(id, status) {
		if (status) {
			jQuery('#vrtkitemshortdescsp' + id).hide();
			jQuery('#vrtkitemlongdescsp' + id).show();
		} else {
			jQuery('#vrtkitemlongdescsp' + id).hide();
			jQuery('#vrtkitemshortdescsp' + id).show();
		}
	}

	function vrInsertTakeAwayItem(id_entry, id_option) {
		var data = {
			id_entry:   id_entry,
			id_option:  id_option,
			item_index: -1,
		};

		vrMakeAddCartRequest(data).then((response) => {
			// do nothing here
		}).catch((error) => {
			// do nothing here
		});
	}

	function vrMakeAddCartRequest(data) {
		// create promise
		return new Promise((resolve, reject) => {
			// make request to add the item within the cart
			UIAjax.do(
				'<?php echo $vik->ajaxUrl('index.php?option=com_vikrestaurants&task=tkreservation.addtocartajax&tmpl=component' . ($itemid ? '&Itemid=' . $itemid : ''), false); ?>',
				data,
				function(resp) {
					// try to decode JSON
					var obj = jQuery.parseJSON(resp);

					var msg = {
						status: 0,
						text:   '',
					};

					if (vrIsCartPublished()) {
						// refresh cart module in case it is published
						vrCartRefreshItems(obj.items, obj.total, obj.discount, obj.finalTotal);
					}

					// resolve promise
					resolve(obj);

					if (obj.message) {
						// use the message fetched by the controller
						msg = obj.message;
					}

					// Display the default successful message only in case there is no message text
					// and the cart is not published (or currently not visible on the screen).
					if (msg.text.length == 0 && (!vrIsCartPublished() || !vrIsCartVisibleOnScreen())) {
						msg.text   = Joomla.JText._('VRTKADDITEMSUCC');
						msg.status = 1;
					}

					if (msg.text.length) {
						// dispatch toast message
						ToastMessage.dispatch(msg);
					}
				},
				function(error) {
					if (!error.responseText || error.responseText.length > 1024) {
						// use default generic error
						error.responseText = Joomla.JText._('VRTKADDITEMERR2');
					}

					// reject promise
					reject(error);

					// raise error
					ToastMessage.dispatch({
						text:   error.responseText,
						status: 0,
					});
				}
			);
		});
	}

</script>
