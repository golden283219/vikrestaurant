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
 * VikRestaurants Media Manager HTML helper.
 *
 * @since 1.8
 */
abstract class VREHtmlMediaManager
{
	/**
	 * A list of IDs used by the fields.
	 * Useful to avoid duplicate IDs in case of
	 * same names and missing ID attributes.
	 *
	 * @var array
	 */
	protected static $ids = array();

	/**
	 * Displays a field to handle the media manager.
	 *
	 * @param 	string  $name        The field name.
	 * @param 	mixed 	$value       The field value or an array of images.
	 * @param 	string 	$id          The field ID attribute. Leave empty to
	 * 								 let the system generates a unique one.
	 * @param 	array   $attributes  An array of input attributes.
	 *
	 * @return  string  The field HTML.
	 *
	 * @uses 	script()
	 * @uses 	modal()
	 */
	public static function field($name, $value = null, $id = '', array $attributes = array())
	{
		// make sure the needed scripts have been loaded
		self::script();

		// create field layout
		$layout = new JLayoutFile('mediamanager.field');

		// configure field attributes
		$data = array();
		$data['name']  = $name;
		$data['value'] = $value;
		
		$id = $id ? $id : 'vre-' . preg_replace("/[^a-zA-Z0-9\-_]+/", '-', $name) . '-mediamanager';
		$data['id'] = $id;

		$cont = 1;

		// make sure the ID hasn't been used yet
		while (in_array($data['id'], static::$ids))
		{
			$data['id'] = $id . '-' . (++$cont);
		}

		static::$ids[] = $data['id'];

		// unset from attributes to avoid displaying the same attributes twice
		unset($attributes['name']);
		unset($attributes['value']);
		unset($attributes['id']);

		if (!isset($attributes['size']))
		{
			$attributes['size'] = 28;
		}

		// append "[]" if the field should support multiple files
		if (!empty($attributes['multiple']) && !preg_match("/\[\]$/", $data['name']))
		{
			$data['name'] .= '[]';	
		}

		if (!isset($attributes['placeholder']))
		{
			$attributes['placeholder'] = JText::_('VRE_DEF_N_SELECTED_0');
		}

		// check whether the preview button should be hidden
		if (isset($attributes['preview']) && !$attributes['preview'])
		{
			$data['preview'] = false;
		}
		else
		{
			$data['preview'] = true;
		}
		unset($attributes['preview']);

		// extract modal title from attributes
		if (isset($attributes['modaltitle']))
		{
			$modal_title = $attributes['modaltitle'];
			unset($attributes['modaltitle']);
		}
		else
		{
			$modal_title = null;
		}

		// look for a custom path
		if (isset($attributes['path']))
		{
			$data['path'] = $attributes['path'];
			unset($attributes['path']);
		}
		else
		{
			$data['path'] = null;
		}

		// assign attributes array
		$data['attrs'] = $attributes;

		// fetch modal HTML
		$data['modal'] = self::modal($modal_title);

		// return HTML of the field
		return $layout->render($data);
	}

	/**
	 * Returns the HTML of the modal containing the media manager.
	 *
	 * @param 	sring  $title  An optional title for the modal box.
	 *
	 * @return 	mixed  The modal HTML or false in case the modal was already loaded.
	 */
	public static function modal($title = null)
	{
		// display modal only once
		static $modal = 0;

		if ($modal)
		{	
			return false;
		}

		$modal = 1;

		// fetch modal
		return JHtml::_(
			'bootstrap.renderModal',
			'jmodal-mediamanager',
			array(
				'title'       => $title ? $title : JText::_('VRE_MEDIA_MANAGER'),
				'closeButton' => true,
				'keyboard'    => true, 
				'bodyHeight'  => 80,
				'url'         => '',
				'footer'      => '<button type="button" class="btn btn-success" id="media-manager-save">' . JText::_('JAPPLY') . '</button>',
			)
		);
	}

	/**
	 * Includes the scripts needed to support a media manager.
	 *
	 * @return 	boolean  True if loaded, false otherwise.
	 */
	public static function script()
	{
		static $loaded = 0;

		// load only once
		if ($loaded)
		{
			return false;
		}

		$loaded = 1;

		$vik = VREApplication::getInstance();

		// make sure fancybox is loaded for images preview
		JHtml::_('vrehtml.assets.fancybox');
		// make sure fontawesome is loaded for buttons
		JHtml::_('vrehtml.assets.fontawesome');

		// include media manager script
		$vik->addScript(VREASSETS_ADMIN_URI . 'js/mediamanager.js');

		$openModalJS  = $vik->bootOpenModalJS();
		$closeModalJS = $vik->bootDismissModalJS();

		$folder = addslashes(VREMEDIA_URI);

		// add support for placeholder translation
		JText::script('VRMANAGEMEDIA10');
		JText::script('VRE_DEF_N_SELECTED');
		JText::script('VRE_DEF_N_SELECTED_0');
		JText::script('VRE_DEF_N_SELECTED_1');

		// add declaration to support dynamic scripts
		JFactory::getDocument()->addScriptDeclaration(
<<<JS
VRE_MEDIA_FOLDER = '{$folder}';

function vreMediaOpenJModal(id, path) {
	VRE_SELECTED_FIELD = id;

	var url     = 'index.php?option=com_vikrestaurants&view=media&layout=modal&tmpl=component';
	var id      = 'mediamanager';
	var jqmodal = true;

	var val = jQuery(VRE_SELECTED_FIELD).mediamanager('val');

	if (!Array.isArray(val)) {
		val = [val];
	}

	// clear selected files before opening modal
	VRE_TMP_FILES = [];

	for (var i = 0; i < val.length; i++) {
		if (val[i]) {
			url += '&media[]=' + val[i];

			VRE_TMP_FILES.push(val[i]);
		}
	}

	if (jQuery(VRE_SELECTED_FIELD).attr('multiple')) {
		url += '&multiple=1';
	}

	if (path) {
		// include path if specified (base64 encoded)
		url += '&path=' + path;
	}

	{$openModalJS}
}

function vreMediaCloseJModal() {
	id = 'mediamanager';
	{$closeModalJS}
}
JS
		);

		return true;
	}
}
