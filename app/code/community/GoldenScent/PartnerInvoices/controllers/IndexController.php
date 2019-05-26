<?php

class GoldenScent_PartnerInvoices_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
        $customerEmail = str_replace('.','_',$this->getRequest()->getParam('customer_email'));
        $partnerName = $this->getRequest()->getParam('partner_name');
        $partnerCookie = Mage::getModel('core/cookie')->get($customerEmail);
        if ($partnerCookie == "") {
            echo "Thanks for visiting the site. Happy Shopping !";
            Mage::getModel('core/cookie')->set($customerEmail, base64_encode($partnerName), 60 * 60 * 24 * 1);
        } 
    }
}
