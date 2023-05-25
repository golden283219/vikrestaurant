<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

class SpauthorarchiveRouterRulesLegacy implements JComponentRouterRulesInterface {

	public function __construct($router) {
		$this->router = $router;
	}

	public function preprocess(&$query) { }
	
	public function build(&$query, &$segments) {
		$app 		= JFactory::getApplication();
		$menu   	= $app->getMenu();
		$segments = array();
		
		if (empty($query['Itemid'])) {
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		} else {
			$menuItem = $menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_spauthorarchive') {
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (isset($query['view'])) {
			$view = $query['view'];
		} else {
			return $segments;
		}

		if (($menuItem instanceof stdClass) && $query['view'] == 'articles') {
			if (!$menuItemGiven) {
				$segments[] = $view;
			}

			// articles
			if ($view == 'articles') {	
				if(isset($query['uid']) && $query['uid']) {
					$segments[] = 'author';
					$segments[] = $query['uid'];
					unset($query['uid']);
				}
				unset($query['view']);
			}
		}
		
		// if segment's have : replace by -
		foreach($segments as $key=> &$segment){
			if($key == 1) {
				//authors
				if (strpos($segment, ':')) {
					$segment = explode(':', $segment)[1];
				} elseif($segment) { // articles
					$user_info = JFactory::getUser($segments[1]);
					$segment = $user_info->username;
				}
			}
		}

		return $segments;
	}
	
	public function parse(&$segments, &$vars) {
		
		
		$app 		= JFactory::getApplication();
		$menu   	= $app->getMenu();
		$item 		= $menu->getActive();
		$total 		= count($segments);
		$vars 		= array();

		if (strpos($segments[1], '-')) {
			$segments[1] = explode('-', $segments[1])[1];
		}

		switch ($item->query['view']) {
			case 'authors':
				$vars['view'] 		= 'articles';
				$userid = JFactory::getUser($segments[1])->id;
				$vars['uid'] = $userid; // uid

			break;
			
			default:
				$vars['view'] 	= 'articles';
				$vars['uid'] 	= (int) $id;
			break;
		}

		return $vars;
	}

	private function multiexplode ($delimiters,$data) {
		$MakeReady = str_replace($delimiters, $delimiters[0], $data);
		$return    = explode($delimiters[0], $MakeReady);
		return $return;
	}
	
}
