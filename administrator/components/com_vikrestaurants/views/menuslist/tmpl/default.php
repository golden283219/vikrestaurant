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

$vik = VREApplication::getInstance();

$cardLayout = new JLayoutFile('blocks.card');

?>

<div style="padding: 10px;">
	<?php
	if (count($this->menus) == 0)
	{
		echo $vik->alert(JText::_('VRNOMENU'));
	}
	else
	{
		?>
		<div class="vre-cards-container">

			<?php
			foreach ($this->menus as $menu)
			{
				if (empty($menu->image) || !is_file(VREMEDIA . DIRECTORY_SEPARATOR . $menu->image))
				{
					if ($this->group == 1)
					{
						// use default menu icon for restaurant
						$menu->image = VREMEDIA_URI . 'menu_default_icon.jpg';
					}
					else
					{
						// use default menu icon for take-away
						$menu->image = VREASSETS_ADMIN_URI . 'images/product-placeholder.png';
					}
				}
				else
				{
					// use menu image URI
					$menu->image = VREMEDIA_URI . $menu->image;
				}

				$displayData = array();
				$displayData['image']   = $menu->image;
				$displayData['primary'] = $menu->name;
				
				?>
				<div class="vre-card-fieldset">
					<?php echo $cardLayout->render($displayData); ?>
				</div>
				<?php
			}
			?>

		</div>
		<?php
	}
	?>
</div>
