<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Templates;

use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;

class DefaultTemplateManager implements TemplateManagerInterface
{
    /**
     * @var Environment $twig
     */
    private $twig;

    /**
     * @param string $templatesPath Path to templates to be used by Twig
     * @param array<mixed> $config Additional configuration for Twig
     */
    public function __construct(string $templatesPath, array $config = [])
    {
        $this->twig = new Environment(
            new FilesystemLoader($templatesPath),
            $config
        );

        $this->twig->addExtension(new StringExtension());

        $loadDebugExtension = (array_key_exists('debug', $config) && $config['debug']);

        if ($loadDebugExtension) {
            $this->twig->addExtension(new DebugExtension());
        }
    }

    /**
     * @inheritDoc
     */
    public function render(string $templateName, array $templateVariables = []): ?string
    {
        return $this->twig->render($templateName, $templateVariables);
    }

    /**
     * @inheritDoc
     */
    public function addGlobals(array $globals): TemplateManagerInterface
    {
        foreach ($globals as $variableName => $variableValue) {
            $this->twig->addGlobal($variableName, $variableValue);
        }

        return $this;
    }
}
