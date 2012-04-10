<?php
class HN_Ajaxcart_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getchildsOfGroup($id) {
	        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
			$tPrefix = (string) Mage::getConfig()->getTablePrefix();
			$cplTbl = $tPrefix . "catalog_product_link";
			$query = "SELECT `linked_product_id` FROM `$cplTbl` WHERE `link_type_id` = 3 AND `product_id` = $id";
			
			$rs = $db->query("SELECT `linked_product_id` FROM `catalog_product_link` WHERE `link_type_id` = 3 AND `product_id` = 54");
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$linkArr = array();
			foreach ($rows as $row) {
				$linkArr[] = $row['linked_product_id'];
			}
		return $linkArr;
	}
	
	public function getchildsOfConfig($id) {
		 $db = Mage::getSingleton('core/resource')->getConnection('core_read');
			$tPrefix = (string) Mage::getConfig()->getTablePrefix();
			$cplTbl = $tPrefix . "catalog_product_super_link";
			$query = 'SELECT `product_id` FROM `'.$cplTbl .'` WHERE `parent_id`='. $id;
			
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			$linkArr = array();
			foreach ($rows as $row) {
				$linkArr[] = $row['product_id'];
			}
		return $linkArr;
	}
}
