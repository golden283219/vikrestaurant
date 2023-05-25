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
 * This class is used to handle Diners Club credit cards.
 *
 * @since  1.7
 */
class CCDinersClub extends CreditCard
{
	/**
	 * Check if the credit card number is valid.
	 * The card number is valid when its length is equals to 14.
	 *
	 * @return 	boolean 	True if the card number is valid.
	 */
	public function isCardNumberValid()
	{
		return (strlen($this->getCardNumber()) == $this->getCardNumberDigits());
	}

	/**
	 * Get the credit card number digits count.
	 *
	 * @return 	integer 	Return the digits count (14).
	 */
	public function getCardNumberDigits()
	{
		return 14;
	}

	/**
	 * Format the credit card number to be more human-readable.
	 * e.g. 3636 0000 0000 00
	 *
	 * @return 	string 	The formatted card number. 
	 */
	public function formatCardNumber()
	{
		$cc = $this->getCardNumber();

		return substr($cc, 0, 4).' '.substr($cc, 4, 4).' '.substr($cc, 8, 4).' '.substr($cc, 12, 2);
	}

	/**
	 * Get a masked version of the credit card for privacy.
	 * e.g. 3636 **** **** **
	 * e.g. **** 0000 0000 00
	 *
	 * @return 	array 	A list containing 2 different masked versions of card number. 
	 */
	public function getMaskedCardNumber()
	{
		return array(
			substr($this->getCardNumber(), 0, 4).' **** **** **',
			'**** '.substr($this->getCardNumber(), 4, 4).' '.substr($this->getCardNumber(), 8, 4).' '.substr($this->getCardNumber(), 12, 2)
		);
	}

	/**
	 * Get the Diners Club alias.
	 *
	 * @return 	string 	The alias of the credit card brand (diners).
	 */
	public function getBrandAlias()
	{
		return CreditCard::DINERS_CLUB;
	}

	/**
	 * Get the name of the credit card brand.
	 *
	 * @return 	string 	The name of the credit card brand (Diners Club).
	 */
	public function getBrandName()
	{
		return 'Diners Club';
	}
	
}
