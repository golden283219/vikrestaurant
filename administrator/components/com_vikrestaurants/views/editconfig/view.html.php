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
 * VikRestaurants configuration view.
 *
 * @since 1.0
 */
class VikRestaurantsVieweditconfig extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$dbo = JFactory::getDbo();	

		// set the toolbar
		$this->addToolBar();
		
		$params = array();
		
		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('param', 'setting')))
			->from($dbo->qn('#__vikrestaurants_config'));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $row)
			{
				$params[$row->param] = $row->setting;
			}
		}

		/**
		 * Fetch default country by using the custom fields helper.
		 *
		 * @since 1.8
		 */
		$def_country = VRCustomFields::getDefaultCountryCode();

		/**
		 * Retrieve custom fields by using the related helper.
		 *
		 * @since 1.8
		 */
		$custom_fields = VRCustomFields::getList(
			null,
			VRCustomFields::FILTER_EXCLUDE_SEPARATOR | VRCustomFields::FILTER_EXCLUDE_REQUIRED_CHECKBOX
		);

		/**
		 * Added support for configuration translations.
		 *
		 * @since 1.8
		 */
		$def_lang = VikRestaurants::getDefaultLanguage();

		$translations = array(
			'symbpos'          => array($def_lang),
			'currdecimalsep'   => array($def_lang),
			'currthousandssep' => array($def_lang),
			'largepartyurl'    => array($def_lang),
			'policylink'       => array($def_lang),
			'tknote'           => array($def_lang),
		);

		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('param', 'tag')))
			->from($dbo->qn('#__vikrestaurants_lang_config'));
		
		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $t)
			{
				if (!in_array($t->tag, $translations[$t->param]))
				{
					$translations[$t->param][] = $t->tag;
				}
			}
		}

		// params
		
		$this->params         = &$params;
		$this->countries      = &$countries;
		$this->defaultCountry = &$def_country;
		$this->customFields   = &$custom_fields;
		$this->translations   = &$translations;

		// display the template
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
		JToolbarHelper::title(JText::_('VRMAINTITLECONFIG'), 'vikrestaurants');

		if (JFactory::getUser()->authorise('core.access.config', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('configuration.save', JText::_('VRSAVE'));
			JToolbarHelper::divider();
		}
	
		JToolbarHelper::cancel('configuration.dashboard', JText::_('VRCANCEL'));
	}
}
