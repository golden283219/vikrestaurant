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
 * Helper class used to set to the Joomla 4 platform.
 *
 * @since 1.8.3
 */
class VREJoomla40SetupHelper
{
	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		// add support for "Search Tools" button text
		JText::script('JFILTER_OPTIONS');
	}

	/**
	 * Observes the event that triggers after dispatching the component.
	 * Here the component should detect all the scripts included inline
	 * within the body. All the found scripts should then be detached 
	 * and re-added as new script declarations.
	 * This is mandatory as Joomla 4 seems to load the scripts in the body.
	 * This way, the scripts will now be able to properly use any resources.
	 *
	 * @return 	void
	 */
	public function addScriptsFooter()
	{
		$app = JFactory::getApplication();

		// check if we are under component template
		if ($app->input->get('tmpl') == 'component')
		{
			// do not treat the scripts as declarations
			// because the current template should keep
			// loading them within the <head>
			return;
		}

		// register event to be executed after component dispatch
		$app->registerEvent('onAfterDispatch', function() use ($app)
		{
			// access application document
			$document = $app->getDocument();

			// get component buffer
			$buffer = $document->getBuffer('component');

			// find all the script tags inside the document
			$buffer = preg_replace_callback("/<script([^>]*)>(.*?)<\/script>/si", function($match) use ($document)
			{
				// check whether the script should be loaded by using the async/defer technique
				if (preg_match("/\s+(defer|async)(?:\s+|$)/i", $match[1]))
				{
					// leave the script as it is
					return $match[0];
				}

				// check whether we have a "src" attribute
				if (preg_match("/\bsrc=['\"](.*?)['\"]/", $match[1], $attrs))
				{
					// register script URI
					$document->addScript(end($attrs));
				}

				// extract script body from matches
				$body = trim(end($match));

				if ($body)
				{
					// register the body of the script as a declaration
					$document->addScriptDeclaration($body);
				}

				// always remove the script from the document
				return '';
			}, $buffer);

			// replace component buffer
			$document->setBuffer($buffer, 'component');
		});
	}

	/**
	 * Within Joomla 4.0 the badge secondary class has been renamed
	 * from "badge-{style}" to "bg-style".
	 *
	 * @return 	void
	 */
	public function replaceBadgeClass()
	{
		$app = JFactory::getApplication();

		// register event to be executed after component dispatch
		$app->registerEvent('onAfterDispatch', function() use ($app)
		{
			// access application document
			$document = $app->getDocument();

			// get component buffer
			$buffer = $document->getBuffer('component');

			// find all the "badge-(style)" occurrences
			$buffer = preg_replace_callback("/\bbadge-(warning|info|important|success)\b/si", function($match) use ($document)
			{
				// adjust unsupported styles
				if ($match[1] == 'important')
				{
					$match[1] = 'danger';
				}

				// fix class name
				return 'bg-' . $match[1];
			}, $buffer);

			// replace component buffer
			$document->setBuffer($buffer, 'component');
		});
	}

	/**
	 * Since Joomla 4.0 JHtmlBehavior::modal() doesn't exist anymore.
	 * Register a new callback in order to support modals without
	 * having to refactor the existing code.
	 *
	 * @return 	void
	 */
	public function registerModal()
	{
		if (!JHtml::isRegistered('behavior.modal'))
		{
			// register behavior.modal callback
			JHtml::register('behavior.modal', function($selector = 'a.modal', array $params = array())
			{
				// invoke our internal modal callback to use Fancybox
				JHtml::_('vrehtml.scripts.modal', $selector, $params);
			});
		}
	}

	/**
	 * Registers an event to change the type of menu from "leftboard"
	 * to "horizontal", in order to avoid conflicts with the default
	 * Joomla sidebar.
	 *
	 * @return 	void
	 */
	public function useHorizontalMenu()
	{
		$app = JFactory::getApplication();

		// according to the new Event framework provided by Joomla, the return
		// value must be passed as argument to the attached event object
		$app->registerEvent('onBeforeDefineVikRestaurantsMenuType', function($event = null)
		{
			if (is_object($event))
			{
				// register return value as argument
				$event['result'] = array('horizontal');
			}
		});
	}

	/**
	 * Turns off the Strict Mode for MySQL drivers by unsetting the following roles:
	 * - ONLY_FULL_GROUP_BY
	 * - STRICT_TRANS_TABLES
	 *
	 * @return 	void
	 */
	public function disableStrictMode()
	{
		try
		{
			$dbo = JFactory::getDbo();

			// load all sql_mode instructions
			$dbo->setQuery('SELECT @@sql_mode');
			$dbo->execute();

			if (!$dbo->getNumRows())
			{
				// setting not found
				return;
			}

			// define list of roles that should be unset
			$excluded = array(
				'ONLY_FULL_GROUP_BY',
				'STRICT_TRANS_TABLES',
			);

			// fetch list
			$roles = preg_split("/\s*,\s*/", $dbo->loadResult());

			// remove the roles that match the excluded list
			$roles = array_filter($roles, function($role) use ($excluded)
			{
				return $role && !in_array(strtoupper($role), $excluded);
			});

			// update SQL modes
			$dbo->setQuery("SET sql_mode=" . $dbo->q(implode(',', $roles)));
			$dbo->execute();
		}
		catch (Exception $e)
		{
			$user = JFactory::getUser();

			// check if we have a super user
			if ($user->authorise('core.admin', 'com_vikrestaurants'))
			{
				// inform the user about the failure
				JFactory::getApplication()->enqueueMessage('<p>Could not turn off SQL strict mode.</p><pre>' . print_r($e, true) . '</pre>', 'warning');
			}
		}
	}

	/**
	 * Adjusts the toolbar to support the button groups provided by
	 * Joomla 4 platform. The buttons can be grouped under 2 
	 * different types of dropdown: save and actions.
	 *
	 * Every button with name (id) that starts with "save" will be
	 * grouped under the omonym dropdown. Every button that requires
	 * a list check will be grouped under the actions dropdown.
	 *
	 * In addition, in case the toolbar contains a save button,
	 * the Joomla sidebar menu will be hidden.
	 *
	 * @return 	void
	 */
	public function prepareToolbar()
	{
		$app = JFactory::getApplication();

		// register event to be executed after component dispatch
		$app->registerEvent('onAfterDispatch', function() use ($app)
		{
			// get toolbar instance
			$toolbar = JToolbar::getInstance();

			// get registered buttons
			$items = $toolbar->getItems();

			$groups = array(
				'actions' => [],
				'save'    => [],
			);

			$hideMenu = false;

			foreach ($items as $i => $btn)
			{
				if (is_array($btn))
				{
					// sum 1 to index in order to exclude the confirmation message
					$ik = strtolower($btn[0]) == 'confirm' ? 1 : 0;

					// extract button identifier
					$name = $btn[1 + $ik];

					// check whether the button requires a selection
					$selection = !empty($btn[4 + $ik]);
				}
				else
				{
					// extract button identifier
					$name = $btn->getName();

					// check whether the button requires a selection
					$selection = $btn->getListCheck();
				}

				// check whether the button name starts with "save"
				if (preg_match('/^save/', $name))
				{
					// register button under save group
					$groups['save'][$i] = $btn;

					// hide menu when we are under management
					$hideMenu = true;
				}
				else if (preg_match("/^apply$/", $name))
				{
					// hide menu when we are under management
					$hideMenu = true;
				}
				// check whether the buttons requires a record selection
				else if ($selection)
				{
					// register button under actions group
					$groups['actions'][$i] = $btn;
				}
			}

			// make sure the save group contains more than a button
			if (count($groups['save']) > 1)
			{
				// create sub-group to host multi-buttons
				$saveGroup = $toolbar->dropdownButton('save-group');

				// add buttons to group and then to bar
				$this->setupToolbarGroup($items, $saveGroup, $groups['save']);
			}
			else if (count($groups['actions']) > 1)
			{
				$dropdown = $toolbar->dropdownButton('status-group')
					->text('JTOOLBAR_CHANGE_STATUS')
					->toggleSplit(false)
					->icon('fas fa-ellipsis-h')
					->buttonClass('btn btn-action')
					->listCheck(true);

				// add buttons to group and then to bar
				$this->setupToolbarGroup($items, $dropdown, $groups['actions']);
			}

			// update toolbar items
			$toolbar->setItems($items);

			if ($hideMenu)
			{
				// set "hidemainmenu" in request to hide the
				// Joomla sidebar under management pages
				$app->input->set('hidemainmenu', 1);
			}
		});
	}

	/**
	 * Helper method used to create a toolbar group that 
	 * contains multiple buttons. The created group is
	 * automatically added to the toolbar.
	 *
	 * @param 	array 	&$items   The toolbar items.
	 * @param 	mixed   $group    The instance handling the group.
	 * @param 	array   $buttons  The buttons to attach.
	 *
	 * @return 	void
	 */
	protected function setupToolbarGroup(&$items, $group, $buttons)
	{
		// configure group to append all the save buttons
		$group->configure(
			function ($childBar) use ($buttons)
			{
				// iterate buttons
				foreach ($buttons as $btn)
				{
					if (is_array($btn))
					{
						// prepare button type
						$btnType = $btn[0] . 'Button';

						// extract confirmation message
						$confirm = strtolower($btn[0]) == 'confirm' ? array_splice($btn, 1, 1) : '';

						// create button from array
						$btn = $childBar->{$btnType}($btn[1])
							->text($btn[2])
							->task($btn[3])
							->listCheck(true);

						if ($confirm)
						{
							// register confirmation message when specified
							$btn->message($confirm[0]);
						}
					}
					else
					{
						// append button to sub-group as it is
						$childBar->appendButton($btn);
					}
				}
			}
		);

		$index = (int) key($buttons);

		// detach buttons from toolbar
		foreach ($buttons as $k => $btn)
		{
			unset($items[$k]);
		}

		// reset buttons keys
		$items = array_values($items);

		// append save group next to the apply button
		array_splice($items, $index, 0, array($group));
	}
}
