<?php

namespace WhiteDonkey\MessageQueue\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use WhiteDonkey\MessageQueue\Api\MessageInterface;

class MessagePublishCommand extends Command
{
    const COMMAND_QUEUE_MESSAGE_PUBLISH = 'queue:publish:index';
    const MESSAGE_ARGUMENT = 'message';

    /**
     * @var PublisherInterface
     */
    protected $publisher;

    /**
     * @var string
     */
    protected $message;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        PublisherInterface $publisher,
        MessageInterface $message,
        $name = null
    ) {
        $this->publisher = $publisher;
        $this->message = $message;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument(self::MESSAGE_ARGUMENT);
        $topic = 'magento.elastic.index';

        try {
            $this->message->setMessage($message);
            $this->publisher->publish($topic, $this->message);
            $output->writeln(sprintf('Published message "%s" to topic "%s"', $message, $topic));
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_QUEUE_MESSAGE_PUBLISH);
        $this->setDescription('Publish a message to a topic');
        $this->setDefinition([
            new InputArgument(
                self::MESSAGE_ARGUMENT,
                InputArgument::REQUIRED,
                'Message'
            )
        ]);
        parent::configure();
    }
}
