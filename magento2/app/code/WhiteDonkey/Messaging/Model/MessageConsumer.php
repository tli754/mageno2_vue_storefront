<?php

namespace WhiteDonkey\Messaging\Model;

class MessageConsumer
{
    /**
     * @param Customer $customer
     */
    public function processMessage(string $message)
    {
        echo "ccc: $message\n";
    }
}
