<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Processors;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

class DefaultConfigProcessor implements ConfigProcessorInterface
{
    private Processor $processor;

    public function __construct()
    {
        $this->processor = new Processor();
    }

    public function process(ConfigurationInterface $configuration, array $configs): array
    {
        return $this->processor->processConfiguration($configuration, $configs);
    }
}
