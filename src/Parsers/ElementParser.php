<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

use WeBee\gCMS\Templates\TemplateManagerInterface;

class ElementParser implements ContentParserInterface
{
    use ParserTrait;

    private const TEMPLATE_EXTENSION = '.twig';
    private const ELEMENT_PATTERN = '#{{ element:([a-z0-9/-]+) \[([\s\S]*?)\] }}#sim';

    public function __construct(private TemplateManagerInterface $templateManager)
    {
    }

    /**
     * Will parse element placeholders into code blocks.
     *
     * The element placeholder is defined as:
     * {{ element:<element_name> [<element_params>] }}
     *
     * Where:
     * - element_name is the relative path and name of a twig template without extension;
     * - element_params is a valid JSON string;
     */
    public function parse(?string $content): ?string
    {
        $parsedContent = preg_replace_callback(
            self::ELEMENT_PATTERN,
            'self::buildFromTemplate',
            $content
        );

        return $parsedContent ?? $content;
    }

    private function buildFromTemplate(array $matches): string
    {
        $templateParams = json_decode($matches[2], true);

        if (null === $templateParams && JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('Invalid JSON string in element placeholder: '.$matches[2]);
        }

        $templateName = $matches[1].self::TEMPLATE_EXTENSION;

        return $this->templateManager->render($templateName, $templateParams);
    }
}
