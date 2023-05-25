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
 * This class implements helpful methods for abstract tables.
 *
 * @since 1.8
 * @since 1.8.2 Renamed from JTableUI
 */
class JTableVRE extends JTable
{
	/**
	 * A list of fields that requires a validation.
	 * Children classes must inherit this property in
	 * order to include additional fields within the
	 * validation process.
	 *
	 * @var array
	 */
	protected $_requiredFields = array();

	/**
	 * Flag used to overwrite the "update nulls" argument
	 * received by the store method without having to
	 * override it.
	 *
	 * @var   boolean
	 * @since 1.8.5
	 */
	protected $_updateNulls = false;

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
		$result = true;

		// create "before save" event
		$event = 'onBeforeSave' . ucfirst($this->getName());

		try
		{
			/**
			 * Trigger event to allow the plugins to bind the object that
			 * is going to be saved. The event to use is built as:
			 * onBeforeSave[TABLE_NAME], where [TABLE_NAME] is the name of
			 * file in which the table child is written.
			 *
			 * @param 	mixed 	 &$src 	 The array/object to bind.
			 * @param 	JTable   $table  The table instance.
			 *
			 * @return 	boolean  False to abort saving.
			 *
			 * @throws 	Exception  It is possible to throw an exception to abort
			 *                     the saving process and return a readable message.
			 *
			 * @since 	1.8
			 */
			if (VREFactory::getEventDispatcher()->false($event, array(&$src, $this)))
			{
				// abort in case a plugin returned false
				$result = false;
			}
		}
		catch (Exception $e)
		{
			// register the error thrown by the plugin and abort 
			$this->setError($e);

			$result = false;
		}

		// extract table name from class
		if (preg_match("/^([a-z]+)Table(.+?)$/i", get_class($this), $match))
		{
			$prefix = strtolower($match[1]);
			$name   = strtolower($match[2]);

			/**
			 * Before registering the user state, make sure that
			 * the headers haven't been sent yet, in order to avoid
			 * post fatal errors.
			 *
			 * @since 1.8.3
			 */
			if (headers_sent() == false)
			{
				// set user state for later use
				JFactory::getApplication()->setUserState($prefix . '.' . $name . '.data', $src);
			}
		}

		// dispatch parent to complete binding
		return $result && parent::bind($src, $ignore);
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to
	 * ensure they are safe to store in the database.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		// iterate required fields
		foreach ($this->_requiredFields as $col)
		{
			// in case the property was specified, make sure it is not an empty string
			if (isset($this->{$col}) && is_scalar($this->{$col}) && !strlen(trim($this->{$col})))
			{
				// register error message
				$this->setError(JText::sprintf('VRE_MISSING_REQ_FIELD', $col));

				// unsafe record
				return false;
			}
		}

		// safe record
		return true;
	}

	/**
	 * Method to store a row in the database from the Table instance properties.
	 *
	 * If a primary key value is set the row with that primary key value will be updated with the instance property values.
	 * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		$is_new = empty($this->id);

		if (!empty($this->_updateNulls))
		{
			// force update of NULL columns
			$updateNulls = true;

			// reset internal property
			$this->_updateNulls = false;
		}

		// invoke parent to store the record
		if (!parent::store($updateNulls))
		{
			// do not proceed in case of error
			return false;
		}

		// get customer data
		$args = $this->getProperties();

		// create "after save" event
		$event = 'onAfterSave' . ucfirst($this->getName());

		/**
		 * Trigger event to allow the plugins to make something after saving
		 * a record in the database. The event to use is built as:
		 * onAfterSave[TABLE_NAME], where [TABLE_NAME] is the name of
		 * file in which the table child is written.
		 *
		 * @param 	array 	 $args    The saved record.
		 * @param 	boolean  $is_new  True if the record was inserted.
		 * @param 	JTable   $table   The table instance.
		 *
		 * @return 	void
		 *
		 * @since 	1.8
		 */
		VREFactory::getEventDispatcher()->trigger($event, array($args, $is_new, $this));

		return true;
	}

	/**
	 * Sets the relations between the given entry and the specified records list.
	 * In order to be used, the children class must declare the following properties:
	 * - _tbl_assoc_pk  the assoc column of the primary table;
	 * - _tbl_assoc_fk  the assoc column of the foreign table.
	 *
	 * @param 	mixed  $id       The assoc column of the primary table.
	 * @param 	array  $records  The assoc column of the foreign table.
	 *
	 * @return 	void
	 */
	public function setRelation($id, array $records)
	{
		if (empty($id) || !isset($this->_tbl_assoc_pk) || !isset($this->_tbl_assoc_fk))
		{
			return;
		}

		$dbo = JFactory::getDbo();

		// get existing records

		$existing = array();

		$q = $dbo->getQuery(true)
			->select($dbo->qn($this->_tbl_assoc_fk))
			->from($dbo->qn($this->getTableName()))
			->where($dbo->qn($this->_tbl_assoc_pk) . ' = ' . (int) $id);

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$existing = $dbo->loadColumn();
		}

		// insert new records

		$has = false;

		$q = $dbo->getQuery(true)
			->insert($dbo->qn($this->getTableName()))
			->columns($dbo->qn(array($this->_tbl_assoc_pk, $this->_tbl_assoc_fk)));

		foreach ($records as $s)
		{
			// make sure the record to push doesn't exist yet
			if (!in_array($s, $existing))
			{
				$q->values($id . ', ' . $s);
				$has = true;
			}
		}

		if ($has)
		{
			$dbo->setQuery($q);
			$dbo->execute();
		}

		// delete records

		$delete = array();

		foreach ($existing as $s)
		{
			// make sure the records to delete is not contained in the selected records
			if (!in_array($s, $records))
			{
				$delete[] = $s;
			}
		}

		if (count($delete))
		{
			$q = $dbo->getQuery(true)
				->delete($dbo->qn($this->getTableName()))
				->where(array(
					$dbo->qn($this->_tbl_assoc_pk) . ' = ' . $id,
					$dbo->qn($this->_tbl_assoc_fk) . ' IN (' . implode(',', $delete) . ')',
				));

			$dbo->setQuery($q);
			$dbo->execute();
		}
	}

	/**
	 * Recovers the table class name.
	 *
	 * @return 	string  The class name.
	 */
	protected function getName()
	{
		// extract table name from object class
		if (preg_match("/Table([a-z0-9_]+)$/i", get_class($this), $match))
		{
			return strtolower(end($match));
		}

		return null;
	}
}
