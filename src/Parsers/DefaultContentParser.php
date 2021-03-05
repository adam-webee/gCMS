<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

use League\CommonMark\CommonMarkConverter as Converter;

class DefaultContentParser implements ContentParserInterface
{
    use ParserTrait;

    private Converter $converter;

    public function __construct()
    {
        $this->converter = new Converter();
    }

    public function parse(?string $content): ?string
    {
        if (is_null($content)) {
            return '';
        }

        return $this->converter->convertToHtml($content);
    }
}
