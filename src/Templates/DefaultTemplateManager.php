<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Templates;

use Exception;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class DefaultTemplateManager implements TemplateManagerInterface
{
    private Environment $twig;

    public function __construct(string $templatesPath, array $templatesConfig = [])
    {
        $this->twig = new Environment(
            new FilesystemLoader($templatesPath),
            $templatesConfig
        );

        $extensions = $templatesConfig['extensions'] ?? [];
        $loadDebugExtension = (array_key_exists('debug', $templatesConfig) && $templatesConfig['debug']);

        if ($loadDebugExtension) {
            $extensions[] = 'Twig\Extension\DebugExtension';
        }

        $this->loadExtensions($extensions);
    }

    public function render(string $templateName, array $templateVariables = []): ?string
    {
        return $this->twig->render($templateName, $templateVariables);
    }

    public function addGlobals(array $globals): TemplateManagerInterface
    {
        foreach ($globals as $variableName => $variableValue) {
            $this->twig->addGlobal($variableName, $variableValue);
        }

        return $this;
    }

    private function loadExtensions(array $extensions = []): void
    {
        foreach ($extensions as $extension) {
            if (!class_exists($extension)) {
                throw new Exception("Requested extension '$extension' does not exists");
            }

            $this->twig->addExtension(new $extension());
        }
    }
}
