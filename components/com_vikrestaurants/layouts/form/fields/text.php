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

$label = $displayData['label'];
$value = $displayData['value'];
$cf    = $displayData['field'];
$user  = $displayData['user'];
$class = isset($displayData['class']) ? ' ' . $displayData['class'] : '';

if (empty($value) && VRCustomFields::isEmail($cf))
{
	$value = JFactory::getUser()->email;
}

switch ($cf['rule'])
{
	case VRCustomFields::EMAIL:
		$input_type = 'email';
		break;

	default:
		$input_type = 'text';
}

$class .= strlen($value) ? ' has-value' : '';
$class .= $cf['required'] ? ' required' : '';

// check if we should display the address response box
$address_response_box = isset($displayData['addressResponseBox']) ? (bool) $displayData['addressResponseBox'] : false;

if ($address_response_box && $user && count($user->locations) > 1)
{
	?>
	<div>

		<div class="cf-value cf-dropdown">

			<span class="cf-label">
				
				<?php if ($cf['required']) { ?>

					<span class="vrrequired"><sup>*</sup></span>

				<?php } ?>

				<span id="vrcf<?php echo $cf['id']; ?>"><?php echo $label; ?></span>

			</span>

			<div class="vre-select-wrapper">
				<select
					id="vrtk-user-address-sel"
					class="vr-cf-select vre-select"
				>
					<option value=""><?php echo JText::_('VRTKDELIVERYADDRPLACEHOLDER'); ?></option>
					<?php
					foreach ($user->locations as $addr)
					{ 
						$val = VikRestaurants::deliveryAddressToStr($addr, array('country', 'address_2'));
						?>
						<option value="<?php echo $addr->id; ?>"><?php echo $val; ?></option>
						<?php
					}
					?>
				</select>
			</div>

		</div>

	</div>
	<?php
}
?>

<div>

	<div class="cf-value">

		<?php
		/**
		 * Added a hidden label before the input to fix the auto-complete
		 * bug on Safari, which always expects to have the inputs displayed
		 * after their labels.
		 *
		 * @since 1.8.2
		 */
		?>
		<label for="vrcfinput<?php echo $cf['id']; ?>" style="display: none;"><?php echo $label; ?></label>
		
		<input
			type="<?php echo $input_type; ?>"
			name="vrcf<?php echo $cf['id']; ?>"
			id="vrcfinput<?php echo $cf['id']; ?>"
			value="<?php echo $value; ?>"
			class="vrinput<?php echo $class; ?>"
			size="40"
		/>

		<span class="cf-highlight"><!-- input highlight --></span>

		<span class="cf-bar"><!-- input bar --></span>

		<span class="cf-label">
			
			<?php if ($cf['required']) { ?>

				<span class="vrrequired"><sup>*</sup></span>

			<?php } ?>

			<span id="vrcf<?php echo $cf['id']; ?>"><?php echo $label; ?></span>

		</span>

		<?php
		if ($address_response_box)
		{
			?>
			<div class="vrtk-address-response" style="display: none;"></div>
			<?php
		}
		?>

	</div>
	
</div>
