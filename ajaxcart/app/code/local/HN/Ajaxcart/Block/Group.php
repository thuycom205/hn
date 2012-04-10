<?php
include_once('Mage/Catalog/Block/Product/View/Abstract.php');
class HN_Ajaxcart_Block_Group extends  Mage_Catalog_Block_Product_View_Abstract {
public function _prepareLayout() {
      
		return parent::_prepareLayout();
	}
public function getAssociatedProducts()
    {
        return $this->getProduct()->getTypeInstance(true)
            ->getAssociatedProducts($this->getProduct());
    }


    /**
     * Set preconfigured values to grouped associated products
     *
     * @return Mage_Catalog_Block_Product_View_Type_Grouped
     */
    public function setPreconfiguredValue() {
        $configValues = $this->getProduct()->getPreconfiguredValues()->getSuperGroup();
        if (is_array($configValues)) {
            $associatedProducts = $this->getAssociatedProducts();
            foreach ($associatedProducts as $item) {
                if (isset($configValues[$item->getId()])) {
                    $item->setQty($configValues[$item->getId()]);
                }
            }
        }
        return $this;
    }
	
}