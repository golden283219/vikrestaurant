/**
 * UILocale it-IT class.
 * Class used to translate strings in Italian language.
 */
class UILocaleIt_IT extends UILocale {

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
		this.register('Shape added' 			, 'Figura aggiunta');

		// js/canvas.js @ UICanvas::remove()
		this.register('Shape removed' 	  		, 'Figura eliminata');
		this.register('%d shapes removed' 		, '%d figure eliminate');

		// js/canvas.js @ UICanvas::select()
		this.register('Shape selected' 	   		, 'Figura selezionata');
		this.register('%d shapes selected' 		, '%d figure selezionate');

		// js/canvas.js @ UICanvas::copy()
		this.register('Shape copied' 	 		, 'Figura copiata');
		this.register('%d shapes copied' 		, '%d figure copiate');

		// js/canvas.js @ UICanvas::paste()
		this.register('Shape pasted' 	 		, 'Figura incollata');
		this.register('%d shapes pasted' 		, '%d figure incollate');

		// js/inspector.js @ UIInspector::save()
		this.register('Element saved' 	  		, 'Elemento salvato');
		this.register('%d elements saved' 		, '%d elementi salvati');

		// js/state/actions/object.js @ UIStateActionObject::execute()
		this.register('Element restored' 	 	, 'Elemento ripristinato');
		this.register('%d elements restored' 	, '%d elementi ripristinati');

		/**
		 * COMMANDS
		 */

		// js/commands/clone.js @ UICommandClone::getTitle()
		this.register('Clone a shape' 	  , 'Clona una figura');
		
		// js/commands/clone.js @ UICommandClone::getToolbar()
		this.register('Keep cloning' 	  , 'Continua a duplicare');
		this.register('Auto select'  	  , 'Seleziona duplicato');

		// js/commands/help.js @ UICommandHelp::getTitle()
		this.register('Help' 	  		  , 'Aiuto');

		// js/commands/rubber.js @ UICommandRubber::getTitle()
		this.register('Remove shapes'	  , 'Elimina figure');

		// js/commands/search.js @ UICommandSearch::activate()
		this.register('Type something'    , 'Scrivi qualcosa');
		this.register('No tables found'   , 'Nessun tavolo trovato');

		// js/commands/search.js @ UICommandSearch::getTitle()
		this.register('Search tables' 	  , 'Cerca tavoli');

		// js/commands/select.js @ UICommandSelect::commitUndoRedoAction()
		this.register('Shape moved'  	  , 'Figura spostata');
		this.register('%d shapes moved'   , '%d figure spostate');
		this.register('Shape resized'  	  , 'Figura ridimensionata');
		this.register('%d shapes resized' , '%d figure ridimensionate');
		this.register('Shape rotated'  	  , 'Figura ruotata');
		this.register('%d shapes rotated' , '%d figure ruotate');

		// js/commands/select.js @ UICommandSelect::getTitle()
		this.register('Selection'  		  , 'Selezione');

		// js/commands/select.js @ UICommandSelect::getToolbar()
		this.register('Simple selection'  , 'Selezione semplice');
		this.register('Reverse selection' , 'Selezione invertita');

		// js/commands/shape.js @ UICommandShape::getTitle()
		this.register('Add new shapes'	  , 'Aggiungi nuove figure');

		// js/commands/shape.js @ UICommandShape::getToolbar()
		this.register('Shape type' 		  , 'Tipo di figura');
		this.register('Rectangle' 		  , 'Rettangolo');
		this.register('Circle' 			  , 'Cerchio');
		this.register('Image' 			  , 'Immagine');

		/**
		 * FORM FIELDS
		 */

		// js/form/fields/media.js @ UIFormFieldMedia::getInput()
		this.register('Search'  	   , 'Cerca');
		this.register('No media found' , 'Nessun immagine trovata');
		this.register('Upload Media'   , 'Carica immagine');

		/**
		 * SHORTCUTS
		 */

		// js/shortcuts/redo.js @ UIShortcutRedo::activate()
		this.register('Nothing to redo' , 'Niente da ripristinare');
		// js/shortcuts/undo.js @ UIShortcutUndo::activate()
		this.register('Nothing to undo' , 'Niente da annullare');

		/**
		 * TABLE INSPECTOR
		 */

		// js/table.js @ UITable::getInspector()
		this.register('Table' 		  , 'Tavolo');
		this.register('ID' 			  , 'ID');
		this.register('Name' 		  , 'Nome');
		this.register('Min Capacity'  , 'Capacità min.');
		this.register('Max Capacity'  , 'Capacità max.');
		this.register('Can be shared' , 'Può essere condiviso');

		/**
		 * CANVAS INSPECTOR
		 */

		// js/canvas.js @ UICanvas::getInspector()
		this.register('Canvas'				 , 'Canvas');
		this.register('Layout'				 , 'Disposizione');
		this.register('Width' 				 , 'Larghezza');
		this.register('Height'				 , 'Altezza');
		this.register('Proportional Size' 	 , 'Dimensione proporzionale');
		this.register('Background' 			 , 'Sfondo');
		this.register('None' 				 , 'Niente');
		this.register('Image' 				 , 'Immagine');
		this.register('Color' 				 , 'Colore');
		this.register('Mode' 				 , 'Modalità');
		this.register('Repeat' 				 , 'Ripeti');
		this.register('Repeat Horizontally'	 , 'Ripeti orizzontalmente');
		this.register('Repeat Vertically'	 , 'Ripeti verticalmente');
		this.register('Cover'				 , 'Copri tutto lo spazio');
		this.register('Display Grid'		 , 'Mostra griglia');
		this.register('Size'				 , 'Dimensione');
		this.register('Snap to Grid'		 , 'Allinea alla griglia');
		this.register('Enable constraints'	 , 'Abilita vincoli');
		this.register('Constraints Accuracy' , 'Precisione vincoli');
		this.register('High' 				 , 'Alta');
		this.register('Normal' 				 , 'Normale');
		this.register('Low' 				 , 'Bassa');

		this.register(
			'When checked, the width and height will have always the same value.',
			'Se abilitato, la larghezza e l\'altezza avranno sempre lo stesso valore.'
		);

		this.register(
			'Select the background image you need to use from the collection below.',
			'Seleziona l\'immagine di sfondo che vuoi usare dalla collezione qui sotto.'
		);

		this.register(
			'Enable this value to align/snap the shapes to the grid.',
			'Abilita questo campo per allineare e vincolare le figure alla griglia.'
		);

		this.register(
			'Constraints help shapes aligning on the grid.',
			'I vincoli (o linee guida) aiutano ad allineare le figure alla griglia.'
		);

		this.register(
			'The lower the accuracy, the easier the alignment of the shapes.',
			'Più bassa è la precisione, più facile è l\'allineamento delle figure.'
		);

		/**
		 * RECT INSPECTOR
		 */

		// js/shapes/rect.js @ UIShapeRect::getInspector()
		this.register('Shape'			 , 'Figura');
		this.register('Position X'		 , 'Posizione X');
		this.register('Position Y'		 , 'Posizione Y');
		this.register('Rotation'		 , 'Rotazione');
		this.register('Roundness'		 , 'Rotondità');
		this.register('Background Color' , 'Colore di sfondo');
		this.register('Foreground Color' , 'Colore del testo');

		this.register(
			'Roundness is the measure of how closely a shape approaches that of a mathematically perfect circle.',
			'La rotondità è la misura di quanto una forma si avvicini a quella di un cerchio matematicamente perfetto.'
		);

		/**
		 * CIRCLE INSPECTOR
		 */

		// js/shapes/circle.js @ UIShapeCircle::getInspector()
		this.register('Radius' , 'Raggio');

		/**
		 * IMAGE INSPECTOR
		 */

		// js/shapes/image.js @ UIShapeImage::getInspector()
		this.register('Background Image' , 'Immagine di sfondo');
	}
	
}

// Push locale within the map
UILocale.classMap['it-IT'] = UILocaleIt_IT;
