<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

class LinksParser implements ContentParserInterface
{
    use ParserTrait;

    private const SLUG_PATTERN = '#{{ slug:([\w\d\/\-\_]+?) }}#ims';
    private const SLUG_REPLACE_TEMPLATE = '%s${1}%s';

    private const STATIC_PATTERN = '#{{ static:([\w\d\/\-\_\.]+?) }}#ims';
    private const STATIC_REPLACE_TEMPLATE = '%s${1}';

    /**
     * Will parse slug placeholders into actual links.
     *
     * Link placeholder: {{ slug:page/slug/without/extension }}
     * Static placeholder: {{ static:path/to/file.ext }}
     */
    public function parse(?string $content): ?string
    {
        $parsedContent = $this->parseSlug(
            $this->parseStatic($content)
        );

        return $parsedContent ?? $content;
    }

    private function parseSlug(?string $content): ?string
    {
        $parsedContent = preg_replace(
            self::SLUG_PATTERN,
            sprintf(self::SLUG_REPLACE_TEMPLATE, $this->getBasePath(), $this->getExtension()),
            $content
        );

        return $parsedContent ?? $content;
    }

    private function parseStatic(?string $content): ?string
    {
        $parsedContent = preg_replace(
            self::STATIC_PATTERN,
            sprintf(self::STATIC_REPLACE_TEMPLATE, $this->getStaticPath()),
            $content
        );

        return $parsedContent ?? $content;
    }

    protected function getStaticPath(): string
    {
        $path = $this->params['path'] ?? [];
        $path = str_replace(['/', '\\'], '/', $path['static'] ?? '');

        return $path ? "$path/" : $path;
    }
}
