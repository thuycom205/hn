<?php

class HN_Catalogbestseller_Helper_Data extends Mage_Core_Helper_Abstract
{
	/***
	 * description: send the email to warehouse
	 */
	public function sendMail($checkout, $text_email) {
		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);

		$mailTemplate = Mage::getModel('core/email_template');
		/* @var $mailTemplate Mage_Core_Model_Email_Template */
		$template = "checkout_payment_success_template";
		$sender = array('email'=>'kontakt@play2day.dk','name'=>'Play2day I/SDenmark');
		$recipient['email'] = $checkout->getCustomerEmail();
		$recipient['name']= $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname();
		$mailTemplate->setTemplateSubject("Information about game licenses");
		$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$checkout->getStoreId()))
		->sendTransactional(
		$template,
		$sender,
		$recipient['email'],
		$recipient['name'],
		array(
          'customer' => $checkout->getCustomerFirstname() . ' ' . $checkout->getCustomerLastname(),
          'textmail' => $text_email     
		)
		);
		$translate->setTranslateInline(false);
		return $this;

	}

	/**
	 * send winning email
	 */
	public function sendWinMail($recipient_email, $recipient_name, $text_email) {
		$translate = Mage::getSingleton('core/translate');
		/* @var $translate Mage_Core_Model_Translate */
		$translate->setTranslateInline(false);

		$mailTemplate = Mage::getModel('core/email_template');
		/* @var $mailTemplate Mage_Core_Model_Email_Template */
		$template = "member_experience_win_template";
		$sender = array('email'=>'sales@coopown.com','name'=>'Coopown');
		$recipient['email'] = $recipient_email;
		$recipient['name']= $recipient_name;
		$mailTemplate->setTemplateSubject("Congratulation!You won the vote");
		$mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
		->sendTransactional(
		$template,
		$sender,
		$recipient['email'],
		$recipient['name'],
		array(
          'customer' => $recipient_name,
          'textmail' => $text_email     
		)
		);
		$translate->setTranslateInline(false);

		return $this;

	}

	
	
	
	/**
	 * description get order information
	 * select * from where order_id
	 */
	public function getOrderInfo($order_id) {
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$orderTable = $tPrefix."sales_flat_order_item";
		$query = "select * from `$orderTable` where `entity_id` =$order_id";
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) == 0) {
			exit("the order id  is not correct");
		}
		return $rows[0];
		
		$products = Mage::getModel('catalog/category')->load($category_id)
->getProductCollection()
->addAttributeToSelect('*')
->addAttributeToFilter(
    'status',
    array('eq' => Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
);
		
	}

	
	
	/**
	* compare all possibility to the database
	* ups _ dhl - usps
	* ups _dhl _usps
	*/
	public function compareToDb($possiblity_array) {
		$query = "SELECT * FROM `shipmethod` WHERE `enable` = ` AND  `flat` = $flat AND `table` = $table AND
		`free` = $free AND `dhl` = '$dhl' AND `fedex` = '$fedex' AND `ups`='$ups' AND `usps`='$usps'";
	}

	public function extractcombinerate($name) {
		$out = '';
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();

		//query
		$shipmethodTbl = $tPrefix. "shipmethod";
		$query = "SELECT * FROM `$shipmethodTbl` WHERE `name` = '$name'";
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) > 0) {
			if ($rows[0]['flat'] == 1)  {
				$out .= 'flatrate_flatrate';
					
			} //end if

			if ($rows[0]['table']==1) {
				$out .= "tablerate_tablerate";
			}
			if ($rows[0]['ups'] != '' && $rows[0]['ups'] != 0) {
				$out .= "ups_" .$rows[0]['ups'];
			}

			if ($rows[0]['fedex'] != '' && $rows[0]['fedex'] != 0) {
				$out .= "fedex_" .$rows[0]['fedex'];
			}
			if ($rows[0]['dhl'] != '' && $rows[0]['dhl'] != 0) {
				$out .= "dhl_" .$rows[0]['dhl'];
			}
		}
	}

	/***
	 *
	 *
	 * Case 1: if the customer buy items from two warehouses. product A from New York and product B from California warehouse.
	 *All the option for shipping service
	 *   Warehouse New York (Product A)
	 *   UPS
	 *       Next Day Air
	 *       Next Day Air Letter
	 *       Ground
	 *   Flatrate
	 *
	 *   Warehouse California (Product B)
	 *
	 *    Flatrate
	 *    Fedex
	 *        Ground
	 *    and in the admin , we define a Ground  as a combined rate shipping and it is combination of UPS Ground and Fedex Ground
	 *    So What is the result will show for the customer
	 *
	 *     We will show these option
	 *
	 *     FlatRate
	 *     Ground (Combination of UPS Ground and Fedex Ground and do not show the other option as UPS next day air and fedex ground  etc)
	 *     Case 2:
	 *        *   UPS
	 *       Next Day Air
	 *       Next Day Air Letter
	 *       Ground
	 *   Flatrate
	 *
	 *   Warehouse California (Product B)
	 *
	 *    Flatrate
	 *    Fedex
	 *        Ground
	 *
	 *    UPS
	 *     Ground
	 *
	 *     In this case .
	 *     Which is better  ?
	 *
	 *     1. FlatRate
	 *     2
	 *      UPS
	 *        Ground (combine of Ground UPS in two warehouse)
	 *     3
	 *      Ground     (combine of Ground UPS and Ground Fedex)
	 *
	 *    Or we will show only two option
	 *    1.FlatRate
	 *    2.UPS Ground
	 *
	 * ny (flatrate, table);  falt-rate, ups-gnd, ; flat_ups-12d,fa co bao nhieu array con co 2* 3 = 6; temp[1]; temp[2]; temp[3]; temp[4]; temp[5]]; temp[6]; temp1
	 * nebraska (ups_gnd, ups_12D,kk)
	 * jjj(flatrate, usp, kk);
	 * flatrate_ups_gnd, ups_ground,
	 * ny_flatrate;nebras_ups_gnd;jj_flatrate;  and ny_flatrate;nebraskas_ups_gnd;
	 *
	 * [flatrate,ups_gnd,flatrate],[flatrate_upsnd,upsgrond];
	 *  2 * 2 * 3 =12 combination;
	 *  each combine 3 element;
	 * tem[1]
	 * $out = array();
	 * foreach ($min as s) {
	 * $temp = array();
	 * $temp[1][] = s;
	 *     foreach ($all as $warehouse==>shipp sevice arr) {
	 *           foreach (ship service as ship_service ) {
	 *           $temp[] = $ship_sevice;
	 *     }
	 * }
	 *
	 * more clear code;
	 * foreach ($j = 0; $j < $all_case ; $j++) {
	 *  echo $j; (1,2,3)
	 * 	  for ($i = 0; $i < count(); $i++) {
	 *         //$i = 1, 2;
	 *         $index = $j * $i ; //
	 *          temp[1][1] = flatrate;
	 *          temp[2][1] = table;
	 *
	 *         temp[1][2] = ups_gnd;
	 *         tem[2][2] = table;
	 *
	 *
	 *         }
	 * }
	 *
	 *
	 *
	 *
	 */
	public function newshowcombinedRate() {
		$highest_priority = array();
		$warehouseArr = $this->getAllWarehouses();
		$all_ship_rate = array();
		$array_cb_warehousename_rate = array();

		$c_arr = array();

		foreach ($warehouseArr as $warehouse_title) {
			$weight =  $this->getWeightByWarehouse($warehouse_title);
			$all_ship_rate[$warehouse_title]  = $this->getShippingMethodPerWarehouse($weight, $warehouse_title);
		}

		for ($i = 0; $i < count($all_ship_rate) ; $i++) {

		}
		$result_out = array();
		$result_out['unique'] = $c_arr;  //Array([0]=> [flatrate_flatrate, ups_ground], [1]=>['flatrate', fedex_xx]);
		$result_out['w_r'] = $array_cb_warehousename_rate; // Array([0] => [ny:flatrateflatrate;cl:ups_ground), [1] =>[ny:ups_1dn,];
		return $result_out; //[flatrate_flatrate, ups_ground, fedex_ground][ups_gnd, flatrate_flatrate]
	}

	public function algo() {

		$warehouseArr = $this->getAllWarehouses();
		$all_ship_rate = array();
		$array_cb_warehousename_rate = array();

		$c_arr = array();

		foreach ($warehouseArr as $warehouse_title) {
			$weight =  $this->getWeightByWarehouse($warehouse_title);
			$all_ship_rate[$warehouse_title]  = $this->getShippingMethodPerWarehouse($weight, $warehouse_title);
		}

		/////////////////
		//			$s = array();
		//			$s[] = 'dhl_xx';
		//			$s[] = 'dhl_yy';
		//			$k = array();
		//			$k['demowarehouse'] = $s;
		//			$all_ship_rate['demowarehouse'] = $s;

		///////////
		$sub_com = array();
		$sub_com_with_warehouseTit = array();
		//$temp_sub_com = array();
		$warehouse_count = 0;
		foreach ($all_ship_rate as $wareshouse_title=>$arr_ship_per_warehouse) {
			$warehouse_count++;
			if ($warehouse_count == 2)  $arr_ship_per_warehouse[] =
			$temp_sub_com = array();
			$temp_sub_comTit = array();
			foreach ($arr_ship_per_warehouse as $ship_service)  	{
				if ($warehouse_count == 1) {
					$sub_com[] = $ship_service; //
					$sub_com_with_warehouseTit[] = $wareshouse_title . ":".$ship_service;
						
				}
				else { /** sub da ton tai mot phan tu nao do  */
					//$temp_arr = array();/
					for ($j = 0; $j < count($sub_com); $j++) {
						$temp_arr = array();
						$tem_arr_with_warehousetTit = array();
						if (count((array)$ship_service) > 0) {
								
							$temp_arr = array_merge((array)$ship_service,(array)$sub_com[$j] ); //f_u

							$warehouseTit_ss = $wareshouse_title . ":".$ship_service;
							$tem_arr_with_warehousetTit = array_merge((array)$warehouseTit_ss,(array)$sub_com_with_warehouseTit[$j] ); //f_u
							if (count($temp_arr) >= $warehouse_count) {
								$temp_sub_com[] = $temp_arr;
							}
							if (count($tem_arr_with_warehousetTit) >= $warehouse_count) {
								$temp_sub_comTit[] = $tem_arr_with_warehousetTit;
							}
						}
					}
				}//end else
					
			} //end of foreach( $arr_ship_per
			if (isset($temp_sub_com)) {
				if (count($temp_sub_com) > 0)
				$sub_com = $temp_sub_com;
			}
				
			if (isset($temp_sub_comTit)) {
				if (count($temp_sub_comTit) > 0)
				$sub_com_with_warehouseTit = $temp_sub_comTit;
			}
		}


		$out = array();

		foreach ($sub_com as $sub_arr) {
			if (count($sub_arr) == $warehouse_count )  $out['unique'][] =$sub_arr;
		}

		foreach ($sub_com_with_warehouseTit as $sub_arrTit) {
			if (count($sub_arrTit) == $warehouse_count )  $out['tit'][] =$sub_arrTit;
		}
		//$out['unique'] = $sub_com;
		//print_r($out);
		return $out;
		/**
		 * 6; tao ra 6 array; w_co =1; tem[i] = this[i]; combine[] =
		 * 2; lay 6 array *2 tao ra 12 array  w_co = 2;
		 * 3: lay 12 array * 3 tao ra 36 array;
		 */
	} //end function
	//1,2
	//123456 1

	//for () ;

	/**
	 * extract the unique element from an array
	 */
	public function toUnique($inputArr) {
		$out = array();
		foreach ($inputArr as $element) {
			if (!in_array($element, $out) ) $out[] = $element;
		}
		return $out;
	} //end of function

	public function getNameCombinefromdb($ArrIn /**ups_gnd, fedex_1dm, dhl_rr */) {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$shipMethod = $tPrefix.'shipmethod' ;

		$flat  = 0;
		$free  = 0;
		$table = 0;
		$ups   = 0;
		$usps  = 0;
		$fedex = 0;
		$dhl   = 0;
		//
		$flat_value  = '';
		$free_value  = '';
		$table_value = '';
		$ups_value   = '';
		$fedex_value = '';
		$usps_value  = '';
		$dhl_value   = '';

		foreach ($ArrIn as $shipping_service /** shipping_service flatrate_flatrate*/) {
			$temp = explode('_', $shipping_service);
			if ($temp[1] && $temp[0]) {
				if ($temp[0] == 'flatrate') { $flat_value = 1; $flat = 1; }
				//free
				elseif ($temp[0] == 'free') { $free_value = 1; $free = 1; }
				//table
				elseif ($temp[0] == 'table') { $table_value = 1; $table = 1; }
				//ups
				elseif ($temp[0] == 'ups') { $ups_value = $temp[1]; $ups = 1;}
				//fedex
				elseif ($temp[0] == 'fedex') { $fedex_value = $temp[1]; $fedex = 1;}
				//usps
				elseif ($temp[0] == 'usps') { $usps_value = $temp[1]; $usps = 1;}
				//dhl
				elseif ($temp[0] == 'dhl') { $dhl_value = $temp[1]; $dhl = 1;}
			}
		}
		$query = "";
		$query = "SELECT * FROM `shipmethod` WHERE `enable` = 1";
		if ($flat == 1) $query .= " AND `flat` = $flat_value ";
		if ($free == 1) $query .=" AND `free` = $free_value";
		if ($table == 1) $query .= " AND `table` = $table_value";
		if ($ups == 1) $query .= " AND `ups` = '$ups_value'";
		if ($usps ==1) $query .= " AND `usps` = '$usps_value'";
		if ($dhl == 1) $query .= " AND `dhl` = '$dhl_value'";
		$rs = $db->query($query);

		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) > 0) {
			return $rows[0]['name'];
		} //end if count
		else {
			return false;
		}


	} //end of method

	/***
	 * call by Carrier instance
	 *
	 */
	public function getOrderIdByTrackingNumber($trackingNumber) {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$trackingTable = $tPrefix. 'sales_flat_shipment_track';

		$query = "select * from `$trackingTable` where `track_number` = '$trackingNumber'";
		//hn debug
		//Mage::log("query ". $query , Zend_Log::INFO);
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) > 0) {
			return $rows[0]['order_id'];
		} else {
			return false;
		}
	}

	/**
	 * get warehouse by OrderId--->get credentials while getTrackingNumber
	 */
	public function getWarehouseByOrderId($orderId) {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$orderTable = $tPrefix. 'sales_flat_order';

		$query = "select * from `$orderTable` where `entity_id` = $orderId";
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) > 0) {
			return $rows[0]['warehouse'];
		} else {
			return false;
		}
	}
	/***
	 * architect
	 * INSERT INTO `freshone`.`sub_order` (`id`, `entity_id`, `warehouse`, `shipping_method`, `qty_shipping`) VALUES (NULL, '2', 'Alabama', 'free_free', '3');
	 * dropship-->carrier
	 * price:
	 * make ship --> get tracking number-->
	 *
	 */
	public function saveSubOrder($orderId,$warehouse,$shipping_method,$shipping_description, $price,$qty) {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$subOrderTbl = $tPrefix. "sub_order";
		$query = "INSERT INTO `". $subOrderTbl. "` (`entity_id`,`warehouse`, `shipping_method`,`shipping_description`, `price`,`qty_shipping`) VALUES (".$orderId.", '".$warehouse."', '".$shipping_method."', '". $shipping_description ."', '" . $price. "', '". $qty."')";
		Mage::log($query, null, "dropship.txt", true);
		$db->query($query);
	}

	/**
	 * save the sub_order_item for the sake of creating Label
	 */
	public function saveSubOrderItem($orderId, $warehouse, $shipping_method, $productid) {
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$subOrderTbl = $tPrefix. "sub_order_item";
        
		$query = "INSERT INTO `". $subOrderTbl. "` (`order_id`,`warehouse`, `shipping_method`, `product_id`) VALUES (".$orderId. ", '". $warehouse ."','" .$shipping_method."',  ".$productid.")";
	    Mage::log("=====================", null, "dropship.txt", true);
        Mage::log($query, null, "dropship.txt", true);
		$db->query($query);
	}
	
   /**
   * Matching the productId 
   * to itemId in the shopping cart
   */
    public function getItemIdFromProductId($productid) {
        foreach (Mage::getSingleton('checkout/cart')->getQuote()->getAllItems() as $item) {
			//echo $item->getId();
			//$item->getProductId();
			if($item->getProductId() == $productId) {
				return 	$item->getId();
			}
		}
		return null;
    }

    /**
    * return array of shipmentid
    */
      public function getWarehouseByShipmentId($shipmentid) {
         $db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$subOrderTbl = $tPrefix. "sales_flat_shipment";
        $orderid = 0;
        $query = "select `order_id` from `$subOrderTbl` where `entity_id` = $shipmentid";
        $rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
           $orderid =  $rows[0]['order_id'] ;
         }
         $sub_orderTbl = $tPrefix."sub_order";
         $query = "select `warehouse` from `$sub_orderTbl` where `entity_id` =  $orderid"; 
         $rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
           return $rows;
         }
        return false;
       }
     /**
     * description : to give array of id of table order_flat_sales_order
     */
       
      public function getProducInOrderByWarehouse($warehouse, $order_id) {
         $db = Mage::getSingleton('core/resource')->getConnection('core_write');
         $itemsArr = array();
         $tPrefix = (string) Mage::getConfig()->getTablePrefix();
		 $subOrderTbl = $tPrefix. "sub_order_item";
         $query = "select * from `$subOrderTbl` where `order_id` = $order_id and `warehouse` =  '". $warehouse ."'";
         $rs = $db->query($query);
		 $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
         if (!empty($rows)) {
         	 foreach ($rows as $item_in_sub_order) {
         	 $order_id = $item_in_sub_order['order_id'];
         	 $product_id = 	$item_in_sub_order['product_id'];
			 $order_sales_itemTbl = $tPrefix. 'sales_flat_order_item';
             $query = "select * from `$order_sales_itemTbl` where `order_id` = $order_id and `product_id`= $product_id";
             $rs = $db->query($query);
             $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
             if (!empty($rows)) {
             	//var_dump($rows);
             	return $rows;
             }
         	 }
          }
      }
}