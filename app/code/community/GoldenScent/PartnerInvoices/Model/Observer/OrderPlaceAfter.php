<?php

class GoldenScent_PartnerInvoices_Model_Observer_OrderPlaceAfter {

    /**
     * Create Invoice and Shipments
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function execute(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();

        $orders = Mage::getModel('sales/order_invoice')->getCollection()
                ->addAttributeToFilter('order_id', array('eq' => $order->getId()));
        $orders->getSelect()->limit(1);
        $customerEmail = str_replace('.', '_', $order->getCustomerEmail());
        $partnerCookie = Mage::getModel('core/cookie')->get($customerEmail);
        if ((int) $orders->count() !== 0) {
            return $this;
        }

        if ($order->getState() == Mage_Sales_Model_Order::STATE_NEW && $partnerCookie != "") {

            $order->setPartnerName(base64_decode($partnerCookie))->save();
            try {

                foreach ($order->getAllItems() as $item) {
                    $item_id = $item->getItemId();
                    $qty = $item->getQtyOrdered();
                    $orderedItems[$item_id] = $qty;
                }
                if (is_array($orderedItems)) {
                    $itemsCount = count($orderedItems);
                    $splitArray = array();
                    if ($itemsCount == 1) {
                        $this->createInvoice($order, $orderedItems);
                    } else if ($itemsCount == 2) {
                        $splitArray = ["1", "1"];
                    } else if ($itemsCount / 2 == 0 && $itemsCount != 2) {
                        array_push($splitArray, floor($itemsCount / 2), floor($itemsCount / 2));
                    } else if ($itemsCount / 2 != 0) {
                        array_push($splitArray, floor($itemsCount / 2), floor($itemsCount / 2) + 1);
                    }
                    foreach ($splitArray as $val) {
                        $itemsArray = array_chunk($orderedItems, $val, true);
                    }
                }
                if ($itemsCount > 1) {
                    foreach ($itemsArray as $items) {
                        $this->createInvoice($order, $items);
                    }
                }
            } catch (Exception $e) {
                $order->addStatusHistoryComment('Exception occurred during creation of invoice/shipment action. Exception message: ' . $e->getMessage(), false);
                $order->save();
            }
        }

        return $this;
    }

    private function createInvoice($order, $items) {
        //START Handle Invoice
        try {
            if (!$order->canInvoice()) {
                $order->addStatusHistoryComment(' Order cannot be invoiced.', false);
                $order->save();
            }

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($items);

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();

            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $order->addStatusHistoryComment('Invoice Created', false);

            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

            $transactionSave->save();
            //END Handle Invoice

            $this->createShipment($order, $items);
        } catch (Exception $e) {
            $order->addStatusHistoryComment('Exception occurred during creation of invoice action. Exception message: ' . $e->getMessage(), false);
            $order->save();
        }
    }

    private function createShipment($order, $items) {
        try {
            //START Handle Shipment
            if (!$order->canShip()) {
                $order->addStatusHistoryComment(' Order cannot be Shipped.', false);
                $order->save();
            }

            $shipment = $order->prepareShipment($items);
            $shipment->register();

            $order->setIsInProcess(true);
            $order->addStatusHistoryComment('Shipment Created', false);

            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder());

            $transactionSave->save();
            //END Handle Shipment
        } catch (Exception $e) {
            $order->addStatusHistoryComment('Exception occurred during creation of shipment action. Exception message: ' . $e->getMessage(), false);
            $order->save();
        }
    }

}
