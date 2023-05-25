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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants media view.
 *
 * @since 1.6
 */
class VikRestaurantsViewmedia extends JViewVRE
{
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		$path = $input->getBase64('path', null);

		if ($path)
		{
			$path = rtrim(base64_decode($path), DIRECTORY_SEPARATOR);
		}
		else
		{
			$path = VREMEDIA;
		}

		// retrieve all images and apply filters
		$all_img = RestaurantsHelper::getMediaFromPath($path, $sort_by_creation = true);

		if ($input->get('layout') != 'modal')
		{
			// set the toolbar
			$this->addToolBar();
			
			$filters = array();
			$filters['keysearch'] = $app->getUserStateFromRequest('vre.media.keysearch', 'keysearch', '', 'string');

			// pagination
			$lim 	= $app->getUserStateFromRequest('com_vikrestaurants.limit', 'limit', $app->get('list_limit'), 'int');
			$lim0 	= $app->getUserStateFromRequest('vre.media.limitstart', 'limitstart', 0, 'uint');
			$navbut	= "";

			if (!empty($filters['keysearch']))
			{
				$app = array();

				foreach ($all_img as $img)
				{
					$file_name = basename($img);

					if (strpos($file_name, $filters['keysearch']) !== false)
					{
						array_push($app, $img);
					}
				}
				$all_img = $app;
				unset($app);
			}
			
			$tot_count = count($all_img);

			if ($tot_count > $lim)
			{
				$all_img = array_slice($all_img, $lim0, $lim);

				jimport('joomla.html.pagination');
				$pageNav = new JPagination($tot_count, $lim0, $lim);
				$navbut = "<table align=\"center\"><tr><td>" . $pageNav->getListFooter() . "</td></tr></table>";
			}

			$this->navbut = &$navbut;
			$this->filters = &$filters;
		}
		else
		{
			/**
			 * Added support for 'modal' layout.
			 *
			 * @since 1.8
			 */
			$this->setLayout('modal');

			// retrieve selected media
			$this->selected = $input->get('media', array(), 'string');
			// check if multi-selection is allowed
			$this->multiple = $input->get('multiple', false, 'bool');

			// unset images that don't exist
			$this->selected = array_filter($this->selected, function($elem) use ($path)
			{
				return $elem && is_file($path . DIRECTORY_SEPARATOR . $elem);
			});

			/**
			 * Check if we are uploading the media files for the first time.
			 *
			 * @since 1.8.2
			 */
			$this->firstConfig = VREFactory::getConfig()->getBool('firstmediaconfig');
		}

		$attr = RestaurantsHelper::getDefaultFileAttributes();

		foreach ($all_img as $i => $f)
		{
			$all_img[$i] = RestaurantsHelper::getFileProperties($f, $attr);
		}
		
		$this->rows = &$all_img;
		$this->path = ($path === VREMEDIA ? '' : $path);
		
		// display the template (default.php)
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEVIEWMEDIA'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.create', 'com_vikrestaurants'))
		{
			JToolbarHelper::addNew('media.add', JText::_('VRNEW'));
			JToolbarHelper::divider();
		}

		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::editList('media.edit', JText::_('VREDIT'));
			JToolbarHelper::spacer();
		}

		if (JFactory::getUser()->authorise('core.delete', 'com_vikrestaurants'))
		{
			JToolbarHelper::deleteList(VikRestaurants::getConfirmSystemMessage(), 'media.delete', JText::_('VRDELETE'));	
		}
	}
}
