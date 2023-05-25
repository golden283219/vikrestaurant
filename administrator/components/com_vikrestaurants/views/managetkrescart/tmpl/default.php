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

JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.select2');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.toast', 'bottom-right');

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<!-- PRODUCTS -->

		<div class="span8">
			<?php
			echo $vik->openFieldset(JText::_('VRTKORDERCARTFIELDSET2'));
			echo $this->loadTemplate('food');
			echo $vik->closeFieldset();
			?>
		</div>
		
		<!-- CART -->

		<div class="span4">
			<?php
			echo $vik->openFieldset(JText::_('VRTKORDERCARTFIELDSET3'));
			echo $this->loadTemplate('cart');
			echo $vik->closeFieldset();
			?>
		</div>

	<?php echo $vik->closeCard(); ?>
	
	<input type="hidden" name="id_order" value="<?php echo $this->order->id; ?>" />

	<input type="hidden" name="cid[]" value="<?php echo $this->order->id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
// render inspector to add a product to the cart

$footer  = '<button type="button" class="btn btn-success" data-role="save" id="res-product-save">' . JText::_('VRADDTOCART') . '</button>';
$footer .= '<button type="button" class="btn btn-danger" id="res-product-delete" style="float:right;">' . JText::_('VRDELETE') . '</button>';

echo JHtml::_(
	'vrehtml.inspector.render',
	'res-product-inspector',
	array(
		'title'       => JText::_('VRE_ADD_PRODUCT'),
		'closeButton' => true,
		'keyboard'    => false,
		'footer'      => $footer,
		'url'         => '', // it will be filled dinamically
	)
);
