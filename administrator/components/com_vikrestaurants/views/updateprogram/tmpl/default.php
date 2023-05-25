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

JHtml::_('vrehtml.assets.fontawesome');

$vik = VREApplication::getInstance();

?>

<form name="adminForm" action="index.php" method="post" id="adminForm">

	<?php echo $vik->openCard(); ?>
	
		<div class="span12">
			<?php echo $vik->openFieldset($this->version->shortTitle); ?>

				<div class="control"><strong><?php echo $this->version->title; ?></strong></div>

				<div class="control" style="margin-top: 10px;">
					<button type="button" class="btn btn-primary" onclick="downloadSoftware(this);">
						<?php echo JText::_($this->version->compare == 1 ? 'VRDOWNLOADUPDATEBTN1' : 'VRDOWNLOADUPDATEBTN0'); ?>
					</button>
				</div>

				<div class="control vr-box-error" id="update-error" style="display: none;margin-top: 10px;"></div>

				<?php
				if (isset($this->version->changelog) && count($this->version->changelog))
				{
					?>
					<div class="control vr-update-changelog" style="margin-top: 10px;">
						<?php echo $this->digChangelog($this->version->changelog); ?>
					</div>
					<?php
				}
				?>

			<?php echo $vik->closeFieldset(); ?>
		</div>

	<?php echo $vik->closeCard(); ?>

	<input type="hidden" name="view" value="updateprogram" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants"/>
</form>

<?php
JText::script('VRSYSTEMCONNECTIONERR');
JText::script('VRE_UPDATE_PROGRAM_WAIT_MESSAGE');
?>

<script type="text/javascript">

	var REQUEST_XHR = null;

	function downloadSoftware(btn) {
		if (UIAjax.isDoing(REQUEST_XHR)) {
			// already requesting
			return;
		}

		switchRunStatus(true, btn);
		setError(null);

		REQUEST_XHR = UIAjax.do(
			'index.php?option=com_vikrestaurants&task=updateprogram.launch&tmpl=component',
			{},
			function(resp) {
				// decode object
				var obj = jQuery.parseJSON(resp);
				
				// check if we were able to decode the JSON string
				if (obj === null) {
					// connection failed. Something gone wrong while decoding JSON
					alert(Joomla.JText._('VRSYSTEMCONNECTIONERR'));
				}
				// make sure the request was successful
				else if (obj.status) {
					// redirect to dashboard
					document.location.href = 'index.php?option=com_vikrestaurants';
					return;

				}
				// unable to validate the license
				else {
					console.error(obj);

					if (obj.hasOwnProperty('error')) {
						setError(obj.error);
					} else {
						setError('Your website does not own a valid support license!<br />Please visit <a href="https://extensionsforjoomla.com" target="_blank">extensionsforjoomla.com</a> to purchase a license or to receive assistance.');
					}
				}

				// dismiss loading dialog
				switchRunStatus(false, btn);
			},
			function(resp) {
				console.error(resp);
				alert(Joomla.JText._('VRSYSTEMCONNECTIONERR'));

				switchRunStatus(false, btn);
			}
		); 
	}

	function switchRunStatus(running, btn) {
		jQuery(btn).prop('disabled', running);

		if (running) {
			// start loading
			openLoadingOverlay(true, Joomla.JText._('VRE_UPDATE_PROGRAM_WAIT_MESSAGE'));
		} else {
			// stop loading
			closeLoadingOverlay();
		}
	}

	function setError(err) {
		if (err !== null && err !== undefined && err.length) {
			jQuery('#update-error').show();
		} else {
			jQuery('#update-error').hide();
		}

		jQuery('#update-error').html(err);
	}

</script>
