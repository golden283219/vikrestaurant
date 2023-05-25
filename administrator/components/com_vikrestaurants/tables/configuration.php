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
 * VikRestaurants configuration table.
 *
 * @since 1.8
 */
class VRETableConfiguration extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		parent::__construct('#__vikrestaurants_config', 'id', $db);

		// register required fields
		$this->_requiredFields[] = 'param';
	}

	/**
	 * Method to bind an associative array or object to the Table instance. This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the Table instance.
	 * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 */
	public function bind($src, $ignore = array())
	{
		$src = (array) $src;

		if (empty($src['param']))
		{
			// prevent creation of new configuration records
			// in case the "param" attribute was not specified
			return false;
		}

		$dbo = JFactory::getDbo();

		$q = $dbo->getQuery(true)
			->select($dbo->qn('id'))
			->from($dbo->qn('#__vikrestaurants_config'))
			->where($dbo->qn('param') . ' = ' . $dbo->q($src['param']));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// overwrite ID for update
			$src['id'] = (int) $dbo->loadResult();
		}

		// bind the details before save
		return parent::bind($src, $ignore);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed    $ids  Either the record ID or a list of records.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($ids = null)
	{
		// do not delete configuration records
		return false;
	}

	/**
	 * Saves the whole configuration.
	 *
	 * @param 	array 	 $args  An associative array.
	 *
	 * @return 	boolean  True in case something has changed, false otherwise.
	 */
	public function saveAll(array $args = array())
	{
		try
		{
			/**
			 * Trigger event to allow the plugins to bind the object that
			 * is going to be saved.
			 *
			 * @param 	mixed 	 &$config  The configuration array.
			 * @param 	JTable   $table    The table instance.
			 *
			 * @return 	boolean  False to abort saving.
			 *
			 * @throws 	Exception  It is possible to throw an exception to abort
			 *                     the saving process and return a readable message.
			 *
			 * @since 	1.8.3
			 */
			if (VREFactory::getEventDispatcher()->false('onBeforeSaveConfig', array(&$args, $this)))
			{
				// abort in case a plugin returned false
				return false;
			}
		}
		catch (Exception $e)
		{
			// register the error thrown by the plugin and abort 
			$this->setError($e);

			return false;
		}

		$dbo = JFactory::getDbo();

		$changed = false;

		foreach ($args as $param => $setting)
		{
			$q = $dbo->getQuery(true)
				->update($dbo->qn($this->getTableName()))
				->set($dbo->qn('setting') . ' = ' . $dbo->q($setting))
				->where($dbo->qn('param') . ' = ' . $dbo->q($param));

			$dbo->setQuery($q);
			$dbo->execute();

			$changed = $changed || $dbo->getAffectedRows();
		}

		/**
		 * Trigger event to allow the plugins to make something after saving
		 * a record in the database.
		 *
		 * @param 	array 	 $args     The configuration array.
		 * @param 	boolean  $changed  True in case something has changed.
		 * @param 	JTable   $table    The table instance.
		 *
		 * @return 	void
		 *
		 * @since 	1.8.3
		 */
		VREFactory::getEventDispatcher()->trigger('onAfterSaveConfig', array($args, $changed, $this));

		return $changed;
	}
}
