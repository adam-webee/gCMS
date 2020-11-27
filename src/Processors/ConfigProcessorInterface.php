<?php

declare(strict_types=1);

namespace WeBee\gCMS\Processors;

use Symfony\Component\Config\Definition\ConfigurationInterface;

interface ConfigProcessorInterface
{
    /**
     * Processes an array of configurations.
     *
     * @param array $configs An array of configuration items to process
     *
     * @return array The processed configuration
     */
    public function process(ConfigurationInterface $configuration, array $configs): array;
}
