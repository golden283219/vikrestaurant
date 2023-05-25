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

JHtml::_('vrehtml.assets.googlemaps', null, 'places');

$locations = $this->customer->locations;

$vik = VREApplication::getInstance();

$deliveryLayout = new JLayoutFile('blocks.card');
?>

<div class="vr-delivery-locations-container vre-cards-container">

	<?php
	for ($i = 0; $i < count($locations); $i++)
	{
		$loc = $locations[$i];
		?>
		<div class="delivery-fieldset vre-card-fieldset" id="delivery-fieldset-<?php echo $i; ?>">

			<?php
			$displayData = array();
			$displayData['id'] = 'delivery-card-' . $i;

			// fetch image
			$displayData['image'] = VREASSETS_ADMIN_URI . 'images/map-loading.png';

			if ($loc->latitude || $loc->longitude)
			{
				$options = array(
					// define image center
					'center' => array(
						'lat' => $loc->latitude,
						'lng' => $loc->longitude,
					),
					// define image size (800x400)
					'size' => array(
						'width'  => 640,
						'height' => 300,
					),
					// use default image
					'default' => $displayData['image'],
				);

				// fetch map image through Google
				$displayData['image'] = JHtml::_('vrehtml.site.googlemapsimage', $options);
			}

			// fetch badge
			switch ($loc->type)
			{
				case 1:
					$icon = 'home';
					break;

				case 2:
					$icon = 'briefcase';
					break;

				default:
					$icon = 'ellipsis-h';
			}

			$displayData['badge'] = '<i class="fas fa-' . $icon . '"></i>';

			// fetch primary text
			$parts = array(
				trim($loc->address . ' ' . $loc->address_2),
				$loc->zip,
			);

			$displayData['primary'] = implode(', ', array_filter($parts));

			if (strlen($loc->note))
			{
				$displayData['primary'] .= $vik->createPopover(array(
					'title'   => JText::_('VRMANAGERESCODE5'),
					'content' => $loc->note,
				));
			}

			// fetch secondary text
			$parts = array(
				$loc->city,
				$loc->state,
				$loc->country,
			);

			$displayData['secondary'] = implode(', ', array_filter($parts));

			// fetch edit button
			$displayData['edit'] = false;

			// render layout
			echo $deliveryLayout->render($displayData);
			?>
		</div>
		<?php
	}
	?>

</div>
