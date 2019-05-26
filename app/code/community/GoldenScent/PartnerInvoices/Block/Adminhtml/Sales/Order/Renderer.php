<?php

/**
 * Adminhtml sales orders grid renderer
 *
 * @category   GoldenScent
 * @package    GoldenScent_PartnerInvoices
 */
class GoldenScent_PartnerInvoices_Block_Adminhtml_Sales_Order_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {

    public function render(Varien_Object $row) {
        $entityId = $row->getData($this->getColumn()->getIndex());
        $orders = Mage::getModel('sales/order')->load($entityId);
        return $orders->getPartnerName();
    }

}
