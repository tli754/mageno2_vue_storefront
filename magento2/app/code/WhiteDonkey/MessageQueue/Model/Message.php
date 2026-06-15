<?php

namespace WhiteDonkey\MessageQueue\Model;

use WhiteDonkey\MessageQueue\Api\MessageInterface;

class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $message;

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        return $this->message = $message;
    }
}
