<?php
class HN_Ajaxcart_IndexController extends Mage_Core_Controller_Front_Action {
	const XML_PATH_EMAIL_TEMPLATE   = 'sendfriend/email/template';
	public function indexAction() {
		echo 'My cart (' . $this->_getCart()->getItemsQty() . " item )";
	}
	protected function _getCart()
	{
		return Mage::getSingleton('checkout/cart');
	}


	/**
	 * Get checkout session model instance
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('checkout/session');
	}

	/**
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote()
	{
		return $this->_getCart()->getQuote();
	}

	/**
	 * Set back redirect url to response
	 *
	 * @return Mage_Checkout_CartController
	 */
	protected function _goBack()
	{
		if ($returnUrl = $this->getRequest()->getParam('return_url')) {
			// clear layout messages in case of external url redirect
			if ($this->_isUrlInternal($returnUrl)) {
				$this->_getSession()->getMessages(true);
			}
			$this->getResponse()->setRedirect($returnUrl);
		} elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
		&& !$this->getRequest()->getParam('in_cart')
		&& $backUrl = $this->_getRefererUrl()) {

			$this->getResponse()->setRedirect($backUrl);
		} else {
			if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
				$this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
			}
			$this->_redirect('checkout/cart');
		}
		return $this;
	}

	/**
	 * Initialize product instance from request data
	 *
	 * @return Mage_Catalog_Model_Product || false
	 */
	protected function _initProduct()
	{
		$productId = (int) $this->getRequest()->getParam('product');
		if ($productId) {
			$product = Mage::getModel('catalog/product')
			->setStoreId(Mage::app()->getStore()->getId())
			->load($productId);
			if ($product->getId()) {
				return $product;
			}
		}
		return false;
	}

	public function totalAction() {

		$cart = $this->_getCart();
		if ($cart->getQuote()->getItemsCount()) {
			$cart->init();
			$cart->save();

			if (!$this->_getQuote()->validateMinimumAmount()) {
				$warning = Mage::getStoreConfig('sales/minimum_order/description');
				$cart->getCheckoutSession()->addNotice($warning);
			}
		}

		foreach ($cart->getQuote()->getMessages() as $message) {
			if ($message) {
				$cart->getCheckoutSession()->addMessage($message);
			}
		}

		/**
		 * if customer enteres shopping cart we should mark quote
		 * as modified bc he can has checkout page in another window.
		 */
		$this->_getSession()->setCartWasUpdated(true);

		Varien_Profiler::start(__METHOD__ . 'cart_display');
		$this
		->loadLayout()
		->_initLayoutMessages('checkout/session')
		->_initLayoutMessages('catalog/session')
		->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
		$this->renderLayout();
		Varien_Profiler::stop(__METHOD__ . 'cart_display');
	}

	/**
	 * render side bar
	 */
	public function sidebarAction() {
		$this->loadLayout();

		$this->renderLayout();
	}

	/***************
	 * get the item in the grouped product
	 */

	public function groupAction() {
		$id = $this->getRequest()->getParam('product');
		
		$product =$this->_initProduct($id);
		Mage::register('product', $product);

		$this->loadLayout();
		$this->renderLayout();
	}


	public function configAction() {
		$id = $this->getRequest()->getParam('product');
		Mage::register('id', $id);

		$product =$this->_initProduct($id);
		Mage::register('product', $product);
		if ($product->isGrouped()) {
			$helper = Mage::helper('ajaxcart');
				
			//$linkArr = $helper->getchildsOfGroup($id);
				
			Mage::register('productid', $linkArr);

		}

		if ($product->isConfigurable()) {
				
			$helper = Mage::helper('ajaxcart');
				
			//$linkArr = $helper->getchildsOfConfig($id);
				
			//  Mage::register('productid', $linkArr);
		}
               // echo "test";

		$this->loadLayout();
		$this->renderLayout();
	}
public function configjsAction() {
		$id = $this->getRequest()->getParam('product');
		Mage::register('id', $id);

		$product =$this->_initProduct($id);
		Mage::register('product', $product);
		if ($product->isGrouped()) {
			$helper = Mage::helper('ajaxcart');
				
			//$linkArr = $helper->getchildsOfGroup($id);
				
			Mage::register('productid', $linkArr);

		}

		if ($product->isConfigurable()) {
				
			$helper = Mage::helper('ajaxcart');
				
			//$linkArr = $helper->getchildsOfConfig($id);
				
			//  Mage::register('productid', $linkArr);
		}
               // echo "test";

		$this->loadLayout();
		$this->renderLayout();
	}


	/**
	 * get the type of product
	 */
	public function typeAction() {
		if ($product = $this->_initProduct()) {
			//echo ($product->getTypeInstance(true)->hasOptions($product));
			if ($product->isConfigurable()) {
				echo "configurable";
			}
			if( $product->isGrouped()) {
				echo "grouped";
			}
		}
	}
}
