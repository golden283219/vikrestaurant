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

JHtml::_('formbehavior.chosen');

$vik = VREApplication::getInstance();

$vik->addScript(VREASSETS_ADMIN_URI . 'js/wizard.js');
$vik->addStyleSheet(VREASSETS_ADMIN_URI . 'css/wizard.css');
$vik->addStyleSheet(VREASSETS_ADMIN_URI . 'css/percentage-circle.css');

$layout = new JLayoutFile('wizard.step');

// calculate overall progress
$progress = $this->wizard->getProgress();

?>

<form action="index.php?option=com_vikrestaurants" method="post" name="adminForm" id="adminForm">

	<!-- Wizard -->
	<div class="vre-wizard" id="vre-wizard">

		<!-- Wizard toolbar -->
		<div class="vre-wizard-toolbar">

			<!-- Wizard progress -->
			<div class="vre-wizard-progress" id="vre-wizard-progress" style="margin-bottom: 0;">

			</div>

			<!-- Wizard description -->
			<div class="vre-wizard-text">
				<?php echo $vik->alert(JText::_('VRWIZARDWHAT'), 'info', false, array('style' => 'margin: 0')); ?>
			</div>

		</div>

		<!-- Wizard steps container -->
		<div class="vre-wizard-steps">

			<?php
			// scan all the supported/active steps
			foreach ($this->wizard as $step)
			{
				?>
				<!-- Wizard step -->
				<div class="wizard-step-outer" data-id="<?php echo $step->getID(); ?>" style="<?php echo $step->isVisible() ? '' : 'display:none;'; ?>">
					<?php
					// display the step by using an apposite layout
					echo $layout->render(array('step' => $step));
					?>
				</div>
				<?php
			}
			?>

		</div>

	</div>
	
	<input type="hidden" name="view" value="restaurant" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_vikrestaurants" />
</form>

<?php
JText::script('VRWIZARDBTNDONE_DESC');
JText::script('VRSYSTEMCONNECTIONERR');
?>

<script>

	jQuery(document).ready(function() {
		// delegate click event to all buttons with a specific role
		jQuery('#vre-wizard').on('click', '[data-role]', function(event) {
			// executes wizard step according to the button role
			VREWizard.execute(this).then((data) => {
				// update progress
				jQuery('#vre-wizard-progress').percentageCircle('progress', data.progress);

				if (data.progress == 100) {
					// auto-dismiss wizard on completion
					vreDismissWizard();
				}
			}).catch((error) => {
				if (error === false) {
					// suppress error
					return false;
				}

				if (!error) {
					// use default connection lost error
					error = Joomla.JText._('VRSYSTEMCONNECTIONERR');
				}

				// use default system alert to display error
				alert(error);
			});
		});

		// render progress circle
		jQuery('#vre-wizard-progress').percentageCircle({
			progress: <?php echo $progress; ?>,
			size: 'small',
			color: '<?php echo $progress == 100 ? 'green' : null; ?>',
		});

		// set green color on complete
		jQuery('#vre-wizard-progress').on('complete', function() {
			jQuery(this).percentageCircle('color', 'green');
		});
	});

	Joomla.submitbutton = function(task) {
		if (task == 'wizard.done') {
			// ask for a confirmation
			var r = confirm(Joomla.JText._('VRWIZARDBTNDONE_DESC'));

			if (!r) {
				return false;
			}
		}

		// submit form
		Joomla.submitform(task, document.adminForm);
	}

	function vreDismissWizard() {
		UIAjax.do('index.php?option=com_vikrestaurants&task=wizard.done');
	}

	<?php
	if ($progress == 100)
	{
		// wizard completed send AJAX request to dismiss the wizard
		?>
		vreDismissWizard();
		<?php
	}
	?>

</script>
