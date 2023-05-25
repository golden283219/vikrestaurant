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
 * VikRestaurants update program view.
 *
 * @since 	1.7
 */
class VikRestaurantsViewupdateprogram extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		// set the toolbar
		$this->addToolBar();

		/**
		 * Get internal event dispatcher to automatically
		 * include the parameters array, which will be used
		 * to fetch the version of the program.
		 *
		 * @see   VREFactory
		 *
		 * @since 1.8
		 */
		$dispatcher = VREFactory::getEventDispatcher();
		
		// retrieve stored data
		$result = $dispatcher->triggerOnce('onGetVersionContents');

		if (!$result)
		{
			// no stored data, make request to check the version
			$result = $dispatcher->triggerOnce('onCheckVersion');
		}

		if (!$result || !isset($result->status) || !isset($result->response->status))
		{
			throw new Exception('An error occurred while fetching the version contents', 500);
		}

		$this->version = &$result->response;

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	private function addToolBar()
	{
		// add menu title and some buttons to the page
		JToolbarHelper::title(JText::_('VRMAINTITLEUPDATEPROGRAM'), 'vikrestaurants');
		
		JToolbarHelper::cancel('updateprogram.cancel', JText::_('VRCANCEL'));
	}

	/**
	 * Scan changelog structure.
	 *
	 * @param 	array 	$arr 	The list containing changelog elements.
	 * @param 	mixed 	$html 	The html built. 
	 * 							Specify false to echo the structure immediately.
	 *
	 * @return 	string|void 	The HTML structure or nothing.
	 */
	public function digChangelog(array $arr, $html = '')
	{
		foreach ($arr as $elem)
		{
			if (isset($elem->tag))
			{
				// build attributes
				
				$attributes = "";

				if (isset($elem->attributes))
				{
					foreach ($elem->attributes as $k => $v)
					{
						$attributes .= ' ' . $k . '="' . $this->escape($v) . '"';
					}
				}

				// build tag opening

				$str = "<{$elem->tag}$attributes>";

				if ($html)
				{
					$html .= $str;
				}
				else
				{
					echo $str;
				}

				// display contents

				if (isset($elem->content))
				{
					if ($html)
					{
						$html .= $elem->content;
					}
					else
					{
						echo $elem->content;
					}
				}

				// recursive iteration for elem children

				if (isset($elem->children))
				{
					$this->digChangelog($elem->children, $html);
				}

				// build tag closure

				$str = "</{$elem->tag}>";

				if ($html)
				{
					$html .= $str;
				}
				else
				{
					echo $str;
				}
			}
		}

		return $html;
	}
}
