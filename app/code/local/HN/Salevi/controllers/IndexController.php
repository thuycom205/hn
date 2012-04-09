<?php
class HN_Catalogbestseller_IndexController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		if ($this->isLogin() ) {
			/// co 3 truong hop
			// 1.Chua login thi cho ra 2 option : search . 2. Tao ra mot registry list( luu mot session va redirect toi trang tao account)
			// 2. Da login thi 1. Tao ra mot registry list
			//3. Da login va da tao ra mot registry list thi show ra registry list

			$customer = $this->_getSession()->getCustomer();
			$cusomer_id = $customer->getId();
			if ($cusomer_id == null || $cusomer_id == "") {
				echo "Only login member can post user experience !";
				exit();
			}
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$query = 'select `entity_id` from `'.$table_prefix.'sales_flat_order` where `customer_id` ='.$cusomer_id;
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');

			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			$num_rows = count($rows);
			if ($num_rows > 0) {
				$Arr_boughtItem = array();
				for ( $i = 0; $i < $num_rows; $i++ ) {
					//echo $rows[$i]['entity_id'];
					$queryitem = 'select * from `sales_flat_order_item` where `order_id` ='.$rows[$i]['entity_id'];

					$rs = $db->query($queryitem);
					$boughtItems = $rs->fetchAll(PDO::FETCH_ASSOC);
					for ($j = 0; $j < count($boughtItems) ; $j++) {
						if ( !in_array($boughtItems[$j]['product_id'], $Arr_boughtItem)) {
							$Arr_boughtItem[] = $boughtItems[$j]['product_id'];
						}
					}


				}
			}

			Mage::register('boughitems', $Arr_boughtItem);
			$query = "select * from `dealship` where `userid`= '$cusomer_id'";
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			Mage::register('myexp', $rows);
		}

		$this->loadLayout();

		$this->renderLayout();
	}

	/**
	 * @author : Luu Thanh Thuy
	 * Enter description here ...
	 */

	public function postAction() {
		if ($this->isLogin() ) {
			$productid = $this->getRequest()->getParam('productid');
			Mage::register('productid', $productid);
		}
		$this->loadLayout();
		$this->renderLayout();
	}

	public function submitAction() {
		$img='';
		$msg ='';
		if (isset($_FILES['image']['name'])) {

			$path = Mage::getBaseDir('media').DS.'catalog';

			try {
				/* Starting upload */
				$uploader = new Varien_File_Uploader('image');

				// Any extention would work
				$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
				$uploader->setAllowRenameFiles(false);

				// Set the file upload mode
				// false -> get the file directly in the specified folder
				// true -> get the file in the product like folders
				//	(file.jpg will go in something like /media/f/i/file.jpg)
				$uploader->setFilesDispersion(false);

				// We set media as the upload dir
				$path = Mage::getBaseDir('media').DS.'catalog';
				$uploader->save($path, $_FILES['image']['name']);
			} catch (Exception $e) {
				//thuydebug
				Mage::log($e->getMessage(), Zend_Log::ERR);
				//thuydebug
			}
			move_uploaded_file($_FILES['image']['tmp_name'],$path.$_FILES['image']['name']);
			$img = Mage::getBaseUrl('media').'catalog/'.$_FILES['image']['name'];
		} else {
			Mage::getSingleton('catalog/session')->addError('please provide a photo of the deal');
			exit();
		}
		//echo "thank you for posting experience";
		$customer = $this->_getSession()->getCustomer();
		$cusomer_id = $customer->getId();
		$productid = $this->getRequest()->getParam('productid');
		$summary = $this->getRequest()->getParam('summary');
		$content = $this->getRequest()->getParam('content');
		$title = $this->getRequest()->getParam('title');
		$rate = $this->getRequest()->getParam('rate');
		$now = date("Y-m-d H:i:s");
		//thuydebug
		Mage::log('customer id'.$cusomer_id, Zend_Log::ERR);
		//$newDate = strtotime('+15 days',$now);
		if ($cusomer_id == null && $cusomer_id == '') {
			$msg = "pls login to post member experience !";
			Mage::register('msg', $msg);
			//thuydebug
			Mage::log($msg, Zend_Log::ERR);
		 Mage::getSingleton('catalog/session')->addError('please login to post experience');

			exit($msg);
		}

		//
		$customer = $this->_getSession()->getCustomer();
		$customerid = $customer->getId();
		if ($customerid != '')  {
			$query = "select * from `dealship` where `userid`= '$customerid'";
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			for ($i = 0; $i < count($rows); $i++) {
				if ( $productid == $rows[$i] ['product_id'] ) {
					$msg = "You already post experience for this deals!";
					Mage::register('msg', $msg);

					//thuydebug
			  Mage::log($msg, Zend_Log::ERR);
			  Mage::getSingleton('catalog/session')->addError($msg);

			  exit($msg);
				}
			}
		}
		//
		if ($productid != null &&  $content != null &&  $title != null) {

			$query = "insert into `dealship` (`product_id`, `userid` , `title` , `summary`, `description`, `rate` , `created_time` ,`exp_time`, `img`)
		         VALUES ('$productid', '$cusomer_id', '$title' , '$summary', '$content', '$rate','$now', null , '$img') ";
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$rs = $db->query($query);
			$msg = "Thank you for posting experience!";
			Mage::register('msg', $msg);
			Mage::getSingleton('catalog/session')->addNotice($msg);
			Mage::log($msg, Zend_Log::ERR);
			$url = Mage::getBaseUrl(). 'dropship/index' ;
			$this->getResponse()->setRedirect($url);
		} else {
			$msg = 	'please fill all the require field'	;
			Mage::getSingleton('catalog/session')->addError($msg);
			$url = Mage::getBaseUrl(). 'dropship/index' ;
			$this->getResponse()->setRedirect($url);
		}
	}

	/***
	 * Vote
	 */
	public function voteAction() {
		if ($this->isLogin() ) {
			$query = "select * from `dealship`";
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			Mage::register("allpost", $rows);
		}
		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * View post detail
	 * Enter description here ...
	 */
	public function postdetailAction() {
		$id = $this->getRequest()->getParam('id');
		if ($id != null && $id != '') {
			$query = "select * from `dealship` where `id`=$id";
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);

			$customer = $this->_getSession()->getCustomer();
			$cusomer_id = $customer->getId();
			Mage::register('customerid', $cusomer_id);
			Mage::register("post", $rows);
			$this->loadLayout();
			$this->renderLayout();
		}
	}

	public function submitvoteAction() {
			
		$customerid= $this->getRequest()->getParam('customer_id');
		$product_id = $this->getRequest()->getParam('product_id');
		$exp_id = $this->getRequest()->getParam('id');
			
		$alreadyVote = $this->alreadyVote($product_id, $customerid);
		if ($alreadyVote) {
			Mage::register('alreadyvote', "1");

		} else {
			if ($customerid != '' && $exp_id != '' && $product_id != '') {
					
				$query = 'insert into `vstore` (customer_id , exp_id, product_id) values ('.$customerid.','. $exp_id .','. $product_id .')';
				//echo $query;
				$db = Mage::getSingleton('core/resource')->getConnection('core_write');
				$table_prefix = Mage::getConfig()->getTablePrefix();
				$db->query($query);

				$query = 'select * from dealship where id ='. $exp_id;
					
					
				$rs = $db->query($query);
				$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
				$number_vote = $rows[0]['number_vote'];
					
				$number_vote = $number_vote + 1;
				$query = "update `dealship` set `number_vote`= ".$number_vote. " where `id` = ". $exp_id;
				$db->query($query);

			}
		}

		//$this->loadLayout();
		//$this->renderLayout();
		$msg = "Thank you for voting !";
		Mage::getSingleton('catalog/session')->addSuccess($msg);
		$url = Mage::getBaseUrl();
		$this->getResponse()->setRedirect($url);
	}


	/**
	 * @author Luu Thanh Thuy
	 * luuthuy205@gmail.com
	 */
	public function complainAction() {
		$customer = $this->_getSession()->getCustomer();
		$cusomer_id = $customer->getId();
		$productid = $this->getRequest()->getParam('productid');

		$query = "insert into `complain` (`product_id`, `userid` , `title` ,  `description`,  `created_time` )
		         VALUES ('$productid', '$cusomer_id', '$title' , '$content', '$now' ) ";
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$table_prefix = Mage::getConfig()->getTablePrefix();
		$rs = $db->query($query);
		$msg = "Thank you for posting your experiece. We will send it to supplier !";
		Mage::getSingleton('catalog/session')->addNotice($msg);
		$url = Mage::getBaseUrl();
		$this->_redirect($url);

	}
	public function resultAction() {
		$event_bridefn     = $this->getRequest()->getParam('event_bridefn');
		$event_brideln     = $this->getRequest()->getParam('event_brideln');
		$event_groomfn     = $this->getRequest()->getParam('event_groomfn');
		$event_groomln     = $this->getRequest()->getParam('event_groomln');
		$result_block = $this->getLayout()->createBlock('bridalregistry/result');

		$this->loadLayout()->getLayout()->getBlock('content')->append($result_block);
		$query = "select * from `event`  where `event_bridefn` like'". $event_bridefn."%' or `event_brideln` like '".$event_brideln."%' or `event_groomfn` like '".$event_groomfn."%' or `event_groomln` like '".$event_groomln."%'";
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);

		Mage::register('raw', $rows);
		$this->renderLayout();
	}


	public function view() {
		$product = Mage::getSingleton('catalog/product');
	}

	/**
	 * update the quatity of the item in registry list
	 */
	public function updateItemAction() {
		$params = $this->getRequest()->getParams();
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		//for ($i = 0; $i < count($params); $i++) {
		echo count($params['product_item_']);
		foreach ($params['product_item_'] as $key=>$value) {


			$query = 'update `item` set `would_love`='.$value['qty']. ' where `product_id`='.$key.' and `event_id`='.$_SESSION['event_id'];
			echo $query;
			$db->query($query);
		}
			
		//$this->getResponse()->setRedirect(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK).'bridalregistry/index');
		//}
	}

	/**
	 * delete an item from item table
	 */
	public function deleteAction() {
		$id = $this->getRequest()->getParam('id');
		if($id!=null && $id != '') {
			$query = 'delete from `item` where `product_id`='.$id;
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$db->query($query);
		}
		$this->getResponse()->setRedirect($baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK).'bridalregistry/index');

	}

	public function tellfriendAction() {
		$event_id = $_SESSION['event_id'];

		/*get the friends list*/
		$this->loadLayout();
		$this->renderLayout();
		$sendto = $this->getRequest()->getParam('sendto');
		$mailcontent = $this->getRequest()->getParam('mailcontent');
		$sendto = "";

	}

	/**
	 * get the Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}

	/**
	 * whether the customer login or not
	 *
	 */
	public function  isLogin() {
			
		if ($this->_getSession()->isLoggedIn()) {

			return TRUE;
		}
		return false;
	}

	/**
	 * @author Luu Thanh Thuy luuthuy205@gmail.com
	 */
	public function alreadyVote($productid, $customer_id) {
		if ($customer_id != null && $customer_id != '' && $productid != null && $productid != '' ) {
			$query = "select * from `vstore` where `product_id`=".$productid .' and `customer_id` ='. $customer_id;
			//echo $query;
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			//$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			if ( count($rows) > 0)
			{
				return true;
			} else {
				return  false;
			}
		}
		return true;
	}

	/**
	 * public function my ex
	 */
	public function myexpAction() {
		$customer = $this->_getSession()->getCustomer();
		$customerid = $customer->getId();
		if ($customerid != '')  {
			$query = "select * from `dealship` where `userid`= '$customerid'";
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			Mage::register('myexp', $rows);
			$this->loadLayout();
			$this->renderLayout();
		}
	}

	/**
	 * public function complain
	 */
	public function haveComplain($customerid, $productid) {
		$query = "select *  from `complain` where `userid` =".$customerid." and `product_id` =".$productid;
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$table_prefix = Mage::getConfig()->getTablePrefix();
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) > 0) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * return true if already submit and return false if not yet
	 */
	public function havePostExp($customerid, $productid) {
		$query = "select *  from `dealship` where `userid` =".$customerid." and `product_id` =".$productid;
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$table_prefix = Mage::getConfig()->getTablePrefix();
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) > 0) {
			return true;    
		} else {
			return false;
		}

	}

	/**
	 * compare time 09/29/11
	 *
	 */
	public  function testAction() {
		echo now();
		$temp = getdate();
		print_r($temp);

		$exp = mktime(0, 0, 0, 2,29, 12);
		$exp_time = getdate($exp);
		echo "about expire time";
		print_r($exp_time);


		$e_year = $exp_time['year'] - $temp['year'];
		$e_date = $exp_time['yday'] - $temp['yday'];
		$remain_date = $e_year*365  + $e_date;
		$remain_mins = $exp_time['minutes'] - $temp['minutes'];
		$remain_seconds = $exp_time['seconds'] - $temp['seconds'];
		echo "remain date is ".$remain_date;
		$date = "04/30/1973";
		//print_r(explode('/', $date)) ;
		list($month, $day, $year) = explode('/', $date);
		echo "Month: $month; Day: $day; Year: $year<br />\n";
		echo "XXXXXXXXXXXXXXXXXXXXXXXXXXX";
		print_r ($this->calRemainDate('08/12/12'));
	}

	/**
	 * luuthuy205@gmail.com
	 */
	public function calRemainDate($exp_date) {
		list($month, $day, $year) = explode('/', $exp_date);
		$exp = mktime(0, 0, 0, $month,$day, $year);
		$exp_time = getdate($exp);
		$temp = getdate();

		$e_year = $exp_time['year'] - $temp['year'];
		$e_date = $exp_time['yday'] - $temp['yday'];
		
		$remain_date = $e_year*365  + $e_date;
		$remain_mins = 60 - $temp['minutes'];
		$remain_seconds = 60 - $temp['seconds'];
		$output = array('date'=>$remain_date, 'mins'=>$remain_mins, 'sec'=>$remain_seconds);
		return $output;
	}

	public function getExpireDate($productid) {
		if ($productid != null) {
			$query = "select * from `shipmanager` where `product_id` =".$productid;
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$table_prefix = Mage::getConfig()->getTablePrefix();
			$rs = $db->query($query);
			$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			return $rows[0]['exp_date'];
		}
	}
	//update new user experiece
	public function updateFirstExp() {

		$helper = Mage::helper('dropship');
		$product_ids = $helper->getLastedBuyPd();
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();
		$first_exp_table = $tPrefix."first_deal_exp";

		foreach ($product_ids as $pd) {
			$current_hit = $helper->getCurrentHit($pd);
			$current_hit = $current_hit + 1;
			$query = "insert into $first_exp_table(product_id, hit_number) values($pd,$current_hit)";
			$db->query($query);

		}
	}

	/**
	 *
	 */

	public function test1Action() {
		$att = Mage::getResourceModel('eav/entity_attribute_collection')
		->setCodeFilter(`warehouse`)
		->getFirstItem();
		echo  $att->getId();
		echo "unit Test"     ;
		echo $att->getFrontendLabel();
		$attributeId = Mage::getResourceModel('eav/entity_attribute')
		->getIdByCode('catalog_product','warehouse');
		echo 'attribute id is'. $attributeId;
		echo '.....................................';
		$attributeOptions = $att->getSource()->getAllOptions(false);
		print_r($attributeOptions);
	}

	/**
	 * test delete
	 *
	 */
	public function testdelAction() {
		$warehouse_name = 'test';
		$attributeId = Mage::getResourceModel('eav/entity_attribute')
		->getIdByCode('catalog_product','warehouse');
		$db = Mage::getSingleton('core/resource')->getConnection('core_write');
		$tPrefix = (string) Mage::getConfig()->getTablePrefix();


		$opionTbl = $tPrefix.'eav_attribute_option';
		$valueTbl = $tPrefix.'eav_attribute_option_value';

		$query = "select `option_id` from `eav_attribute_option` where `attribute_id`=$attributeId";
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		$arr_optionId = array();
		foreach ($rows as $row ) {
			$arr_optionId[] = $row['option_id'];
		}

		//select the option value
		// $query = "select `option_id` from `eav_attribute_option_value` where `value`=$warehouse_name";
		// $rs = $db->query($query);
		// $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
			
			
		$query = "SELECT * FROM `eav_attribute_option_value`, `eav_attribute_option` WHERE `eav_attribute_option_value`.`value` = '$warehouse_name' AND `eav_attribute_option_value`.`option_id` = `eav_attribute_option`.`option_id` AND `eav_attribute_option`.`attribute_id` = $attributeId";
			
		$rs = $db->query($query);
		$rows = $rs->fetchAll(PDO::FETCH_ASSOC);
		$option_id = $rows[0]['option_id'];
		$value_id = $rows[0]['value_id'];
			
		$del_query= "delete from `$valueTbl` where `value_id` = $value_id";
		$del_option_query = "delete from `$opionTbl` where `option_id` = $option_id";
		// $db->query($del_query);
		// $db->query($del_option_query);
	}
	/**
	 * testt
	 */
	public function testtAction() {
		$product = Mage::getModel('catalog/product')->load(1);
		echo $product->getName();
		echo $product->getData('warehouse');
		$warehouse = $product-> getResource()->getAttribute('warehouse')->getFrontend()->getValue($product);
		echo $warehouse;
	}
	public function abtestAction() {
		echo 	Mage::helper('dropship')->combine();
	}
	public function toAction() {
		echo Mage::getStoreConfig('dropship/general/enableddisplaycombinedrate');
		$data = Mage::helper('dropship')->showAllAvailabePossibility();
		print_r($data);
	}
	public function dAction() {
		//echo Mage::getStoreConfig('dropship/general/enableddisplaycombinedrate');
		$data = Mage::helper('dropship')->finalStep();
		print_r ($data);
	}
	
	/**
	 * validate the zipcode
	 */
	public function validateAction() {
		$postalCode = $this->getRequest() -> getParam('postcode');
		 $valid = Mage::helper('dropship')->validateZipcode($postalCode);
		 if ($valid) {
		 	echo "valid";
		 } else {
		 	echo "invalid";
		 }
	} //end of method
	
	/**
	 * @author <a href="mailto:luuthuy205@gmail.com" > Luu Thanh Thuy </a>
	 */
	public function configAction() {
		$carrierConfig = Mage::getStoreConfig('carriers/ups');
        var_dump($carrierConfig);
		
	}
	/**
	 * fedex
	 */
   public function fedexAction() {
		$carrierConfig = Mage::getStoreConfig('carriers/fedex');
        var_dump($carrierConfig);
		
	}
 public function dhlAction() {
		$carrierConfig = Mage::getStoreConfig('carriers/dhl');
        var_dump($carrierConfig);
		
	}
	
 public function upspAction() {
		$carrierConfig = Mage::getStoreConfig('carriers/usps');
        var_dump($carrierConfig);
		
	}
	
	public function sessionAction() {
		print_r($_SESSION['rate']);
		print_r($_SESSION['warehouse']);
	}
}
