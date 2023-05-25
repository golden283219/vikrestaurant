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
 * Wrapper used to handle mail notifications for the administrators
 * when someone leaves a review for a take-away product.
 *
 * @since 1.8
 */
class VREMailTemplateTakeawayReview implements VREMailTemplate
{
	/**
	 * The review object.
	 *
	 * @var object
	 */
	protected $review;

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
	 * Class constructor.
	 *
	 * @param 	integer  $id       The review ID.
	 * @param 	string 	 $langtag  An optional language tag.
	 */
	public function __construct($id, $langtag = null)
	{
		if (!$langtag)
		{
			// always use default language in case it is not specified
			$langtag = VikRestaurants::getDefaultLanguage();
		}

		// load review helper
		VRELoader::import('library.reviews.handler');

		// initialize review handler
		$handler = new ReviewsHandler();
		// obtain the product review
		$this->review = $handler->takeaway()->getReview($id);

		// register language tag
		$this->langtag = $langtag;

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
		// copy review details in a local
		// variable for being used directly
		// within the template file
		$review = $this->review;

		if ($this->templateFile)
		{
			// use specified template file
			$file = $this->templateFile;
		}
		else
		{
			// get template file from configuration
			$file = VREFactory::getConfig()->get('tkreviewmailtmpl');

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
		unset($review);

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
		$subject = JText::sprintf('VRREVIEWSUBJECT', $fromname);

		// let plugins manipulate the subject for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'review', 'subject', $subject, $this->review);

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

		$vik = VREApplication::getInstance();

		// fetch product image URI
		if ($this->review->productImage)
		{
			$product_image_uri = VREMEDIA_SMALL_URI . $this->review->productImage;
		}
		else
		{
			$product_image_uri = '';
		}

		// fetch verified review
		if ($this->review->verified)
		{
			$review_verified = JText::_('VRREVIEWVERIFIED');
		}
		else
		{
			$review_verified = '';
		}

		// fetch review rating
		$star    = '<img src="' . VREASSETS_URI . 'css/images/rating-star.png" alt="" />';
		$no_star = '<img src="' . VREASSETS_URI . 'css/images/rating-star-no.png" alt="" />';

		// Repeat star image for the value of the rating.
		// Then display empty stars for the missed rating.
		$review_rating = str_repeat($star, $this->review->rating)
			. str_repeat($no_star, 5 - $this->review->rating);

		// fetch confirmation link HREF
		$confirmation_link_href = "index.php?option=com_vikrestaurants&task=approve_review&id={$this->review->id}&conf_key={$this->review->conf_key}";
		$confirmation_link_href = $vik->routeForExternalUse($confirmation_link_href);

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

		// get user ID
		if ($this->review->jid > 0)
		{
			// get user details
			$user = JFactory::getUser($this->review->jid);
		}
		else
		{
			$user = null;
		}

		// build placeholders lookup
		$placeholders = array(
			'logo'                  => $logo_str,
			'company_name'          => $config->get('restname'),
			'review_content'        => JText::sprintf('VRREVIEWCONTENT', $this->review->email, $this->review->name),
			'review_product_menu'   => $this->review->menuTitle,
			'review_product_name'   => $this->review->productName,
			'review_product_desc'   => $this->review->productDescription,
			'review_product_image'  => $product_image_uri,
			'review_title'          => $this->review->title,
			'review_comment'        => $this->review->comment,
			'review_rating'         => $review_rating,
			'review_verified'       => $review_verified,
			'confirmation_link'     => $confirmation_link_href,
			'user_name'             => $user ? $user->name : '',
			'user_username'         => $user ? $user->username : '',
			'user_email'            => $user ? $user->email : '',
		);

		// parse e-mail template placeholders
		foreach ($placeholders as $tag => $value)
		{
			$tmpl = str_replace("{{$tag}}", $value, $tmpl);
		}

		// let plugins manipulate the content for this e-mail template
		$res = VREMailFactory::letPluginsManipulateMail('takeaway', 'review', 'content', $tmpl, $this->review);

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
		
		return $sent;
	}

	/**
	 * Checks whether the notification should be sent.
	 *
	 * @return 	boolean
	 */
	public function shouldSend()
	{
		// always send notification when a review is left
		return true;
	}
}
