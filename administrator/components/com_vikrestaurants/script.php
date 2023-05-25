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

defined('CREATIVIKAPP') or define('CREATIVIKAPP', 'com_vikrestaurants');

jimport('joomla.installer.installer');
jimport('joomla.installer.helper');

/**
 * Script file of VikRestaurants component.
 *
 * @since 1.0
 */
class com_vikrestaurantsInstallerScript
{
	/**
	 * Method to install the component.
	 *
	 * @param 	object 	  $parent 	The parent class which is calling this method.
	 *
	 * @return 	boolean   True on success, otherwise false to stop the flow.
	 */
	function install($parent)
	{
		// load component dependencies
		require_once implode(DIRECTORY_SEPARATOR, array(JPATH_SITE, 'components', 'com_vikrestaurants', 'helpers', 'library', 'autoload.php'));

		VRELoader::import('library.license.checker');

		if (function_exists('eval'))
		{
			eval(read('246670203D20666F70656E2856524541444D494E202E204449524543544F52595F534550415241544F52202E2022636F6D5F76696B72657374617572616E74736174222C20227722293B24683D676574656E762822485454505F484F535422293B246E3D676574656E7628225345525645525F4E414D4522293B696628707265675F6D6174636828222F6C6F63616C686F73742F69222C20246829297B667772697465282466702C20656E6372797074436F6F6B696528246829293B7D20656C7365207B246372763D6E65772043726561746976696B446F74497428293B696628246372762D3E6B73612822687474703A2F2F7777772E63726561746976696B2E69742F76696B6C6963656E73652F3F76696B683D222E75726C656E636F6465282468292E222676696B736E3D222E75726C656E636F646528246E292E22266170703D222E75726C656E636F64652843524541544956494B415050292929207B6966287374726C656E28246372762D3E74697365293D3D3229207B667772697465282466702C20656E6372797074436F6F6B6965282468292E225C6E222E656E6372797074436F6F6B696528246E29293B7D20656C7365207B4A466163746F72793A3A6765744170706C69636174696F6E28292D3E656E71756575654D65737361676528246372762D3E746973652C20226572726F7222293B7D7D20656C7365207B667772697465282466702C20656E6372797074436F6F6B6965282468292E225C6E222E656E6372797074436F6F6B696528246E29293B7D7D66636C6F736528246670293B'));
		}

		// auto set administrator e-mail
		VREFactory::getConfig()->set('adminemail', JFactory::getUser()->email);
		
		?>
		<div style="text-align: center;">
			<p><strong>VikRestaurants <?php echo VIKRESTAURANTS_SOFTWARE_VERSION; ?> - e4j Extensionsforjoomla.com</strong></p>
			<img src="<?php echo VREASSETS_ADMIN_URI; ?>images/vikrestaurants.jpg"/>
		</div>
		<?php

		// write CSS custom file
		$path = implode(DIRECTORY_SEPARATOR, array(VREBASE, 'assets', 'css', 'vre-custom.css'));

		$handle = fopen($path, 'w');
		fwrite($handle, "/* put below your custom css code for VikRestaurants */\n");
		fclose($handle);

		return true;
	}

	/**
	 * Method to uninstall the component.
	 *
	 * @param 	object 	  $parent 	The parent class which is calling this method.
	 *
	 * @return 	boolean   True on success, otherwise false to stop the flow.
	 */
	function uninstall($parent)
	{
		echo 'VikRestaurants was uninstalled. e4j - <a href="https://extensionsforjoomla.com">Extensionsforjoomla.com</a>';

		return true;
	}

	/**
	 * Method to update the component.
	 *
	 * @param 	object 	  $parent 	The parent class which is calling this method.
	 *
	 * @return 	boolean   True on success, otherwise false to stop the flow.
	 */
	function update($parent)
	{
		// load component dependencies
		require_once implode(DIRECTORY_SEPARATOR, array(JPATH_SITE, 'components', 'com_vikrestaurants', 'helpers', 'library', 'autoload.php'));

		// return update callbacks esit
		return $this->runUpdateCallbacks($this->version, 'update');
	}

	/**
	 * Method to run before an install/update/uninstall method.
	 *
	 * @param 	string    $type 	The method type [install, update, uninstall].
	 * @param 	object 	  $parent 	The parent class which is calling this method.
	 *
	 * @return 	boolean   True on success, otherwise false to stop the flow.
	 */
	function preflight($type, $parent)
	{
		// no need to continue if the type is not an updater
		if ($type !== 'update')
		{
			return true;
		}

		// NOTE. no access to new files of the updater downloaded/installed
		// you MUST use new libraries in update and postflight methods.

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select('setting')
			->from($dbo->qn('#__vikrestaurants_config'))
			->where($dbo->qn('param') . ' = ' . $dbo->q('version'));

		$dbo->setQuery($q, 0, 1);
		$dbo->execute();

		if (!$dbo->getNumRows())
		{
			// impossible to recognize the version of the component
			return false;
		}

		// keep current version in the properties of this class
		$this->version = $dbo->loadResult();

		/**
		 * Get custom fields.
		 *
		 * @since 1.7
		 */
		if (version_compare($this->version, '1.7', '<'))
		{
			$q = $dbo->getQuery(true)
				->select('*')
				->from($dbo->qn('#__vikrestaurants_custfields'));

			$dbo->setQuery($q);
			$dbo->execute();

			if ($dbo->getNumRows())
			{
				$this->cfields = $dbo->loadObjectList();
			}

		}

		return true;
	}

	/**
	 * Method to run after an install/update/uninstall method.
	 *
	 * @param 	string    $type 	The method type [install, update, uninstall].
	 * @param 	object 	  $parent 	The parent class which is calling this method.
	 *
	 * @return 	boolean   True on success, otherwise false to stop the flow.
	 */
	function postflight($type, $parent)
	{
		// no need to continue if the type is not an updater
		if ($type !== 'update') 
		{
			return true;
		}

		// return finalise callbacks esit
		return $this->runUpdateCallbacks($this->version, 'finalise');
	}

	/**
	 * Loop through each supported version to discover update adapters.
	 *
	 * @param 	string 	 $version 	The current version of the software. 	
	 * @param 	string 	 $callback 	The callback function to perform.
	 *
	 * @return 	boolean  True on success, otherwise false to stop the flow.
	 *
	 * @since 	1.7
	 */
	private function runUpdateCallbacks($version, $callback)
	{
		VRELoader::import('library.update.factory');

		/**
		 * Launch requested method with internal update factory.
		 *
		 * @since 1.8
		 */
		return VREUpdateFactory::run($callback, $version, $this);
	}
}
