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
