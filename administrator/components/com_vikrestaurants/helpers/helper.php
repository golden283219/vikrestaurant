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

/**
 * VikRestaurants component helper.
 *
 * @since 1.0
 */
abstract class RestaurantsHelper
{
	/**
	 * Displays the main menu of the component.
	 *
	 * @return 	void
	 *
	 * @see 	printFooter() it is needed to invoke also this method when the menu is displayed.
	 *
	 * @uses 	getActiveView()
	 * @uses 	getAuthorisations()
	 * @uses 	getCheckVersionParams()
	 */
	public static function printMenu()
	{
		$vik = VREApplication::getInstance();

		// load font awesome framework
		JHtml::_('vrehtml.assets.fontawesome');

		$task = self::getActiveView();
		$auth = self::getAuthorisations();

		$base_href = 'index.php?option=com_vikrestaurants';

		// load menu factory
		VRELoader::import('library.menu.factory');

		$board = MenuFactory::createMenu();

		///// DASHBOARD /////

		if ($auth['dashboard']['numactives'] > 0)
		{
			$parent = MenuFactory::createSeparator(JText::_('VRMENUDASHBOARD'), $base_href, $task == 'restaurant');

			$board->push($parent->setCustom('tachometer-alt'));
		}

		///// RESTAURANT /////

		if ($auth['restaurant']['numactives'] > 0)
		{
			$parent = MenuFactory::createSeparator(JText::_('VRMENUTITLEHEADER1'));

			if ($auth['restaurant']['actions']['rooms'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUROOMS'), $base_href . '&view=rooms', $task == 'rooms');
				$parent->addChild($item->setCustom('home'));
			}

			if ($auth['restaurant']['actions']['tables'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUTABLES'), $base_href . '&view=tables', $task == 'tables');
				$parent->addChild($item->setCustom('th'));
			}

			if ($auth['restaurant']['actions']['maps'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUVIEWMAPS'), $base_href . '&view=maps', $task == 'maps');
				$parent->addChild($item->setCustom('map'));
			}

			if ($auth['restaurant']['actions']['products'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUMENUSPRODUCTS'), $base_href . '&view=menusproducts', $task == 'menusproducts');
				$parent->addChild($item->setCustom('hamburger'));
			}

			if ($auth['restaurant']['actions']['menus'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUMENUS'), $base_href . '&view=menus', $task == 'menus');
				$parent->addChild($item->setCustom('bars'));
			}

			if ($auth['restaurant']['actions']['reservations'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENURESERVATIONS'), $base_href . '&view=reservations', $task == 'reservations');
				$parent->addChild($item->setCustom('calendar-check'));
			}

			$board->push($parent->setCustom('utensils'));
		}

		///// OPERATIONS /////

		if ($auth['operations']['numactives'] > 0)
		{
			$parent = MenuFactory::createSeparator(JText::_('VRMENUTITLEHEADER2'));

			if ($auth['operations']['actions']['shifts'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUSHIFTS'), $base_href . '&view=shifts', $task == 'shifts');
				$parent->addChild($item->setCustom('clock'));
			}

			if ($auth['operations']['actions']['specialdays'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUSPECIALDAYS'), $base_href . '&view=specialdays', $task == 'specialdays');
				$parent->addChild($item->setCustom('calendar-alt'));
			}

			if ($auth['operations']['actions']['operators'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUOPERATORS'), $base_href . '&view=operators', $task == 'operators');
				$parent->addChild($item->setCustom('user-tie'));
			}

			$board->push($parent->setCustom('wrench'));
		}

		///// BOOKING /////

		if ($auth['booking']['numactives'] > 0)
		{
			$parent = MenuFactory::createSeparator(JText::_('VRMENUTITLEHEADER3'));

			if ($auth['booking']['actions']['customers'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUCUSTOMERS'), $base_href . '&view=customers', $task == 'customers');
				$parent->addChild($item->setCustom('user'));
			}

			if ($auth['booking']['actions']['reviews'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUREVIEWS'), $base_href . '&view=reviews', $task == 'reviews');
				$parent->addChild($item->setCustom('star'));	
			}

			if ($auth['booking']['actions']['coupons'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUCOUPONS'), $base_href . '&view=coupons', $task == 'coupons');
				$parent->addChild($item->setCustom('gift'));	
			}

			if ($auth['booking']['actions']['invoices'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUINVOICES'), $base_href . '&view=invoices', $task == 'invoices');
				$parent->addChild($item->setCustom('file-pdf'));	
			}

			$board->push($parent->setCustom('bookmark'));
		}

		///// TAKEAWAY /////

		if ($auth['takeaway']['numactives'] > 0)
		{
			$parent = MenuFactory::createSeparator(JText::_('VRMENUTITLEHEADER5'));

			if ($auth['takeaway']['actions']['tkmenus'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUTAKEAWAYMENUS'), $base_href . '&view=tkmenus', $task == 'tkmenus');
				$parent->addChild($item->setCustom('pizza-slice'));
			}

			if ($auth['takeaway']['actions']['tktoppings'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUTAKEAWAYTOPPINGS'), $base_href . '&view=tktoppings', $task == 'tktoppings');
				$parent->addChild($item->setCustom('bacon'));
			}

			if ($auth['takeaway']['actions']['tkdeals'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUTAKEAWAYDEALS'), $base_href . '&view=tkdeals', $task == 'tkdeals');
				$parent->addChild($item->setCustom('ticket-alt'));
			}

			if ($auth['takeaway']['actions']['tkareas'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUTAKEAWAYDELIVERYAREAS'), $base_href . '&view=tkareas', $task == 'tkareas');
				$parent->addChild($item->setCustom('map-marker-alt'));
			}

			if ($auth['takeaway']['actions']['tkorders'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUTAKEAWAYRESERVATIONS'), $base_href . '&view=tkreservations', $task == 'tkreservations');
				$parent->addChild($item->setCustom('shopping-bag'));
			}

			$board->push($parent->setCustom('shopping-basket'));
		}

		///// GLOBAL /////

		if ($auth['global']['numactives'] > 0)
		{
			$parent = MenuFactory::createSeparator(JText::_('VRMENUTITLEHEADER4'));

			if ($auth['global']['actions']['custfields'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUCUSTOMFIELDS'), $base_href . '&view=customf', $task == 'customf');
				$parent->addChild($item->setCustom('filter'));
			}

			if ($auth['global']['actions']['payments'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUPAYMENTS'), $base_href . '&view=payments', $task == 'payments');
				$parent->addChild($item->setCustom('credit-card'));
			}

			if ($auth['global']['actions']['media'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENUMEDIA'), $base_href . '&view=media', $task == 'media');
				$parent->addChild($item->setCustom('camera'));
			}

			if ($auth['global']['actions']['rescodes'])
			{
				$item = MenuFactory::createItem(JText::_('VRMENURESCODES'), $base_href . '&view=rescodes', $task == 'rescodes');
				$parent->addChild($item->setCustom('tags'));
			}

			$board->push($parent->setCustom('layer-group'));
		}

		///// CONFIGURATION /////

		if ($auth['configuration']['numactives'] > 0)
		{
			$parent = MenuFactory::createSeparator(JText::_('VRMENUCONFIG'), $base_href . '&view=editconfig', $task == 'editconfig');

			$board->push($parent->setCustom('cogs'));
		}

		///// CUSTOM /////

		$line_separator = MenuFactory::createCustomItem('line');

		// split
		$board->push($line_separator);
		$board->push(MenuFactory::createCustomItem('split'));

		// check version
		if ($auth['configuration']['numactives'] > 0)
		{
			/**
			 * Detect current platform and use the correct version button:
			 * - VikUpdater for Joomla
			 * - Go To PRO for WordPress
			 *
			 * @since 1.8
			 */
			if (VersionListener::getPlatform() == 'joomla')
			{
				if ($task == 'restaurant' || $task == 'editconfig')
				{
					$board->push($line_separator);
					$board->push(MenuFactory::createCustomItem('version', self::getCheckVersionParams()));
				}
			}
			else if (VersionListener::getPlatform() == 'wordpress')
			{
				// always display license button
				$board->push(MenuFactory::createCustomItem('license'));
			}
		}
		
		///// BUILD MENU /////

		/**
		 * Trigger event to allow the plugins to manipulate the back-end menu of VikRestaurants.
		 *
		 * @param 	MenuShape  &$menu 	The menu to build.
		 *
		 * @return 	void
		 *
		 * @since 	1.8
		 */
		VREFactory::getEventDispatcher()->trigger('onBeforeBuildVikRestaurantsMenu', array(&$board));

		echo $board->build();

		/**
		 * Open body by using the specific menu handler.
		 *
		 * @since 1.8
		 */
		echo $board->openBody();
	}

	/**
	 * Displays the footer of the component.
	 *
	 * @return 	void
	 *
	 * @see 	printMenu() it is needed to invoke also this method when the footer is displayed.
	 */
	public static function printFooter()
	{
		/**
		 * Close body by using the specific menu handler.
		 *
		 * @since 1.8
		 */
		echo MenuFactory::createMenu()->closeBody();
		
		if (VikRestaurants::isFooterVisible())
		{
			/**
			 * Find manufacturer name according to the platform in use.
			 * Display a link in the format [SHORT] - [LONG].
			 *
			 * @since 1.8
			 */
			$manufacturer = VREApplication::getInstance()
				->getManufacturer(array('link' => true, 'short' => true, 'long' => true));

			?>
			<p id="vrestfooter">
				<?php echo JText::sprintf('VRFOOTER', VIKRESTAURANTS_SOFTWARE_VERSION) . ' ' . $manufacturer; ?>
			</p>
			<?php
		}
	}

	/**
	 * In case of missing view, fetches the first 
	 * available one.
	 *
	 * @return 	string  The name of the default view.
	 *
	 * @since 	1.8.3
	 */
	public static function getDefaultView()
	{
		// scan ACL table to find the very first allowed page
		$acl = static::getAuthorisations();

		// iterate sections
		foreach ($acl as $section)
		{
			// iterates actions
			foreach ($section['actions'] as $action => $status)
			{
				// make sure the view is accessible
				if ($status)
				{
					// look for a specific view name
					return isset($section['views'][$action]) ? $section['views'][$action] : $action;
				}
			}
		}

		// the user seems to be unable to access any views
		return null;
	}

	/**
	 * Returns the current active view.
	 * For example, if we are visiting the rooms closures,
	 * the active view will be "rooms", as the closures don't
	 * have a specific menu item.
	 *
	 * @return 	string  The current active view.
	 *
	 * @since 	1.8.3
	 */
	public static function getActiveView()
	{
		$input = JFactory::getApplication()->input;

		// get view/task from request
		$view = $input->get('view', $input->get('task'));

		if (empty($view))
		{
			// get default view
			$view = static::getDefaultView();
		}

		switch($view)
		{
			case 'operatorlogs':
				$view = 'operators';
				break;

			case 'roomclosures':
				$view = 'rooms';
				break;

			case 'tkmenuattr':
				$view = 'tkmenus';
				break;

			case 'tktopseparators':
				$view = 'tktoppings';
				break;

			case 'tkproducts':
				$view = 'tkmenus';
				break;
		}

		return $view;
	}

	/**
	 * Returns the parent to which the task belongs.
	 * For example, if we are visiting the rooms closures,
	 * the parent will be "rooms", as the closures don't
	 * have a specific menu item.
	 *
	 * @return 	string  The current active task.
	 *
	 * @deprecated 1.9  Use RestaurantsHelper::getActiveView() instead.
	 */
	public static function getParentTask()
	{
		return static::getActiveView();
	}

	/**
	 * Returns the arguments used to display a link to check the version.
	 *
	 * @return 	array
	 */
	protected static function getCheckVersionParams()
	{
		$data = array(
			'hn'  => getenv('HTTP_HOST'),
			'sn'  => getenv('SERVER_NAME'),
			'app' => 'com_vikrestaurants',
			'ver' => VIKRESTAURANTS_SOFTWARE_VERSION,
		);

		return array(
			'url' 	=> 'https://extensionsforjoomla.com/vikcheck/?' . http_build_query($data),
			'label' => 'Check Updates',
		);
	}
	
	/**
	 * Loads the base CSS and JS resources.
	 *
	 * @return 	void
	 */
	public static function load_css_js()
	{
		$vik = VREApplication::getInstance();

		/**
		 * Load only jQuery framework provided by the CMS.
		 *
		 * @since 1.8
		 */
		$vik->loadFramework('jquery.framework');
		
		/**
		 * Do not load jQuery UI on Joomla 4.
		 *
		 * @since 1.8.3
		 */
		if (VersionListener::isJoomla4x() === false)
		{
			$vik->addScript(VREASSETS_URI . 'js/jquery-ui.min.js');
			$vik->addStyleSheet(VREASSETS_URI . 'css/jquery-ui.min.css');
		}
		
		$vik->addScript(VREASSETS_URI . 'js/jquery-ui.sortable.min.js');
		
		$vik->addStyleSheet(VREASSETS_ADMIN_URI . 'css/vikrestaurants.css');
		
		$vik->addScript(VREASSETS_ADMIN_URI . 'js/colorpicker.js');
		$vik->addScript(VREASSETS_ADMIN_URI . 'js/eye.js');
		$vik->addScript(VREASSETS_ADMIN_URI . 'js/utils.js');

		// load site script too (before back-end in order to support functions overrides)
		$vik->addScript(VREASSETS_URI . 'js/vikrestaurants.js');
		$vik->addScript(VREASSETS_ADMIN_URI . 'js/vikrestaurants.js');
		
		$vik->addStyleSheet(VREASSETS_ADMIN_URI . 'css/colorpicker.css');

		/**
		 * Loads utils.
		 *
		 * @since 1.8
		 */
		JHtml::_('vrehtml.assets.utils');

		// load platform adapters
		if (VersionListener::isJoomla25())
		{
			$vik->addStyleSheet(VREASSETS_ADMIN_URI . 'css/adapter/J25/vre-admin.css');
		}
		else if (VersionListener::isJoomla3x())
		{
			$vik->addStyleSheet(VREASSETS_ADMIN_URI . 'css/adapter/J30/vre-admin.css');
		}
		else if (VersionListener::isJoomla40())
		{
			$vik->addScript(VREASSETS_ADMIN_URI . 'js/adapter/J40.js');
			$vik->addStyleSheet(VREASSETS_ADMIN_URI . 'css/adapter/J40/vre-admin.css');

			// register adapter scripts on DOM loaded
			$app = JFactory::getApplication();
			$app->getDocument()->addScriptDeclaration('jQuery(function($) { $(\'body\').addClass(\'com_vikrestaurants\'); __vikrestaurants_j40_adapter(); });');
		}

		/**
		 * Always instantiate the currency object.
		 *
		 * @since 1.7.4
		 */
		VikRestaurants::load_currency_js();

		// always include CSS for confirm dialog in back-end
		$vik->addStyleSheet(VREASSETS_URI . 'css/confirmdialog.css');
	}

	/**
	 * Loads FontAwesome.
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 Use VREHtmlAssets::fontawesome() instead.
	 */
	public static function load_font_awesome($fix = false)
	{
		$vik = VREApplication::getInstance();

		JHtml::_('vrehtml.assets.fontawesome');

		if ($fix)
		{
			$vik->fixContentPadding();
		}
	}

	/**
	 * Loads Select2.
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 Use VREHtmlAssets::select2() instead.
	 */
	public static function load_complex_select()
	{
		JHtml::_('vrehtml.assets.select2');
	}
	
	/**
	 * Loads Chart JS.
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 Use VREHtmlAssets::chartjs() instead.
	 */
	public static function load_charts()
	{
		JHtml::_('vrehtml.assets.chartjs');
	}

	/**
	 * Loads the SVG framework recursively.
	 *
	 * @return 	void
	 *
	 * @since 	1.7.4
	 */
	public static function load_svg_framework()
	{
		$vik = VREApplication::getInstance();
		
		$jsUri  = VREASSETS_ADMIN_URI . 'js/ui-svg/';
		$cssUri = VREASSETS_ADMIN_URI . 'css/ui-svg/';

		// load CSS
		$vik->addStyleSheet($cssUri . 'core.css');
		$vik->addStyleSheet($cssUri . 'joomla.css');

		// load JS core
		$vik->addScript($jsUri . "locale.js");
		$vik->addScript($jsUri . "clonable.js");
		$vik->addScript($jsUri . "object.js");
		$vik->addScript($jsUri . "formwrapper.js");
		$vik->addScript($jsUri . "toolbar.js");
		$vik->addScript($jsUri . "inspector.js");
		$vik->addScript($jsUri . "canvas.js");
		$vik->addScript($jsUri . "shortcut.js");
		$vik->addScript($jsUri . "command.js");
		$vik->addScript($jsUri . "grid.js");
		$vik->addScript($jsUri . "table.js");
		$vik->addScript($jsUri . "selection.js");
		$vik->addScript($jsUri . "shape.js");
		$vik->addScript($jsUri . "constraint.js");
		$vik->addScript($jsUri . "state.js");
		$vik->addScript($jsUri . "statusbar.js");
		$vik->addScript($jsUri . "filedialog.js");
		$vik->addScript($jsUri . "tiles.js");
		$vik->addScript($jsUri . "utils.js");

		// load JS selections
		$vik->addScript($jsUri . "selection/rect.js");
		$vik->addScript($jsUri . "selection/shape.js");

		// load JS commands
		$vik->addScript($jsUri . "commands/clone.js");
		$vik->addScript($jsUri . "commands/help.js");
		$vik->addScript($jsUri . "commands/rubber.js");
		$vik->addScript($jsUri . "commands/search.js");
		$vik->addScript($jsUri . "commands/select.js");
		$vik->addScript($jsUri . "commands/shape.js");
		$vik->addScript($jsUri . "commands/shortcut.js");

		// load JS shortcuts
		$vik->addScript($jsUri . "shortcuts/copy.js");
		$vik->addScript($jsUri . "shortcuts/paste.js");
		$vik->addScript($jsUri . "shortcuts/redo.js");
		$vik->addScript($jsUri . "shortcuts/remove.js");
		$vik->addScript($jsUri . "shortcuts/selectall.js");
		$vik->addScript($jsUri . "shortcuts/undo.js");

		// load JS shapes
		$vik->addScript($jsUri . "shapes/rect.js");
		$vik->addScript($jsUri . "shapes/circle.js");
		$vik->addScript($jsUri . "shapes/image.js");

		// load JS state actions
		$vik->addScript($jsUri . "state/action.js");
		$vik->addScript($jsUri . "state/actions/add.js");
		$vik->addScript($jsUri . "state/actions/object.js");
		$vik->addScript($jsUri . "state/actions/remove.js");

		// load JS form
		$vik->addScript($jsUri . "form/form.js");
		$vik->addScript($jsUri . "form/control.js");
		$vik->addScript($jsUri . "form/field.js");

		// load JS form fields
		$vik->addScript($jsUri . "form/fields/checkbox.js");
		$vik->addScript($jsUri . "form/fields/hidden.js");
		$vik->addScript($jsUri . "form/fields/list.js");
		$vik->addScript($jsUri . "form/fields/number.js");
		$vik->addScript($jsUri . "form/fields/radio.js");
		$vik->addScript($jsUri . "form/fields/separator.js");
		$vik->addScript($jsUri . "form/fields/text.js");
		// load JS form fields with dependencies
		$vik->addScript($jsUri . "form/fields/color.js");
		$vik->addScript($jsUri . "form/fields/media.js");
		$vik->addScript($jsUri . "form/fields/medialist.js");

		// load JS locale
		$vik->addScript($jsUri . "locale/joomla.js");

		// load JS add-ons
		$vik->addScript($jsUri . 'addons/commands/exit.js');
		$vik->addScript($jsUri . 'addons/commands/save.js');

		/**
		 * STATUS BAR
		 */

		JText::script('VRE_UISVG_SHAPE_ADDED');
		JText::script('VRE_UISVG_SHAPE_REMOVED');
		JText::script('VRE_UISVG_N_SHAPES_REMOVED');
		JText::script('VRE_UISVG_SHAPE_SELECTED');
		JText::script('VRE_UISVG_N_SHAPES_SELECTED');
		JText::script('VRE_UISVG_SHAPE_COPIED');
		JText::script('VRE_UISVG_N_SHAPES_COPIED');
		JText::script('VRE_UISVG_SHAPE_PASTED');
		JText::script('VRE_UISVG_N_SHAPES_PASTED');
		JText::script('VRE_UISVG_ELEMENT_SAVED');
		JText::script('VRE_UISVG_N_ELEMENTS_SAVED');
		JText::script('VRE_UISVG_ELEMENT_RESTORED');
		JText::script('VRE_UISVG_N_ELEMENTS_RESTORED');

		/**
		 * COMMANDS
		 */

		JText::script('VRE_UISVG_CLONE_CMD_TITLE');
		JText::script('VRE_UISVG_CLONE_CMD_PARAM_KEEP_CLONING');
		JText::script('VRE_UISVG_CLONE_CMD_PARAM_AUTO_SELECT');
		JText::script('JTOOLBAR_HELP');
		JText::script('VRE_UISVG_REMOVE_CMD_TITLE');
		JText::script('VRE_UISVG_SEARCH_CMD_PLACEHOLDER');
		JText::script('VRE_UISVG_SEARCH_CMD_RESULT');
		JText::script('VRE_UISVG_SEARCH_CMD_TITLE');
		JText::script('VRE_UISVG_SHAPE_MOVED');
		JText::script('VRE_UISVG_N_SHAPES_MOVED');
		JText::script('VRE_UISVG_SHAPE_RESIZED');
		JText::script('VRE_UISVG_N_SHAPES_RESIZED');
		JText::script('VRE_UISVG_SHAPE_ROTATED');
		JText::script('VRE_UISVG_N_SHAPES_ROTATED');
		JText::script('VRE_UISVG_SELECT_CMD_TITLE');
		JText::script('VRE_UISVG_SELECT_CMD_PARAM_SIMPLE_SELECTION');
		JText::script('VRE_UISVG_SELECT_CMD_PARAM_REVERSE_SELECTION');
		JText::script('VRE_UISVG_NEW_CMD_TITLE');
		JText::script('VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE');
		JText::script('VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE_RECT');
		JText::script('VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE_CIRCLE');
		JText::script('VRE_UISVG_NEW_CMD_PARAM_SHAPE_TYPE_IMAGE');

		/**
		 * FORM FIELDS
		 */

		JText::script('VRE_UISVG_SEARCH');
		JText::script('VRE_UISVG_NO_MEDIA');
		JText::script('VRE_UISVG_UPLOAD_MEDIA');

		/**
		 * SHORTCUTS
		 */

		JText::script('VRE_UISVG_NO_REDO');
		JText::script('VRE_UISVG_NO_UNDO');

		/**
		 * TABLE INSPECTOR
		 */

		JText::script('VRE_UISVG_TABLE');
		JText::script('JGLOBAL_FIELD_ID_LABEL');
		JText::script('VRMANAGETABLE1');
		JText::script('VRMANAGETABLE2');
		JText::script('VRMANAGETABLE3');
		JText::script('VRMANAGETABLE12');

		/**
		 * CANVAS INSPECTOR
		 */

		JText::script('VRE_UISVG_CANVAS');
		JText::script('VRE_UISVG_LAYOUT');
		JText::script('VRE_UISVG_WIDTH');
		JText::script('VRE_UISVG_HEIGHT');
		JText::script('VRE_UISVG_PROP_SIZE');
		JText::script('VRE_UISVG_BACKGROUND');
		JText::script('VRE_UISVG_NONE');
		JText::script('VRE_UISVG_IMAGE');
		JText::script('VRE_UISVG_COLOR');
		JText::script('VRE_UISVG_MODE');
		JText::script('VRE_UISVG_REPEAT');
		JText::script('VRE_UISVG_HOR_REPEAT');
		JText::script('VRE_UISVG_VER_REPEAT');
		JText::script('VRE_UISVG_COVER');
		JText::script('VRE_UISVG_SHOW_GRID');
		JText::script('VRE_UISVG_SIZE');
		JText::script('VRE_UISVG_GRID_SNAP');
		JText::script('VRE_UISVG_GRID_CONSTRAINTS');
		JText::script('VRE_UISVG_GRID_CONSTRAINTS_ACCURACY');
		JText::script('VRE_UISVG_HIGH');
		JText::script('VRE_UISVG_NORMAL');
		JText::script('VRE_UISVG_LOW');

		JText::script('VRE_UISVG_PROP_SIZE_DESCRIPTION');
		JText::script('VRE_UISVG_IMAGE_DESCRIPTION');
		JText::script('VRE_UISVG_GRID_SNAP_DESCRIPTION');
		JText::script('VRE_UISVG_GRID_CONSTRAINTS_DESCRIPTION');
		JText::script('VRE_UISVG_GRID_CONSTRAINTS_ACCURACY_DESCRIPTION');

		/**
		 * RECT INSPECTOR
		 */

		JText::script('VRE_UISVG_SHAPE');
		JText::script('VRE_UISVG_POSX');
		JText::script('VRE_UISVG_POSY');
		JText::script('VRE_UISVG_ROTATION');
		JText::script('VRE_UISVG_ROUNDNESS');
		JText::script('VRE_UISVG_BACKGROUND_COLOR');
		JText::script('VRE_UISVG_FOREGROUND_COLOR');

		JText::script('VRE_UISVG_ROUNDNESS_DESCRIPTION');

		/**
		 * CIRCLE INSPECTOR
		 */

		JText::script('VRE_UISVG_RADIUS');

		/**
		 * IMAGE INSPECTOR
		 */

		JText::script('VRE_UISVG_BACKGROUND_IMAGE');

		/**
		 * ADD-ONS
		 */
		JText::script('JTOOLBAR_APPLY');
		JText::script('VRE_UISVG_SAVED');
		JText::script('JERROR_SAVE_FAILED');
		JText::script('JERROR_AN_ERROR_HAS_OCCURRED');
		JText::script('VRE_UISVG_EXIT');
	}
	
	/**
	 * Returns an associative array containing the authorisations used
	 * to check which views can be visited by the logged-in user.
	 *
	 * @return 	array
	 */
	public static function getAuthorisations()
	{
		static $rules = null;

		if ($rules)
		{
			// return cached array
			return $rules;
		}

		$rules = array(
			'dashboard' => array(
				'actions'    => array('dashboard' => 0),
				'views'      => array('dashboard' => 'restaurant'),
				'numactives' => 0
			),

			'restaurant' => array(
				'actions'    => array('rooms' => 0, 'tables' => 0, 'maps' => 0, 'products' => 0, 'menus' => 0, 'reservations' => 0),
				'views'      => array('products' => 'menusproducts'),
				'numactives' => 0
			),

			'operations' => array(
				'actions'    => array('shifts' => 0, 'specialdays' => 0, 'operators' => 0),
				'views'      => array(),
				'numactives' => 0
			),

			'booking' => array(
				'actions'    => array('customers' => 0, 'reviews' => 0, 'coupons' => 0, 'invoices' => 0),
				'views'      => array(),
				'numactives' => 0
			),

			'takeaway' => array(
				'actions'    => array('tkmenus' => 0, 'tktoppings' => 0, 'tkdeals' => 0, 'tkareas' => 0, 'tkorders' => 0),
				'views'      => array('tkorders' => 'tkreservations'),
				'numactives' => 0
			),

			'global' => array(
				'actions'    => array('custfields' => 0, 'payments' => 0, 'media' => 0, 'rescodes' => 0),
				'views'      => array('custfields' => 'customf'),
				'numactives' => 0
			),

			'configuration' => array(
				'actions'    => array('config' => 0),
				'views'      => array('config' => 'editconfig'),
				'numactives' => 0
			)
		);
		
		$user = JFactory::getUser();
		
		foreach ($rules as $group => $rule)
		{
			foreach ($rule['actions'] as $action => $val)
			{
				$rules[$group]['actions'][$action] = $user->authorise("core.access.$action", "com_vikrestaurants");
				
				if ($rules[$group]['actions'][$action])
				{
					$rules[$group]['numactives']++;
				}
			}
		}

		if (!VikRestaurants::isRestaurantEnabled())
		{
			// turn off restaurant section
			$rules['restaurant']['numactives'] = 0;
		}

		if (!VikRestaurants::isTakeAwayEnabled())
		{
			// turn off take-away section
			$rules['takeaway']['numactives'] = 0;

			// turn off reviews, if enabled
			if ($rules['booking']['actions']['reviews'])
			{
				$rules['booking']['actions']['reviews'] = 0;
				$rules['booking']['numactives']--;
			}
		}

		return $rules;
	}
	
	/**
	 * Register a new Joomla user with the details
	 * specified in the given $args array.
	 *
	 * All the restrictions specified in com_users
	 * component are always bypassed.
	 *
	 * @param 	array 	$args 	The user details.
	 *
	 * @return 	mixed 	The ID of the user on success, false otherwise.
	 *
	 * @throws  RuntimeException
	 */
	public static function createNewJoomlaUser($args)
	{
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_users');

		$vik = VREApplication::getInstance();

		$user = new JUser;
		$data = array();

		if (empty($args['usertype']))
		{
			$groups = array($params->get('new_usertype', 2));
		}
		else
		{
			if (is_array($args['usertype']))
			{
				$groups = $args['usertype'];
			}
			else
			{
				$groups = array((string) $args['usertype']);
			}
		}

		if (empty($args['user_username']))
		{
			// empty username, use the specified name
			$args['user_username'] = $args['user_name'];
		}

		// get the default new user group, Registered if not specified
		$data['groups'] 	= $groups;
		$data['name'] 		= $args['user_name'];
		$data['username'] 	= $args['user_username'];
		$data['email'] 		= $vik->emailToPunycode($args['user_mail']);
		$data['password'] 	= $args['user_pwd1'];
		$data['password2']	= $args['user_pwd2'];
		$data['sendEmail'] 	= 0;

		/**
		 * Instead of returning 'false', this method 
		 * throws exceptions in case of errors.
		 *
		 * @since 1.8
		 */
		
		// bind user data
		if (!$user->bind($data))
		{
			// get error from user table
			$error = $user->getError(null, true);

			// throw exception
			throw new RuntimeException($error ? $error : JText::_('VRE_USER_SAVE_BIND_ERR'));
		}

		if (!$user->save())
		{
			// get error from user table
			$error = $user->getError(null, true);

			// throw exception
			throw new RuntimeException($error ? $error : JText::_('VRE_USER_SAVE_CHECK_ERR'));
		}

		return $user->id;
	}

	/**
	 * Returns a list of images stored within the media folders.
	 *
	 * @param 	boolean  $order_by_creation
	 * @param 	boolean  $thumbs
	 *
	 * @return 	array
	 *
	 * @since 	1.8
	 *
	 * @uses 	getMediaFromPath()
	 */
	public static function getAllMedia($order_by_creation = false, $thumbs = false)
	{
		if ($thumbs)
		{
			$path = VREMEDIA_SMALL;
		}
		else
		{
			$path = VREMEDIA;
		}

		return self::getMediaFromPath($path, $order_by_creation);
	}

	/**
	 * Returns a list of images stored in the following folder.
	 *
	 * @param 	string   $path
	 * @param 	boolean  $order_by_creation
	 *
	 * @return 	array
	 *
	 * @since 1.8
	 */
	public static function getMediaFromPath($path, $order_by_creation = false)
	{
		/**
		 * Considering that certain server configurations may not support GLOB_BRACE mask,
		 * we need to filter the list manually.
		 *
		 * @since 1.7.4
		 */
		$arr = glob($path . DIRECTORY_SEPARATOR . '*');

		$arr = array_filter($arr, function($path)
		{
			return preg_match("/.*\.(png|jpe?g|gif|bmp)$/i", $path);
		});

		if ($order_by_creation)
		{
			/**
			 * Replaced create_function with anonymous function
			 * as it has been declared deprecated since PHP 7.2.
			 *
			 * In case of PHP 5.2 or lower, this code may raise a fatal
			 * error as this version doesn't support yet anonymous functions.
			 *
			 * @since 1.7.4
			 */
			usort($arr, function($a, $b)
			{
				// sort by descending creation date
				$ord = filemtime($b) - filemtime($a);

				/**
				 * In case of same creation date, sort files alphabetically (ASC).
				 *
				 * @since 1.8
				 */
				if ($ord == 0)
				{
					$ord = strcmp($a, $b);
				}

				return $ord;
			});
		}

		return $arr;
	}

	/**
	 * Returns an associative array containing the details of
	 * the specified file.
	 *
	 * @param 	string 	$file  The file path.
	 * @param 	array 	$attr  An array of options.
	 *
	 * @return 	mixed 	An array in case the file exists, null otherwise.
	 *
	 * @since 	1.2
	 */
	public static function getFileProperties($file, $attr = array())
	{
		if (!is_file($file))
		{
			return null;
		}

		// fill the options with the attributes needed in
		// case they were not specified
		$attr = self::getDefaultFileAttributes($attr);
		
		$prop = array();
		$prop['file'] 	     = $file;
		$prop['path'] 	     = dirname($file);
		$prop['name'] 	     = basename($file);
		$prop['file_ext']    = substr($file, strrpos($file, '.'));
		$prop['size'] 	     = JHtml::_('number.bytes', filesize($file));
		$prop['timestamp']   = filemtime($file);
		$prop['creation']    = JHtml::_('date', $prop['timestamp'], $attr['dateformat'], date_default_timezone_get());
		$prop['name_no_ext'] = substr($prop['name'], 0, strrpos($prop['name'], '.'));

		// fetch URI
		if ($prop['path'] == VREMEDIA)
		{
			// use media URI
			$prop['uri'] = VREMEDIA_URI;
		}
		else if ($prop['path'] == VREMEDIA_SMALL)
		{
			// use media@small URI
			$prop['uri'] = VREMEDIA_SMALL_URI;
		}
		else if (strpos($prop['path'], VRECUSTOMERS_AVATAR) !== false)
		{
			/**
			 * Use customers avatar folder.
			 *
			 * @since 1.8.2
			 */
			$prop['uri'] = VRECUSTOMERS_AVATAR_URI;
		}
		else if (strpos($prop['path'], VREBASE) !== false)
		{
			/**
			 * Fetch URI based on given (internal) path.
			 *
			 * @since 1.8
			 */
			$folder = str_replace(VREBASE, '', $prop['path']);
			$folder = trim($folder, DIRECTORY_SEPARATOR);
			$folder = str_replace(DIRECTORY_SEPARATOR, '/', $folder);

			$prop['uri'] = VREBASE_URI . $folder . '/';
		}
		else
		{
			throw new Exception('Unable to read files out of VikRestaurants', 500);
		}

		// complete file URI
		$prop['uri'] .= $prop['name'];

		if (preg_match('/\.(jpe?g|png|bmp|gif)$/i', $prop['file_ext']))
		{
			$img_size = getimagesize($file);

			$prop['width']  = $img_size[0];
			$prop['height'] = $img_size[1];
		}

		return $prop;
	}

	/**
	 * Returns an array of options to be used while fetching the
	 * details of a file. The default values will be used only
	 * if the specified attributes array doesn't contain them.
	 *
	 * @param 	array 	$attr  An array of attributes.
	 *
	 * @return 	array 	The resulting array.
	 */
	public static function getDefaultFileAttributes($attr = array())
	{
		if (empty($attr['dateformat']))
		{
			$config = VREFactory::getConfig();

			$attr['dateformat'] = 'd M Y ' . preg_replace("/:i/", ':i:s', $config->get('timeformat'));
		}

		return $attr;
	}

	/**
	 * Returns the default group that should be used.
	 * In case the restaurant is enabled, it will return 0.
	 * In case the restaurant is disabled and the take-away is
	 * enabled, it will return 1.
	 * In case both the features are disabled, null will be returned.
	 *
	 * @param 	array 	$values 	Specify the custom values that will be used.
	 * 								Index [0], value for restaurant.
	 * 								Index [1], value for take-away.
	 *
	 * @return 	mixed 	The default group.
	 *
	 * @since 	1.7.4
	 */
	public static function getDefaultGroup(array $values = null)
	{
		if ($values === null || count($values) != 2)
		{
			$values = array(0, 1);
		}

		if (VikRestaurants::isRestaurantEnabled(true))
		{
			return $values[0];
		}

		if (VikRestaurants::isTakeAwayEnabled(true))
		{
			return $values[1];
		}

		return null;
	}

	/**
	 * Creates a dropdown containing the supported groups.
	 *
	 * @param 	string 	 $name
	 * @param 	mixed 	 $selected
	 * @param 	string 	 $id
	 * @param 	mixed 	 $values
	 * @param 	string 	 $class
	 * @param 	boolean  $allowClear
	 * @param 	boolean  $placeholder
	 *
	 * @return 	string
	 *
	 * @since 	1.5
	 */
	public static function buildGroupDropdown($name, $selected, $id, $values = null, $class = '', $allowClear = false, $placeholder = null)
	{
		// get supported groups
		$groups = JHtml::_('vrehtml.admin.groups', $values, $allowClear, $placeholder);

		if (!$groups)
		{
			// use input hidden and display placeholder
			return '<input type="hidden" name="' . htmlspecialchars($name) . '" value="" />' . $placeholder;
		}

		// fetch HTML of options 
		$select = JHtml::_('select.options', $groups, 'value', 'text', $selected);

		// create dropdown
		$select = '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '" class="' . htmlspecialchars($class) . '">' . $select . '</select>';
			
		// make placeholder JS safe
		$placeholder = addslashes($placeholder);
		$allowClear  = $allowClear ? 'true' : 'false';

		// auto-render dropdown using select2 plugin
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function() {
	jQuery('#{$id}').select2({
		minimumResultsForSearch: -1,
		placeholder: '{$placeholder}',
		allowClear: {$allowClear},
		width: 300,
	});
});
JS
		);

		return $select;
	}

	/**
	 * Updates the extra fields of VikRestaurants to let them
	 * be sent to our servers during Joomla! updates.
	 *
	 * @return 	void
	 *
	 * @since 	1.7
	 */
	public static function registerUpdaterFields()
	{
		// make sure the Joomla version is 3.2.0 or higher
		// otherwise the extra_fields wouldn't be available
		$jv = new JVersion();
		if (version_compare($jv->getShortVersion(), '3.2.0', '<'))
		{
			// stop to avoid fatal errors.
			return;
		}

		$config = VREFactory::getConfig();
		$extra_fields = $config->getInt('update_extra_fields', 0);	

		if ($extra_fields > time())
		{
			// not needed to rewrite extra fields
			return;
		}

		// get current domain
		$server = JFactory::getApplication()->input->server;
		$domain = base64_encode($server->getString('HTTP_HOST'));
		$ip 	= $server->getString('REMOTE_ADDR');

		// import url update handler
		VRELoader::import('library.update.urihandler');

		$update = new UriUpdateHandler('com_vikrestaurants');

		$update->addExtraField('domain', $domain)
			->addExtraField('ip', $ip)
			->register();

		// validate schema version
		$update->checkSchema($config->get('version'));

		// rewrite extra fields next week
		$config->set('update_extra_fields', time() + 7 * 86400);
	}
	
	/**
	 * Get the actions.
	 *
	 * @param 	integer  $id
	 *
	 * @return 	object
	 */
	public static function getActions($id = 0)
	{
		jimport('joomla.access.access');

		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($id))
		{
			$assetName = 'com_vikrestaurants';
		}
		else
		{
			$assetName = 'com_vikrestaurants.message.' . (int) $id;
		}

		$actions = JAccess::getActions('com_vikrestaurants', 'component');

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		};

		return $result;
	}
}

if (!class_exists('OrderingManager'))
{
	/**
	 * Helper class used to handle lists ordering.
	 *
	 * @since 1.0
	 */
	class OrderingManager
	{
		/**
		 * The component name.
		 *
		 * @var string
		 */
		protected static $option = 'com_vikrestaurants';

		/**
		 * The value in query string that will be used to 
		 * recover the selected ordering column.
		 *
		 * @var string
		 */
		protected static $columnKey = 'vrordcolumn';

		/**
		 * The value in query string that will be used to 
		 * recover the selected ordering direction.
		 *
		 * @var string
		 */
		protected static $typeKey = 'vrordtype';
		
		/**
		 * Class constructor.
		 */
		protected function __construct()
		{
			// not accessible
		}

		/**
		 * Prepares the class with custom configuration.
		 *
		 * @param 	string 	$option
		 * @param 	string 	$column
		 * @param 	string 	$type
		 *
		 * @return 	void
		 */
		public static function getInstance($option = '', $column = '', $type = '')
		{
			if (!empty($option))
			{
				self::$option = $option;
			}

			if (!empty($column))
			{
				self::$columnKey = $column;
			}

			if (!empty($type))
			{
				self::$typeKey = $type;
			}
		}
		
		/**
		 * Returns the link that will be used to sort the column.
		 *
		 * @param 	string 	$task 			The task to reach after clicking the link.
		 * @param 	string 	$text 			The link text.
		 * @param 	string 	$col 			The column to sort.
		 * @param 	string 	$type 			The new direction value (1 ASC, 2 DESC).
		 * @param 	string 	$def_type 		The default direction if $type is empty.
		 * @param 	array 	$params 		An associative array with addition params to include in the URL-
		 * @param 	string 	$active_class 	The class used in case of active link.
		 *
		 * @return 	string 	The HTML of the link.
		 */
		public static function getLinkColumnOrder($task, $text, $col, $type = '', $def_type = '', $params = array(), $active_class = '')
		{
			if (empty($type))
			{
				$type 			= $def_type;
				$active_class 	= '';
			}

			if (!is_array($params))
			{
				if (empty($params))
				{
					$params = array();
				}
				else
				{
					$params = array($params);
				}
			}

			// inject URL vars in $params array
			$params['option'] 			= self::$option;
			$params['view']				= $task;
			$params[self::$columnKey] 	= $col;
			$params[self::$typeKey] 	= $type;

			$href = 'index.php?' . http_build_query($params);
			
			return '<a class="' . $active_class . '" href="' . $href . '">' . $text . '</a>';
		}
		
		/**
		 * Returns the ordering details for the specified values.
		 *
		 * @param 	string 	$task 		The task where we are.
		 * @param 	string 	$def_col 	The default column to sort.
		 * @param 	string 	$def_type 	The default ordering direction.
		 *
		 * @return 	array 	An associative array containing the ordering column and direction.
		 */
		public static function getColumnToOrder($task, $def_col = 'id', $def_type = 1)
		{
			$app = JFactory::getApplication();

			$col 	= $app->getUserStateFromRequest(self::$columnKey . "[$task]", self::$columnKey, $def_col, 'string');
			$type 	= $app->getUserStateFromRequest(self::$typeKey . "[$task]", self::$typeKey, $def_type, 'uint');
			
			return array('column' => $col, 'type' => $type);
		}
		
		/**
		 * Returns the ordering direction, based on the current one.
		 *
		 * @param 	string 	$task 		The task where we are.
		 * @param 	string 	$col 		The column we need to alter.
		 * @param 	string 	$curr_type 	The current direction.
		 *
		 * @return 	string  The new direction value.
		 */
		public static function getSwitchColumnType($task, $col, $curr_type)
		{
			$stored = JFactory::getApplication()->getUserStateFromRequest(self::$columnKey . "[$task]", self::$columnKey, '', 'string');
			
			$types = array(1, 2);

			if ($stored == $col)
			{
				$index = array_search($curr_type, $types);

				if ($index >= 0)
				{
					return $types[($index + 1) % 2];
				}
			} 
			
			return end($types);
		}
	}
}

/**
 * Media Manager helper.
 *
 * @since 		1.7
 * @deprecated 	1.9 Use VREHtmlMediaManager instead.
 */
class MediaManagerHTML
{
	/**
	 * Attaches to the documents the media manager scripts.
	 *
	 * @param 	string 	$no_image_text  The "no selection" placeholder.
	 *
	 * @return 	void
	 *
	 * @deprecated 1.9 Use JHtml::_('vrehtml.mediamanager.script') instead.
	 */
	public function useScript($no_image_text = '')
	{
		$this->noSelectionText = $no_image_text;

		JHtml::_('vrehtml.mediamanager.script');
	}

	/**
	 * Displays the modal for the media selection.
	 *
	 * @param 	string 	$title  The modal title.
	 *
	 * @return 	string  The HTML of the modal.
	 *
	 * @deprecated 1.9 Use JHtml::_('vrehtml.mediamanager.modal') instead.
	 */
	public function buildModal($title)
	{
		$this->modalTitle = $title;

		return JHtml::_('vrehtml.mediamanager.modal', $title);
	}

	/**
	 * Displays the media field.
	 *
	 * @param 	string  $name   The field name.
	 * @param 	string  $id     The field ID attribute.
	 * @param   mixed   $value  The field value.
	 *
	 * @return 	string  The HTML of the field.
	 *
	 * @deprecated 1.9 Use JHtml::_('vrehtml.mediamanager.field') instead.
	 */
	public function buildMedia($name, $id = null, $value = null)
	{
		$attrs = array();

		if (!empty($this->noSelectionText))
		{
			$attrs['placeholder'] = $this->noSelectionText;
		}

		if (!empty($this->modalTitle))
		{
			$attrs['modaltitle'] = $this->modalTitle;
		}

		return JHtml::_('vrehtml.mediamanager.field', $name, $value, $id, $attrs);
	}
}
