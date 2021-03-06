<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Templates;

interface TemplateManagerInterface
{
    /**
     * Renders templates using provided data.
     *
     * @param array<mixed> $templateVariables [opt] if not passed empty array will be used
     *
     * @return string Rendered template
     */
    public function render(string $templateName, array $templateVariables = []): ?string;

    /**
     * Registers  global variables (accessible for all templates).
     *
     * New globals can be added before compiling or rendering a template;
     * but after, you can only update existing globals.
     *
     * @param array<mixed> $globals
     */
    public function addGlobals(array $globals): self;
}
