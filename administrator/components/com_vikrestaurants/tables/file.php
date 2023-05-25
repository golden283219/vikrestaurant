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
 * VikRestaurants file table.
 *
 * @since 1.8
 */
class VRETableFile extends JTableVRE
{
	/**
	 * Class constructor.
	 *
	 * @param 	object 	$db  The database driver instance.
	 */
	public function __construct($db)
	{
		// Use CONFIG database table just to avoid errors while
		// instantiating this class
		parent::__construct('#__vikrestaurants_config', 'id', $db);
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

		// make sure the file path has been specified
		if (empty($src['id']))
		{
			$this->setError('Missing file path');

			return false;
		}

		// check if the file was encoded
		if (strpos($src['id'], VREBASE) !== 0 && strpos($src['id'], VREADMIN) !== 0)
		{
			// decode file from base64
			$src['id'] = base64_decode($src['id']);
		}

		// register file
		$this->id = $src['id'];

		// register content to save
		$this->content = isset($src['content']) ? $src['content'] : '';

		return true;
	}

		/**
	 * Method to perform sanity checks on the Table instance properties to
	 * ensure they are safe to store in the database.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 */
	public function check()
	{
		// DO NOT INVOKE PARENT

		// make sure the file is located within VikRestaurants folder
		if (strpos($this->id, VREBASE) !== 0 && strpos($this->id, VREADMIN) !== 0)
		{
			// register error message
			$this->setError('Only files within VikRestaurants can be created or updated.');

			return false;
		}

		return true;
	}

	/**
	 * Method to upload/update a media in the server filesystem.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		// open file resource
		$handle = fopen($this->id, 'wb');
		// write content in file resource
		$bytes = fwrite($handle, $this->content);
		// close file resource
		fclose($handle);

		return is_file($this->id);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed    $ids   Either the record ID or a list of records.
	 * @param 	mixed 	 $path  An optional path from which the file should be
	 * 							deleted. If not specified, the default media
	 * 							folders will be used.
	 *
	 * @return  boolean  True on success.
	 */
	public function delete($ids = null, $path = null)
	{
		/**
		 * For security reasons, files cannot be deleted here.
		 */

		return false;
	}
}
