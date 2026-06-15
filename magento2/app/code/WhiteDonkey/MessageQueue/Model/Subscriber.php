<?php

namespace WhiteDonkey\MessageQueue\Model;

use WhiteDonkey\MessageQueue\Api\MessageInterface;
use WhiteDonkey\MessageQueue\Api\SubscriberInterface;

class Subscriber implements SubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public function processMessage(MessageInterface $message)
    {
        echo 'Message received: ' . $message->getMessage() . PHP_EOL;
    }
}
