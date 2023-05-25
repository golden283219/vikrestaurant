/**
 * UILocale Joomla class.
 * Class used to translate strings in using Joomla language.
 */
class UILocaleJoomla extends UILocale {

	/**
	 * @override
	 * Class constructor.
	 */
	constructor() {
		super();

		/**
		 * CORE
		 */

		// js/canvas.js @ UICanvas::add()
		this.register('Shape added' 			, 'VRE_UISVG_SHAPE_ADDED');

		// js/canvas.js @ UICanvas::remove()
		this.register('Shape removed' 	  		, 'VRE_UISVG_SHAPE_REMOVED');
		this.register('%d shapes removed' 		, 'VRE_UISVG_N_SHAPES_REMOVED');

		// js/canvas.js @ UICanvas::select()
		this.register('Shape selected' 	   		, 'VRE_UISVG_SHAPE_SELECTED');
		this.register('%d shapes selected' 		, 'VRE_UISVG_N_SHAPES_SELECTED');

		// js/canvas.js @ UICanvas::copy()
		this.register('Shape copied' 	 		, 'VRE_UISVG_SHAPE_COPIED');
		this.register('%d shapes copied' 		, 'VRE_UISVG_N_SHAPES_COPIED');

		// js/canvas.js @ UICanvas::paste()
		this.register('Shape pasted' 	 		, 'VRE_UISVG_SHAPE_PASTED');
		this.register('%d shapes pasted' 		, 'VRE_UISVG_N_SHAPES_PASTED');

		// js/inspector.js @ UIInspector::save()
		this.register('Element saved' 	  		, 'VRE_UISVG_ELEMENT_SAVED');
		this.register('%d elements saved' 		, 'VRE_UISVG_N_ELEMENTS_SAVED');

		// js/state/actions/object.js @ UIStateActionObject::execute()
		this.register('Element restored' 	 	, 'VRE_UISVG_ELEMENT_RESTORED');
		this.register('%d elements restored' 	, 'VRE_UISVG_N_ELEMENTS_RESTORED');

		/**
		 * COMMANDS
		 */

		// js/commands/clone.js @ UICommandClone::getTitle()
		this.register('Clone a shape' 	  , 'VRE_UISVG_CLONE_CMD_TITLE');
		
		// js/commands/clone.js @ UICommandClone::getToolbar()
		this.register('Keep cloning' 	  , 'VRE_UISVG_CLONE_CMD_PARAM_KEEP_CLONING');
		this.register('Auto select'  	  , 'VRE_UISVG_CLONE_CMD_PARAM_AUTO_SELECT');

		// js/commands/help.js @ UICommandHelp::getTitle()
		this.register('Help' 	  		  , 'JTOOLBAR_HELP');

		// js/commands/rubber.js @ UICommandRubber::getTitle()
		this.register('Remove shapes'	  , 'VRE_UISVG_REMOVE_CMD_TITLE');

		// js/commands/search.js @ UICommandSearch::activate()
		this.register('Type something'    , 'VRE_UISVG_SEARCH_CMD_PLACEHOLDER');
		this.register('No tables found'   , 'VRE_UISVG_SEARCH_CMD_RESULT');

		// js/commands/search.js @ UICommandSearch::getTitle()
		this.register('Search tables' 	  , 'VRE_UISVG_SEARCH_CMD_TITLE');

		// js/commands/select.js @ UICommandSelect::commitUndoRedoAction()
		this.register('Shape moved'  	  , 'VRE_UISVG_SHAPE_MOVED');
		this.register('%d shapes moved'   , 'VRE_UISVG_N_SHAPES_MOVED');
		this.register('Shape resized'  	  , 'VRE_UISVG_SHAPE_RESIZED');
		this.register('%d shapes resized' , 'VRE_UISVG_N_SHAPES_RESIZED');
		this.register('Shape rotated'  	  , 'VRE_UISVG_SHAPE_ROTATED');
		this.register('%d shapes rotated' , 'VRE_UISVG_N_SHAPES_ROTATED');

		// js/commands/select.js @ UICommandSelect::getTitle()
		this.register('Selection'  		  , 'VRE_UISVG_SELECT_CMD_TITLE');

		// js/commands/select.js @ UICommandSelect::getToolbar()
		this.register('Simple selection'  , 'VRE_UISVG_SELECT_CMD_PARAM_SIMPLE_SELECTION');
		this.register('Reverse selection' , 'VRE_UISVG_SELECT_CMD_PARAM_REVERSE_SELECTION');

		// js/commands/shape.js @ UICommandShape::getTitle()
		this.register('Add new shapes'	  , 'VRE_UISVG_NEW_CMD_TITLE');

		// js/commands/shape.js @ UICommandShape::getToolbar()
		this.register('Shape type' 		  , 'VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE');
		this.register('Rectangle' 		  , 'VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE_RECT');
		this.register('Circle' 			  , 'VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE_CIRCLE');
		this.register('Image' 			  , 'VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE_IMAGE');

		/**
		 * FORM FIELDS
		 */

		// js/form/fields/media.js @ UIFormFieldMedia::getInput()
		this.register('Search'  	   , 'VRE_UISVG_SEARCH');
		this.register('No media found' , 'VRE_UISVG_NO_MEDIA');
		this.register('Upload Media'   , 'VRE_UISVG_UPLOAD_MEDIA');

		/**
		 * SHORTCUTS
		 */

		// js/shortcuts/redo.js @ UIShortcutRedo::activate()
		this.register('Nothing to redo' , 'VRE_UISVG_NO_REDO');
		// js/shortcuts/undo.js @ UIShortcutUndo::activate()
		this.register('Nothing to undo' , 'VRE_UISVG_NO_UNDO');

		/**
		 * TABLE INSPECTOR
		 */

		// js/table.js @ UITable::getInspector()
		this.register('Table' 		  , 'VRE_UISVG_TABLE');
		this.register('ID' 			  , 'JGLOBAL_FIELD_ID_LABEL');
		this.register('Name' 		  , 'VRMANAGETABLE1');
		this.register('Min Capacity'  , 'VRMANAGETABLE2');
		this.register('Max Capacity'  , 'VRMANAGETABLE3');
		this.register('Can be shared' , 'VRMANAGETABLE12');

		/**
		 * CANVAS INSPECTOR
		 */

		// js/canvas.js @ UICanvas::getInspector()
		this.register('Canvas'				 , 'VRE_UISVG_CANVAS');
		this.register('Layout'				 , 'VRE_UISVG_LAYOUT');
		this.register('Width' 				 , 'VRE_UISVG_WIDTH');
		this.register('Height'				 , 'VRE_UISVG_HEIGHT');
		this.register('Proportional Size' 	 , 'VRE_UISVG_PROP_SIZE');
		this.register('Background' 			 , 'VRE_UISVG_BACKGROUND');
		this.register('None' 				 , 'VRE_UISVG_NONE');
		this.register('Image' 				 , 'VRE_UISVG_IMAGE');
		this.register('Color' 				 , 'VRE_UISVG_COLOR');
		this.register('Mode' 				 , 'VRE_UISVG_MODE');
		this.register('Repeat' 				 , 'VRE_UISVG_REPEAT');
		this.register('Repeat Horizontally'	 , 'VRE_UISVG_HOR_REPEAT');
		this.register('Repeat Vertically'	 , 'VRE_UISVG_VER_REPEAT');
		this.register('Cover'				 , 'VRE_UISVG_COVER');
		this.register('Display Grid'		 , 'VRE_UISVG_SHOW_GRID');
		this.register('Size'				 , 'VRE_UISVG_SIZE');
		this.register('Snap to Grid'		 , 'VRE_UISVG_GRID_SNAP');
		this.register('Enable constraints'	 , 'VRE_UISVG_GRID_CONSTRAINTS');
		this.register('Constraints Accuracy' , 'VRE_UISVG_GRID_CONSTRAINTS_ACCURACY');
		this.register('High' 				 , 'VRE_UISVG_HIGH');
		this.register('Normal' 				 , 'VRE_UISVG_NORMAL');
		this.register('Low' 				 , 'VRE_UISVG_LOW');

		this.register(
			'When checked, the width and height will have always the same value.',
			'VRE_UISVG_PROP_SIZE_DESCRIPTION'
		);

		this.register(
			'Select the background image you need to use from the collection below.',
			'VRE_UISVG_IMAGE_DESCRIPTION'
		);

		this.register(
			'Enable this value to align/snap the shapes to the grid.',
			'VRE_UISVG_GRID_SNAP_DESCRIPTION'
		);

		this.register(
			'Constraints help shapes aligning on the grid.',
			'VRE_UISVG_GRID_CONSTRAINTS_DESCRIPTION'
		);

		this.register(
			'The lower the accuracy, the easier the alignment of the shapes.',
			'VRE_UISVG_GRID_CONSTRAINTS_ACCURACY_DESCRIPTION'
		);

		/**
		 * RECT INSPECTOR
		 */

		// js/shapes/rect.js @ UIShapeRect::getInspector()
		this.register('Shape'			 , 'VRE_UISVG_SHAPE');
		this.register('Position X'		 , 'VRE_UISVG_POSX');
		this.register('Position Y'		 , 'VRE_UISVG_POSY');
		this.register('Rotation'		 , 'VRE_UISVG_ROTATION');
		this.register('Roundness'		 , 'VRE_UISVG_ROUNDNESS');
		this.register('Background Color' , 'VRE_UISVG_BACKGROUND_COLOR');
		this.register('Foreground Color' , 'VRE_UISVG_FOREGROUND_COLOR');


		this.register(
			'Roundness is the measure of how closely a shape approaches that of a mathematically perfect circle.',
			'VRE_UISVG_ROUNDNESS_DESCRIPTION'
		);

		/**
		 * CIRCLE INSPECTOR
		 */

		// js/shapes/circle.js @ UIShapeCircle::getInspector()
		this.register('Radius' , 'VRE_UISVG_RADIUS');

		/**
		 * IMAGE INSPECTOR
		 */

		// js/shapes/image.js @ UIShapeImage::getInspector()
		this.register('Background Image' , 'VRE_UISVG_BACKGROUND_IMAGE');
	}
	
	/**
	 * @override
	 * Registers a new translation.
	 * 
	 * @param 	string 	key  The original text.
	 * @param 	string 	val  The translated text.
	 *
	 * @return 	self 	This object to support chaining.
	 */
	register(key, val) {
		// push translated value using JText object
		return super.register(key, Joomla.JText._(val));
	}
}

// Push locale within the map
UILocale.classMap['joomla'] = UILocaleJoomla;

// always load Joomla language
UILocale.getInstance('joomla');
