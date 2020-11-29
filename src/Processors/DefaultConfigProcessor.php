<?php

declare(strict_types=1);

namespace WeBee\gCMS\Processors;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use WeBee\gCMS\Processors\ConfigProcessorInterface;

class DefaultConfigProcessor implements ConfigProcessorInterface
{
    /**
     * @var Processor $processor
     */
    private $processor;

    /**
     * Builds processor instance.
     */
    public function __construct()
    {
        $this->processor = new Processor();
    }

    /**
     * @inheritDoc
     */
    public function process(ConfigurationInterface $configuration, array $configs): array
    {
        return $this->processor->processConfiguration($configuration, $configs);
    }
}
