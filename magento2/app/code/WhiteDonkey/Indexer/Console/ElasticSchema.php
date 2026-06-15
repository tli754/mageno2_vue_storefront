<?php

namespace WhiteDonkey\Indexer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WhiteDonkey\Elastic\Model\ElasticMigrator;

class ElasticSchema extends Command
{
    const NAME = 'indexer:elastic-schema';
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription('create Elastic Search index.');

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
        try {
            $elasticMigrator = new ElasticMigrator();
            $elasticMigrator->run();
            $output->writeln('<info>Run Elastic Search schema finished ... </info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Error: '.$e->getMessage().'</error>');
        }
    }
}

