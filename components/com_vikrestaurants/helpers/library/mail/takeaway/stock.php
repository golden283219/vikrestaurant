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

VRELoader::import('library.mail.template');

/**
 * Wrapper used to send mail notifications to the administrators
 * about the items with a stock lower than the specified threshold.
 *
 * @since 1.8
 */
class VREMailTemplateTakeawayStock implements VREMailTemplate
{
	/**
	 * The items list.
	 *
	 * @var array
	 */
	protected $items = null;

	/**
	 * The language tag to use.
	 *
	 * @var string
	 */
	protected $langtag;

	/**
	 * An optional template file to use.
	 *
	 * @var string
	 */
	protected $templateFile;

	/**
	 * A configuration array.
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param 	string 	$langtag  An optional language tag.
	 * @param 	array 	$options  A configuration array.
	 */
	public function __construct($langtag = null, array $options = array())
	{
		if (!$langtag)
		{
			// always use default language in case it is not specified
			$langtag = VikRestaurants::getDefaultLanguage();
		}

		// register language tag
		$this->langtag = $langtag;

		// DO NOT load items here to save a query
		// in case the stock system is disabled.
		// The items will be recovered only when 
		// the template is going to be parsed.

		$this->options = $options;

		// load given language to translate template contents
		VikRestaurants::loadLanguage($this->langtag);
	}

	/**
	 * Returns the code of the template before 
	 * being parsed.
	 *
	 * @param 	string  An optional template file to use.
	 * 					If not specified, the one set in
	 * 					configuration will be used.
	 *
	 * @return 	void
	 */
	public function setFile($file)
	{
		// use specified template file
		$this->templateFile = $file;

		// check if a filename or a path was passed
		if ($file && !is_file($file))
		{
			// make sure we have a valid file path
			$this->templateFile = VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . $file;
		}
	}

	/**
	 * Returns the code of the template before 
	 * being parsed.
	 *
	 * @return 	string
	 */
	public function getTemplate()
	{
		// copy item details in a local
		// variable for being used directly
		// within the template file
		$items = $this->loadItems();

		if ($this->templateFile)
		{
			// use specified template file
			$file = $this->templateFile;
		}
		else
		{
			// get template file from configuration
			$file = VREFactory::getConfig()->get('tkstockmailtmpl');

			// build template path
			$file = VREHELPERS . DIRECTORY_SEPARATOR . 'tk_mail_tmpls' . DIRECTORY_SEPARATOR . $file;
		}

		// make sure the file exists
		if (!is_file($file))
		{
			// missing file, return empty string
			return '';
		}

		// start output buffering 
		ob_start();
		// include file to catch its contents
		include $file;
		// write template contents within a variable
		$content = ob_get_contents();
		// clear output buffer
		ob_end_clean();

		// free space
		unset($items);

		return $content;
	}

	/**
	 * Fetches the subject to be used in the e-mail.
	 *
	 * @return 	string
	 */
	public function getSubject()
	{
		// get restaurant name
		$fromname = VREFactory::getConfig()->getString('restname');

		// fetch subject
		$subject = JText::sprintf('VRTKADMINLOWSTOCKSUBJECT', $fromname);

		// let plugins manipulate the subject for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'stock', 'subject', $subject, $this->items);

		if ($res === false)
		{
			// a plugin prevented the e-mail sending
			return '';
		}

		return $subject;
	}

	/**
	 * Parses the HTML of the template and returns it.
	 *
	 * @return 	string
	 */
	public function getHtml()
	{
		$config = VREFactory::getConfig();

		// load template HTML
		$tmpl = $this->getTemplate();

		// fetch company logo image
		$logo_str = $config->get('companylogo');

		if ($logo_str && is_file(VREMEDIA . DIRECTORY_SEPARATOR . $logo_str))
		{
			$logo_str = '<img src="' . VREMEDIA_URI . $logo_str . '" alt="' . htmlspecialchars($config->get('restname')) . '" />';
		}
		else
		{
			$logo_str = '';
		}

		// build placeholders lookup
		$placeholders = array(
			'logo'           => $logo_str,
			'company_name'   => $config->get('restname'),
			'stocks_content' => JText::_('VRTKADMINLOWSTOCKCONTENT'),
			'stocks_help'    => JText::_('VRTKADMINLOWSTOCKHELP'),
		);

		// parse e-mail template placeholders
		foreach ($placeholders as $tag => $value)
		{
			$tmpl = str_replace("{{$tag}}", $value, $tmpl);
		}

		// let plugins manipulate the content for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'stock', 'content', $tmpl, $this->items);

		if ($res === false)
		{
			// a plugin prevented the e-mail sending
			return '';
		}

		return $tmpl;
	}

	/**
	 * Sends the HTML contents via e-mail.
	 *
	 * @return 	boolean
	 */
	public function send()
	{
		$config = VREFactory::getConfig();

		// get administrators e-mail
		$adminmails = VikRestaurants::getAdminMailList();
		// get sender e-mail address
		$sendermail = VikRestaurants::getSenderMail();
		// get restaurant name
		$fromname = $config->getString('restname');
		
		// fetch subject
		$subject = $this->getSubject();
			
		// parse e-mail template
		$html = $this->getHtml();

		if (empty($subject) || empty($html))
		{
			// do not send e-mail in case the subject or
			// the content are empty
			return false;
		}
		
		// init application
		$vik = VREApplication::getInstance();

		$sent = false;

		foreach ($adminmails as $recipient)
		{
			// send the e-mail notification
			$sent = $vik->sendMail($sendermail, $fromname, $recipient, $recipient, $subject, $html) || $sent;
		}

		/**
		 * Flag products as notified, in order to 
		 * prevent duplicated notifications.
		 *
		 * @since 1.8.4
		 */
		if (JFactory::getApplication()->isClient('site'))
		{
			JTableVRE::addIncludePath(VREADMIN . DIRECTORY_SEPARATOR . 'tables');
		}

		$prodTable = JTableVRE::getInstance('tkentry', 'VRETable');
		$optTable  = JTableVRE::getInstance('tkentryoption', 'VRETable');

		// iterate items to notify
		foreach (static::loadItems() as $menus)
		{
			foreach ($menus->list as $item)
			{
				$data = array(
					'stock_notified' => 1,	
				);

				if ($item->group_option_id)
				{
					// use the option ID as primary key
					$data['id'] = $item->group_option_id;

					// set variation as notified
					$optTable->save($data);
				}
				else
				{
					// use the product ID as primary key
					$data['id'] = $item->id_product;

					// set product as notified
					$prodTable->save($data);
				}
			}
		}
		
		return $sent;
	}

	/**
	 * Checks whether the notification should be sent.
	 *
	 * @return 	boolean
	 */
	public function shouldSend()
	{
		// send only if the stock system is enabled
		if (VREFactory::getConfig()->getBool('tkenablestock') == false)
		{
			// stock system disabled
			return false;
		}

		// make sure there is at least a product to notify
		return count($this->loadItems());
	}

	/**
	 * Finds all the items having low stocks.
	 *
	 * @return 	array
	 */
	protected function loadItems()
	{
		if ($this->items)
		{
			return $this->items;
		}

		$dbo = JFactory::getDbo();

		// filter products only if we are not testing the mail
		if (empty($this->options['test']))
		{
			/**
			 * Make sure the product hasn't been notified yet.
			 *
			 * @since 1.8.4
			 */
			$having = "`product_stock_notified` = 0 AND (`products_in_stock` - `products_used`) <= `product_notify_below`";

			$start  = 0;
			$offset = null;
		}
		else
		{
			// obtain the first 5 products
			$having = "1";

			$start  = isset($this->options['start'])  ? (int) $this->options['start']  : 0;
			$offset = isset($this->options['offset']) ? (int) $this->options['offset'] : 5;
		}

		// build query used to retrieve items with low stocks
		$q = "SELECT
			`e`.`id` AS `id_product`, `e`.`name` AS `product_name`,
			`o`.`id` AS `id_option`, `o`.`name` AS `option_name`, `o`.`stock_enabled` AS `option_stock_enabled`,
			`m`.`id` AS `id_menu`, `m`.`title` AS `menu_title`,

			IF (
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, 0, `o`.`id`
			) AS `group_option_id`,

			IF (
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, `e`.`stock_notified`, `o`.`stock_notified`
			) AS `product_stock_notified`,

			IF(
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, `e`.`notify_below`, `o`.`notify_below`
			) AS `product_notify_below`,

			IF(
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, `e`.`items_in_stock`, `o`.`items_in_stock`
			) AS `product_original_stock`,

			IF(
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, 
				(
					IFNULL(
						(
							SELECT SUM(`so`.`items_available`) 
							FROM `#__vikrestaurants_takeaway_stock_override` AS `so` 
							WHERE `so`.`id_takeaway_entry` = `e`.`id` AND `so`.`id_takeaway_option` IS NULL
						), `e`.`items_in_stock`
					)
				), (
					IFNULL(
						(
							SELECT SUM(`so`.`items_available`) 
							FROM `#__vikrestaurants_takeaway_stock_override` AS `so` 
							WHERE `so`.`id_takeaway_entry` = `e`.`id` AND `so`.`id_takeaway_option` = `o`.`id`
						), `o`.`items_in_stock`
					)
				)
			) AS `products_in_stock`,

			IF(
				`o`.`id` IS NULL OR `o`.`stock_enabled` = 0, 
				(
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							LEFT JOIN `#__vikrestaurants_takeaway_menus_entry_option` AS `io` ON `i`.`id_product_option` = `io`.`id`
							WHERE (`r`.`status` = 'CONFIRMED' OR `r`.`status` = 'PENDING') AND `i`.`id_product` = `e`.`id`
							AND (`o`.`id` IS NULL OR `io`.`stock_enabled` = 0)
						), 0
					)
				), (
					IFNULL(
						(
							SELECT SUM(`i`.`quantity`)
							FROM `#__vikrestaurants_takeaway_reservation` AS `r` 
							LEFT JOIN `#__vikrestaurants_takeaway_res_prod_assoc` AS `i` ON `i`.`id_res` = `r`.`id`
							WHERE (`r`.`status` = 'CONFIRMED' OR `r`.`status` = 'PENDING') AND `i`.`id_product` = `e`.`id` AND `i`.`id_product_option` = `o`.`id`
						), 0
					)
				)
			) AS `products_used`

			FROM
				`#__vikrestaurants_takeaway_menus_entry` AS `e`
			LEFT JOIN
				`#__vikrestaurants_takeaway_menus_entry_option` AS `o` ON `e`.`id` = `o`.`id_takeaway_menu_entry`
			LEFT JOIN
				`#__vikrestaurants_takeaway_menus` AS `m` ON `m`.`id` = `e`.`id_takeaway_menu` 
			GROUP BY
				`e`.`id`, `group_option_id`
			HAVING
				{$having}
			ORDER BY
				`m`.`ordering` ASC,
				(`products_in_stock` - `products_used`) ASC,
				`e`.`ordering` ASC,
				`o`.`ordering` ASC";

		$dbo->setQuery($q, $start, $offset);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			// retrieve items
			$items = $dbo->loadObjectList();

			// get translator
			$translator = VREFactory::getTranslator();

			$menu_ids    = array();
			$product_ids = array();
			$option_ids  = array();

			foreach ($items as $item)
			{
				$menu_ids[]    = $item->id_menu;
				$product_ids[] = $item->id_product;

				if ($item->id_option && $item->option_stock_enabled)
				{
					$option_ids[] = $item->id_option;
				}
			}

			// pre-load menus translations
			$menuLang = $translator->load('tkmenu', array_unique($menu_ids), $this->langtag);
			// pre-load products translations
			$prodLang = $translator->load('tkentry', array_unique($product_ids), $this->langtag);
			// pre-load products options translations
			$optLang = $translator->load('tkentryoption', array_unique($option_ids), $this->langtag);

			$this->items = array();

			// iterate items and apply translationss
			foreach ($items as $item)
			{
				// translate menu title for the given language
				$menu_tx = $menuLang->getTranslation($item->id_menu, $this->langtag);

				if ($menu_tx)
				{
					// inject translation within order item
					$item->menu_title = $menu_tx->title;
				}

				// translate product name for the given language
				$prod_tx = $prodLang->getTranslation($item->id_product, $this->langtag);

				if ($prod_tx)
				{
					// inject translation within order item
					$item->product_name = $prod_tx->name;
				}

				if ($item->id_option && $item->option_stock_enabled)
				{
					// translate product option name for the given language
					$opt_tx = $optLang->getTranslation($item->id_option, $this->langtag);

					if ($opt_tx)
					{
						// inject translation within order item
						$item->option_name = $opt_tx->name;
					}
				}

				// group by menu
				if (!isset($this->items[$item->id_menu]))
				{
					$menu = new stdClass;
					$menu->id    = $item->id_menu;
					$menu->title = $item->menu_title;
					$menu->list  = array();

					$this->items[$item->id_menu] = $menu;
				}

				// fetch name
				$item->name = $item->product_name . ($item->option_name && $item->option_stock_enabled ? ' - ' . $item->option_name : '');

				// calculate remaining in stock
				$item->remaining = $item->products_in_stock - $item->products_used;

				// push item in list
				$this->items[$item->id_menu]->list[] = $item;
			}
		}
		else
		{
			// no items to fetch
			$this->items = array();
		}

		return $this->items;
	}
}
