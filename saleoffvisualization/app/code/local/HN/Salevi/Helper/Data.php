<?php
class HN_Salevi_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getProductsWithSpecialPrice() {
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$eavTbl = $tPrefix. "eav_attribute";
		$productVarcharTbl = $tPrefix. "catalog_product_entity_decimal";
		$query = "select * from `$eavTbl` where `attribute_code` = 'special_price'";
		$query = "select * from `$orderTable` where `entity_id` =$order_id";
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) == 0) {
			exit("the order id  is not correct");
		}
		$att_id =  $rows[0]['attribute_id'];
		$query = "SELECT * FROM `$productVarcharTbl`  WHERE `attribute_id` = $att_id AND `value` IS NOT NULL";
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
	    if (count($rows) == 0) {
			exit("the order id  is not correct");
		}
		
		$productSaleOffColl = array();
		foreach ($rows as $row) {
			$ele = array();
			$product = Mage::getModel('catalog/product')->load($row['entity_id']);
			$special = $product->getSpecialPrice();
			$pricefrom = $product->getSpecialFromDate();
			$priceto = $product->getSpecialToDate();
			$price = $product->getPrice();
			echo $price;
			echo $special;
			echo $pricefrom;
			echo $pricefrom;
		}
	}
}