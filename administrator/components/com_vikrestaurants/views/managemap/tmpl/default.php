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

JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen');
JHtml::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('vrehtml.assets.fontawesome');
JHtml::_('vrehtml.assets.toast', 'bottom-right');

RestaurantsHelper::load_svg_framework();

$room   = $this->room;
$tables = $this->tables;

$vik = VREApplication::getInstance();

?>

<div class="ui-border-layout" margin>

	<!-- NORTH -->

	<div class="ui-toolbar-panel">

		<div class="ui-commands-toolbar" id="vre-ui-toolbar">

		</div>

		<div class="ui-toolbar-buttons">
			<button type="button" id="toolbar-btn-save"><i class="fas fa-save"></i></button>
			<button type="button" id="toolbar-btn-exit"><i class="fas fa-sign-out-alt"></i></button>
		</div>
		
	</div>

	<!-- MIDDLE -->

	<div class="ui-content">
		
		<!-- WEST -->

		<div class="ui-commands-panel">
			<!-- selection tool -->
			<button type="button" class="cmd-btn" id="cmd-select"><i class="fas fa-mouse-pointer"></i></button>
			<!-- draw tool -->
			<button type="button" class="cmd-btn" id="cmd-draw"><i class="fas fa-pencil-alt"></i></button>
			<!-- rubber tool -->
			<button type="button" class="cmd-btn" id="cmd-rubber"><i class="fas fa-eraser"></i></button>
			<!-- clone tool -->
			<button type="button" class="cmd-btn" id="cmd-clone"><i class="fas fa-clone"></i></button>
			<!-- search tool -->
			<button type="button" class="cmd-btn" id="cmd-search"><i class="fas fa-search"></i></button>
			<!-- help tool -->
			<button type="button" class="cmd-btn" id="cmd-help"><i class="fas fa-life-ring"></i></button>
		</div>

		<!-- CENTER -->

		<div class="ui-canvas-panel">
			<!-- Learn about this code on MDN: https://developer.mozilla.org/en-US/docs/Web/SVG/Element/g -->
			<svg width="2048" height="2048" id="vre-ui-canvas" xmlns="http://www.w3.org/2000/svg">
				<defs></defs>
			</svg>
		</div>

		<!-- EAST -->

		<div class="ui-inspector-panel" id="vre-ui-inspector">
			
		</div>

	</div>

	<!-- SOUTH -->

	<div class="ui-statusbar-panel" id="vre-ui-statusbar">
		
	</div>

</div>

<form action="index.php?option=com_vikrestaurants" name="adminForm" id="adminForm">

	<input type="hidden" name="option" value="com_vikrestaurants" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="selectedroom" value="<?php echo $this->room['id']; ?>" />
</form>

<?php
// customer details modal
echo JHtml::_(
	'bootstrap.renderModal',
	'jmodal-newmedia',
	array(
		'title'       => JText::_('VRMEDIAFIELDSET4'),
		'closeButton' => true,
		'keyboard'    => true, 
		'bodyHeight'  => 80,
		'url'		  => 'index.php?option=com_vikrestaurants&view=flashupload&tmpl=component',
	)
);
?>

<script>

	// add class to body to detect UI-SVG page with CSS
	jQuery('body').addClass('ui-svg-room');

	jQuery(document).ready(function() {

		var canvas;

		/**
		 * CANVAS DEFAULT DATA
		 */
		var options = {
			showGrid: true,
			gridSize: 30,
			gridSnap: true,
		};

		/**
		 * COMMANDS DEFAULT DATA
		 */
		var cmdOptions = {};

		cmdOptions['cmd-draw'] = {
			shapeDefaultBgColor: 'ff9933',
			shapeDefaultWidth: 120,
			shapeDefaultHeight: 120,
			shapeDefaultRadius: 60,
		};

		// initialize canvas
		canvas = new UICanvas(
			'#vre-ui-canvas',
			new UIInspector('#vre-ui-inspector'),
			new UIToolbar('#vre-ui-toolbar'),
			options
		);

		// setup status bar
		canvas.setStatusBar(new UIStatusBar('#vre-ui-statusbar'));

		// visible commands
		canvas.registerCommand(new UICommandSelect('cmd-select'));
		canvas.registerCommand(new UICommandShape('cmd-draw', cmdOptions['cmd-draw']));
		canvas.registerCommand(new UICommandRubber('cmd-rubber'));
		canvas.registerCommand(new UICommandClone('cmd-clone'));
		canvas.registerCommand(new UICommandSearch('cmd-search'));

		canvas.registerCommand(new UICommandHelp('cmd-help'));

		// hidden commands
		canvas.registerCommand(new UICommandShortcut('cmd-shortcut'));

		// register shortcuts
		canvas.registerShortcut(new UIShortcutCopy());
		canvas.registerShortcut(new UIShortcutPaste());
		canvas.registerShortcut(new UIShortcutRedo());
		canvas.registerShortcut(new UIShortcutRemove());
		canvas.registerShortcut(new UIShortcutSelectAll());
		canvas.registerShortcut(new UIShortcutUndo());

		// setup images
		canvas.addImage(<?php echo json_encode($this->mediaList); ?>);

		UITiles.addTile(
			'sharedtable', 
			'<?php echo VREASSETS_ADMIN_URI; ?>css/images/sharedtable.png',
			function() {
				// reloads all the shared tables once the icon is ready
				for (var k in canvas.shapes) {
					if (!canvas.shapes.hasOwnProperty(k)) {
						continue;
					}

					canvas.shapes[k].refresh();
				}
			}
		);

		// restore saved map
		canvas.unserialize(<?php echo $this->canvasData; ?>);

		/**
		 * APPLY ADDONS
		 */
		var saveCommand = new UICommandSave('toolbar-btn-save', {id: <?php echo $this->room['id']; ?>});
		var exitCommand = new UICommandExit('toolbar-btn-exit');

		// register custom commands as shortcuts
		canvas.registerShortcut(saveCommand);
		canvas.registerShortcut(exitCommand);

		saveCommand.source.on('click', function(event) {
			// activate save shortcut
			saveCommand.activate(canvas);
		});

		exitCommand.source.on('click', function(event) {
			// activate exit shortcut
			exitCommand.activate(canvas);
		});

		// register event only if it is not active
		jQuery(window).on('beforeunload', function(event) {
			
			// attach the message to the event property
			if (canvas.hasChanged()) {
				// The translated message is meant to work only
				// for internal purposes as almost all the browsers 
				// uses their own localised strings.
				var dialogText = 'Do you want to leave the page? Your changes will be lost if you don\'t save them.';

				event.returnValue = dialogText;

				// return the message to trigger the browser prompt
				return dialogText;
			}
		});

		/**
		 * Finally, trigger default command
		 */
		canvas.triggerCommand('cmd-select');

		<?php
		if ($this->debug)
		{
			?>
			// attach canvas to window object to be accessed
			// using the browser inspector
			window.canvas = canvas;
			<?php
		}
		?>

	});

	/**
	 * Register list to catch all the media files that will
	 * be uploaded through the flashupload modal.
	 *
	 * @var array
	 */
	var UPLOADED_MEDIAS = [];

	/**
	 * Add support image uploader.
	 *
	 * @return 	void
	 */
	UIFileDialog.open = function() {
		vrOpenJModal('newmedia', null, true);
	}

	/**
	 * Overwrite modal upload dismiss handler
	 * in order to notify all the subscribers.
	 *
	 * @return 	void
	 */
	function vreDismissHandler() {
		// notify all the observers with the uploaded files
		UIFileDialog.notify(UPLOADED_MEDIAS);
	}

	/**
	 * Display a modal popup.
	 *
	 * @param 	string   id       The modal ID
	 * @param 	mixed    url      The iframe URL.
	 * @param 	boolean  jqmodal  True if modal.
	 *
	 * @return 	void
	 */
	function vrOpenJModal(id, url, jqmodal) {
		// invoke vreDismissHandler() function when closing "flashupload" modal
		<?php echo $vik->bootOpenModalJS('vreDismissHandler'); ?>
	}

</script>
