<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Command;

use DomainException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WeBee\gCMS\Processors\DefaultConfigProcessor;

abstract class AbstractCommand extends Command
{
    protected const DEFAULT_CONFIG_FILE_PATH = __DIR__.'\\..\\..\\config.json';

    protected array $config = [];

    protected function configure()
    {
        $this->addOption('config-file', 'c', InputOption::VALUE_REQUIRED, 'Path to configuration file', null);
        $this->additionalConfiguration();
    }

    abstract protected function additionalConfiguration();

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig($input->getOption('config-file'));
    }

    protected function loadConfig(?string $configFilePath)
    {
        $this->config = (new DefaultConfigProcessor())
            ->process(
                new CommandConfig(),
                [
                    $this->config,
                    $this->loadFromFile(self::DEFAULT_CONFIG_FILE_PATH),
                    $this->loadFromFile($configFilePath),
                ]
            )
        ;
    }

    protected function loadFromFile(string $configFilePath): array
    {
        if (null === $configFilePath || !file_exists($configFilePath)) {
            throw new DomainException('Configuration file not found');
        }

        $config = json_decode(file_get_contents($configFilePath), true);

        if (null === $config) {
            throw new DomainException('Configuration file is not a valid JSON file');
        }

        return $config;
    }
}
