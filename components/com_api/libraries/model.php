<?php
/**
 * @package com_api
 * @copyright Copyright (C) 2009 2014 Techjoomla, Tekdi Technologies Pvt. Ltd. All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link http://techjoomla.com
 * Work derived from the original RESTful API by Techjoomla (https://github.com/techjoomla/Joomla-REST-API) 
 * and the com_api extension by Brian Edgerton (http://www.edgewebworks.com)
*/

defined('_JEXEC') or die( 'Restricted access' );

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Pagination\Pagination;

class ApiModel extends BaseDatabaseModel {

	public function __construct($config=array()) {
		parent::__construct($config);
	}

	public function getPagination() {

		if (!$this->get('total')) :
			$this->getTotal();
		endif;

		if (empty($this->pagination)) {
		  $this->pagination = new Pagination($this->get('total'), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->pagination;
  	}
}
