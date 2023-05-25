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
 * VikRestaurants media files table.
 *
 * @since 1.8
 */
class VRETableMedia extends JTableVRE
{
	/**
	 * A list of protected images that cannot be 
	 * either removed or renamed.
	 *
	 * @var array
	 */
	protected $protectedImages = array();

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

		$this->protectedImages[] = 'menu_default_icon';
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
		// DO NOT INVOKE PARENT

		$src = (array) $src;

		// register media file name
		if (!empty($src['id']))
		{
			$this->id = $src['id'];
		}
		else
		{
			$this->id = null;
		}

		// register uploaded image, if any
		if (!empty($src['image']))
		{
			$this->image = $src['image'];
		}
		else
		{
			$this->image = null;
		}

		// register new name
		if (!empty($src['name']))
		{
			// use specified name
			$this->name = $src['name'];

			// in case the name doesn't contain the extension type, retrieve it if possible
			if ($this->id && !preg_match("/\.(png|jpe?g|gif|bmp)$/", $this->name))
			{
				// get file properties
				$prop = RestaurantsHelper::getFileProperties(VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $this->id);
				// append extension
				$this->name .= $prop['file_ext'];
			}
		}
		else if ($this->image && !empty($this->image['name']))
		{
			// use name of uploaded image
			$this->name = $this->image['name'];
		}
		else if ($this->id)
		{
			// use same file name
			$this->name = $this->id;
		}
		else
		{
			// no specified name
			$this->name = '';
		}

		// register action
		$this->action = isset($src['action']) ? (int) $src['action'] : 0;

		// register file properties
		$this->properties = array(
			'resize'       => @$src['resize'],
			'resize_value' => @$src['resize_value'],
			'thumb_value'  => @$src['thumb_value'],
		);

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

		// make sure an image was specified when replacing
		if ((int) $this->action > 0 && !$this->image)
		{
			// register error message
			$this->setError(JText::sprintf('VRE_INVALID_REQ_FIELD', JText::_('VRCONFIGFILETYPEERROR')));

			// image not selected
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
		// save media properties first of all
		VikRestaurants::storeMediaProperties($this->properties);

		// DO NOT INVOKE PARENT

		// update existing
		if ($this->id)
		{
			// replace original image
			if ($this->action == 1)
			{
				// upload original image
				$resp = VikRestaurants::uploadFile($this->image, VREMEDIA . DIRECTORY_SEPARATOR, 'jpeg,jpg,png,gif,bmp', $overwrite = true);

				if (!$resp->esit)
				{
					// unable to upload the image, abort
					$this->setError(JText::_($resp->errno == 1 ? 'VRCONFIGUPLOADERROR' : 'VRCONFIGFILETYPEERROR'));

					return false;
				}

				// rename media file to original one
				rename(
					VREMEDIA . DIRECTORY_SEPARATOR . $resp->name,
					VREMEDIA . DIRECTORY_SEPARATOR . $this->id
				);
			}
			// replace thumbnail image
			else if ($this->action == 2)
			{
				// upload thumbnail image
				$resp = VikRestaurants::uploadFile($this->image, VREMEDIA_SMALL . DIRECTORY_SEPARATOR, 'jpeg,jpg,png,gif,bmp', $overwrite = true);

				if (!$resp->esit)
				{
					// unable to upload the image, abort
					$this->setError(JText::_($resp->errno == 1 ? 'VRCONFIGUPLOADERROR' : 'VRCONFIGFILETYPEERROR'));

					return false;
				}

				// rename media file to original one
				rename(
					VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $resp->name,
					VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $this->id
				);
			}
			// replace both original and thumbnail images
			else if ($this->action == 3)
			{
				$resp = VikRestaurants::uploadMedia($this->image, $settings = null, $overwrite = true);

				if (!$resp->esit)
				{
					// unable to upload the image, abort
					$this->setError(JText::_($resp->errno == 1 ? 'VRCONFIGUPLOADERROR' : 'VRCONFIGFILETYPEERROR'));

					return false;
				}

				// rename original media file to previous name
				rename(
					VREMEDIA . DIRECTORY_SEPARATOR . $resp->name,
					VREMEDIA . DIRECTORY_SEPARATOR . $this->id
				);

				// rename thumbnail media file to previous name
				rename(
					VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $resp->name,
					VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $this->id
				);
			}

			// finally, rename the file to the specified name if different
			if ($this->id != $this->name)
			{
				// make sure we are not going to rename a protected media file
				if ($this->isProtected($this->id))
				{
					// unable to rename protected file, raise error
					$this->setError(JText::sprintf('VRMEDIAPROTECTEDERR', $this->id));

					return false;
				}

				// make sure the new destination is empty
				if (is_file(VREMEDIA . DIRECTORY_SEPARATOR . $this->name))
				{
					// specified path is already occupied by a different file, raise error
					$this->setError(JText::sprintf('VRMEDIARENERR', $this->name));

					return false;
				}

				// rename media file and update all the records that were using it
				if ($this->rename($this->id, $this->name))
				{
					// update primary key
					$this->id = $this->name;
				}
			}
		}
		// upload new
		else if ($this->image)
		{
			// upload original image and create thumbnail (do not overwrite existing)
			$resp = VikRestaurants::uploadMedia($this->image);

			if (!$resp->esit)
			{
				// unable to upload the image, abort
				$this->setError(JText::_($resp->errno == 1 ? 'VRCONFIGUPLOADERROR' : 'VRCONFIGFILETYPEERROR'));

				return false;
			}

			// inject name of uploaded file within the table as primary key
			$this->id = $resp->name;
		}

		return true;
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
		if (!$ids)
		{
			return false;
		}

		$res = false;

		// check if the given path is a directory
		if ($path && !is_dir($path))
		{
			// try to decode from base 64
			$path = base64_decode($path);

			if (!is_dir($path))
			{
				// invalid path
				$path = null;
			}
		}

		foreach ((array) $ids as $id)
		{
			// make sure we are deleting an image and it is not protected
			if ($this->isImage($id) && !$this->isProtected($id))
			{
				// check if we should delete the file from the given path
				if ($path)
				{
					if (is_file($path . DIRECTORY_SEPARATOR . $id))
					{
						$res = unlink($path . DIRECTORY_SEPARATOR . $id) || $res;
					}
				}
				else
				{
					// try to delete original file, if exists
					if (is_file(VREMEDIA . DIRECTORY_SEPARATOR . $id))
					{
						$res = unlink(VREMEDIA . DIRECTORY_SEPARATOR . $id) || $res;
					}

					// try to delete thumbnail file, if exists
					if (is_file(VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $id))
					{
						$res = unlink(VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $id) || $res;
					}
				}
			}
		}

		return $res;
	}

	/**
	 * Renames the specified media file with the new one.
	 *
	 * @param 	string 	 $prev  The previous file name.
	 * @param 	string 	 $new   The new file name.
	 *
	 * @return 	boolean  True on success, false otherwise.
	 */
	protected function rename($prev, $new)
	{
		// rename original media file and its thumbnail
		$renamed = rename(VREMEDIA . DIRECTORY_SEPARATOR . $prev, VREMEDIA . DIRECTORY_SEPARATOR . $new)
			&& rename(VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $prev, VREMEDIA_SMALL . DIRECTORY_SEPARATOR . $new);

		if (!$renamed)
		{
			// something went wrong while renaming the files
			return false;
		}

		// relationship between database tables and columns
		// containing media names
		$lookup = array(
			'menus'                    => 'image',
			'menus_section'            => 'image',
			'res_code'                 => 'icon',
			'room'                     => 'image',
			'section_product'          => 'image',
			'takeaway_menus_entry'     => 'img_path',
			'takeaway_menus_attribute' => 'icon',
		);

		$dbo = JFactory::getDbo();

		// mass update all the specified database tables
		foreach ($lookup as $table => $column)
		{
			$q = $dbo->getQuery(true)
				->update($dbo->qn('#__vikrestaurants_' . $table))
				->set($dbo->qn($column) . ' = ' . $dbo->q($new))
				->where($dbo->qn($column) . ' = ' . $dbo->q($prev));

			$dbo->setQuery($q);
			$dbo->execute();
		}

		// retrieve all the special days that MIGHT contain the specified image name
		$q = $dbo->getQuery(true)
			->select($dbo->qn(array('id', 'images')))
			->from($dbo->qn('#__vikrestaurants_specialdays'))
			->where($dbo->qn('images') . ' LIKE ' . $dbo->q("%{$prev}%"));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $sd)
			{
				// decode stored images
				$images = array_filter(explode(';;', $sd->images));

				// find index of previous image
				$index = array_search($prev, $images);

				if ($index !== false)
				{
					// update image name
					$images[$index] = $new;

					$q = $dbo->getQuery(true)
						->update($dbo->qn('#__vikrestaurants_specialdays'))
						->set($dbo->qn('images') . ' = ' . $dbo->q(implode(';;', $images)))
						->where($dbo->qn('id') . ' = ' . $sd->id);

					$dbo->setQuery($q);
					$dbo->execute();
				}
			}
		}

		return true;
	}

	/**
	 * Checks whether the specified file is an image.
	 * This only checks the extension file type.
	 *
	 * @param 	string 	 $file 	The file name.
	 *
	 * @return 	boolean  True if image, false otherwise.
	 */
	protected function isImage($file)
	{
		return preg_match("/\.(png|jpe?g|bmp|gif)$/i", $file);
	}

	/**
	 * Checks whether the specified file is protected.
	 *
	 * @param 	string 	 $file  The filename to check.
	 *
	 * @return 	boolean  True if protected, false otherwise.
	 */
	protected function isProtected($file)
	{
		// iterate protected images
		foreach ($this->protectedImages as $p)
		{
			// check whether the specified file matches the protected image
			if (preg_match("/^{$p}\.(png|jpe?g|bmp|gif)$/i", $file))
			{
				return true;
			}
		}

		return false;
	}
}
