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

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * VikRestaurants reservation bill management view.
 *
 * @since 1.7
 */
class VikRestaurantsViewmanagebill extends JViewVRE
{	
	/**
	 * VikRestaurants view display method.
	 *
	 * @return 	void
	 */
	function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$dbo   = JFactory::getDbo();
		
		$ids = $input->get('cid', array(0), 'uint');

		// set the toolbar
		$this->addToolBar();

		$q = $dbo->getQuery(true);

		$q->select($dbo->qn(array(
				'r.id',
				'r.bill_closed',
				'r.bill_value',
				'r.deposit',
				'r.tot_paid',
				'r.coupon_str',
				'r.discount_val',
				'r.tip_amount',
			)));

		$q->select($dbo->qn('p.id', 'id_assoc'));
		$q->select($dbo->qn('p.id_product'));
		$q->select($dbo->qn('p.id_product_option'));
		$q->select($dbo->qn('p.name', 'prod_name'));
		$q->select($dbo->qn('p.quantity', 'prod_quantity'));
		$q->select($dbo->qn('p.price', 'prod_price'));
		$q->select($dbo->qn('p.notes', 'prod_notes'));

		$q->from($dbo->qn('#__vikrestaurants_reservation', 'r'));
		$q->leftjoin($dbo->qn('#__vikrestaurants_res_prod_assoc', 'p') . ' ON ' . $dbo->qn('r.id') . ' = ' . $dbo->qn('p.id_reservation'));

		$q->where($dbo->qn('r.id') . ' = ' . $ids[0]);

		$q->order($dbo->qn('p.id') . ' ASC');

		$dbo->setQuery($q);
		$dbo->execute();
		
		if ($dbo->getNumRows() == 0)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
			$app->redirect('index.php?option=com_vikrestaurants&view=reservations');
			exit;
		}

		$app = $dbo->loadObjectList();

		$bill = new stdClass;
		$bill->id       = $app[0]->id;
		$bill->closed   = $app[0]->bill_closed;
		$bill->value    = $app[0]->bill_value;
		$bill->deposit  = $app[0]->deposit;
		$bill->paid     = $app[0]->tot_paid;
		$bill->discount = $app[0]->discount_val;
		$bill->tip      = $app[0]->tip_amount;

		if ($app[0]->coupon_str)
		{
			list($code, $amount, $type) = explode(';;', $app[0]->coupon_str);

			$bill->coupon = new stdClass;
			$bill->coupon->code   = $code;
			$bill->coupon->amount = $amount;
			$bill->coupon->type   = $type;
		}
		else
		{
			$bill->coupon = null;	
		}

		$bill->products = array();

		foreach ($app as $p)
		{
			if ($p->id_product)
			{
				$prod = new stdClass;
				$prod->id        = $p->id_product;
				$prod->id_option = $p->id_product_option;
				$prod->id_assoc  = $p->id_assoc;
				$prod->name      = $p->prod_name;
				$prod->quantity  = $p->prod_quantity;
				$prod->price     = $p->prod_price;
				$prod->notes     = $p->prod_notes;

				$bill->products[] = $prod;
			}
		}

		// get all products

		$products = array();

		$q = $dbo->getQuery(true)
			->select('p.*')
			->select($dbo->qn('o.id', 'option_id'))
			->select($dbo->qn('o.name', 'option_name'))
			->select($dbo->qn('o.inc_price', 'option_price'))
			->from($dbo->qn('#__vikrestaurants_section_product', 'p'))
			->leftjoin($dbo->qn('#__vikrestaurants_section_product_option', 'o') . ' ON ' . $dbo->qn('p.id') . ' = ' . $dbo->qn('o.id_product'))
			->order(array(
				$dbo->qn('p.hidden') . ' ASC',
				$dbo->qn('p.ordering') . ' ASC',
				$dbo->qn('o.ordering') . ' ASC',
			));

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			foreach ($dbo->loadObjectList() as $p)
			{
				if (!isset($products[$p->id]))
				{
					$p->options = array();

					$products[$p->id] = $p;
				}

				if ($p->option_id)
				{
					$option = new stdClass;
					$option->id    = $p->option_id;
					$option->name  = $p->option_name;
					$option->price = $p->option_price;

					$products[$p->id]->options[] = $option;
				}
			}
		}

		$products = array_values($products);

		// get all coupons

		$coupons = array();

		$q = $dbo->getQuery(true)
			->select('*')
			->from($dbo->qn('#__vikrestaurants_coupons'))
			->where($dbo->qn('group') . ' = 0');

		$dbo->setQuery($q);
		$dbo->execute();

		if ($dbo->getNumRows())
		{
			$coupons = $dbo->loadObjectList();
		}
		
		$this->bill       = &$bill;
		$this->products   = &$products;
		$this->coupons    = &$coupons;
		$this->returnTask = $input->get('from', null);

		// display the template
		parent::display($tpl);
	}

	/**
	 * Setting the toolbar.
	 *
	 * @return 	void
	 */
	private function addToolBar()
	{
		JToolbarHelper::title(JText::_('VRMAINTITLEEDITBILL'), 'vikrestaurants');
		
		if (JFactory::getUser()->authorise('core.edit', 'com_vikrestaurants'))
		{
			JToolbarHelper::apply('reservation.savebill', JText::_('VRSAVE'));
			JToolbarHelper::save('reservation.saveclosebill', JText::_('VRSAVEANDCLOSE'));
			JToolbarHelper::divider();
		}
		
		JToolbarHelper::cancel('reservation.cancel', JText::_('VRCANCEL'));
	}
}
