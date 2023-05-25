<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

jimport('joomla.form.formfield');

class JFormFieldAuthorlist extends JFormField {

    protected $type = 'authorlist';

    protected function getInput(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'name' )));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('block')." = ".$db->quote('0'));
        $query->order('registerDate DESC');

        $db->setQuery($query);  
        $results = $db->loadObjectList();
        $author_list = $results;


        foreach($author_list as $author){
            $options[] = JHTML::_( 'select.option', $author->id, $author->name );
        }
        
        return JHTML::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value);
    }
}
