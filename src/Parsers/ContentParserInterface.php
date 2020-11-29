<?php

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

interface ContentParserInterface
{
    /**
     * Parses provided input to expected output form.
     *
     * @param null|string $content Content to be parsed
     *
     * @return null|string Parsed content
     */
    public function parse(?string $content): ?string;
}
