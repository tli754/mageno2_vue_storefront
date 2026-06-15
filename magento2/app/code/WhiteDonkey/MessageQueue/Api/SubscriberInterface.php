<?php

namespace WhiteDonkey\MessageQueue\Api;

use WhiteDonkey\MessageQueue\Api\MessageInterface;

interface SubscriberInterface
{
    /**
     * @return void
     */
    public function processMessage(MessageInterface $message);
}
