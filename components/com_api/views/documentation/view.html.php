<?php
/**
 * @package com_api
 * @copyright Copyright (C) 2009 2014 Techjoomla, Tekdi Technologies Pvt. Ltd. All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link http://techjoomla.com
 * Work derived from the original RESTful API by Techjoomla (https://github.com/techjoomla/Joomla-REST-API) 
 * and the com_api extension by Brian Edgerton (http://www.edgewebworks.com)
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class ApiViewDocumentation extends ApiView {

	public $can_register = null;

	public function display($tpl = null) {

		HTMLHelper::stylesheet('com_api.css', 'components/com_api/assets/css/');

		$user	= Factory::getUser();

		$dmodel	= BaseDatabaseModel::getInstance('Documentation', 'ApiModel');
		$endpoints	= $dmodel->getList();

		$kmodel	= BaseDatabaseModel::getInstance('Key', 'ApiModel');
		$tokens	= $kmodel->getList();

		$this->endpoints = $endpoints;
		$this->user = $user;
		$this->tokens = $tokens;

		parent::display($tpl);
	}

}
