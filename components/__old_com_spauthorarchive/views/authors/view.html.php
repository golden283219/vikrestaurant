<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

class SpauthorarchiveViewAuthors extends JViewLegacy {

	protected $items;
	protected $params;
	protected $layout_type;

	function display($tpl = null) {
		// Assign data to the view
		$app 	= JFactory::getApplication();
		$model 	= $this->getModel();
		$menus 	= JFactory::getApplication()->getMenu();
		$this->items 		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->params 		= $app->getParams();
		$this->columns		= $this->params->get('columns', 4);
		$this->show_desc	= $this->params->get('show_desc', 1);
		$menu = $menus->getActive();
		if($menu) {
			$this->params->merge($menu->params);
		}
		$this->layout_type = str_replace('_', '-', $this->params->get('layout_type', 'default'));
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
			return false;
		}

		foreach ($this->items as &$this->item) {
			$this->item->profile_data = $model->getUserProfileData($this->item->id);
			$this->item->image = (isset($this->item->profile_data['avatar']['avatar']) && $avatar = $this->item->profile_data['avatar']['avatar']) ? JURI::root(true) . $avatar : 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($this->item->email))) . '?s=180';
			$this->item->socials = (isset($this->item->profile_data['socials']) && $socials = $this->item->profile_data['socials']) ? $socials : '' ;
			$this->item->url = JRoute::_('index.php?option=com_spauthorarchive&view=articles&uid='.$this->item->id.':'.$this->item->username . SpauthorarchiveHelper::getItemid('authors'));
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	protected function _prepareDocument() {
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		$menu = $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_SPAUTHORARCHIVGE_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->get('sitename');
		} elseif ($app->get('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		} elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description')) {
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
