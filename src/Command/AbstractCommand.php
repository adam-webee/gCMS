<?php

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WeBee\gCMS\Command\CommandConfig;
use WeBee\gCMS\Processors\DefaultConfigProcessor;

abstract class AbstractCommand extends Command
{
    /**
     * @var array<mixed> $config Command configuration
     */
    protected $config = [];

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'Path to configuration file', null);
        $this->addConfiguration();
    }

    /**
     * Defines specific part of command configuration
     */
    abstract protected function addConfiguration();

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input->getOption('config-file'));
    }

    /**
     * Loads configuration necessary for command execution.
     *
     * @param null|string $configFilePath Path to file with generation settings
     */
    protected function loadConfig(?string $configFilePath)
    {
        if (null === $configFilePath || !file_exists($configFilePath)) {
            throw new DomainException('Configuration file not found');
        }

        $config = json_decode(file_get_contents($configFilePath), true);

        if (null === $config) {
            throw new DomainException('Configuration file is not a valid JSON file');
        }

        $this->config = (new DefaultConfigProcessor())
            ->process(
                new CommandConfig(),
                [$this->config, $config]
            )
        ;
    }
}
