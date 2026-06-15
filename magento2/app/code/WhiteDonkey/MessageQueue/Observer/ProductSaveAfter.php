<?php

namespace WhiteDonkey\MessageQueue\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        var_dump("create a indexer, index product to elastic search");
//        $this->publisher->publish('product.updates', $observer->getProduct()->getId());
    }
}
