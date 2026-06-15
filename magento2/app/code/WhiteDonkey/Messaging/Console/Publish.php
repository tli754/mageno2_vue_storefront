<?php

namespace WhiteDonkey\Messaging\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

class Publish extends Command
{
    const MESSAGE = 'message';
    const TOPIC_NAME = 'simple_message';

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * DeletePublisher constructor.
     * @param PublisherInterface $publisher
     */
    public function __construct(PublisherInterface $publisher, string $name = null)
    {
        $this->publisher = $publisher;

        return parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $options = [
            new InputArgument(
                self::MESSAGE,
                InputArgument::REQUIRED,
                'Message'
            )
        ];
        $this->setName('customer:publish');
        $this->setDescription('Envoi un message.');
        $this->setDefinition($options);

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument(self::MESSAGE);

        try {
            $this->publisher->publish(self::TOPIC_NAME, $message);
            $output->writeln('<info>Envoyé: '.$message.'</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error: '.$e->getMessage().'</error>');
        }
    }
}

