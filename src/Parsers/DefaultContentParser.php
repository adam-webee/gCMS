<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

use League\CommonMark\CommonMarkConverter;

class DefaultContentParser implements ContentParserInterface
{
    /**
     * @var CommonMarkConverter $converter
     */
    private $converter;

    /**
     * Builds converter instance.
     */
    public function __construct()
    {
        $this->converter = new CommonMarkConverter();
    }

    /**
     * @inheritDoc
     */
    public function parse(?string $content): ?string
    {
        if (is_null($content)) {
            return '';
        }

        return $this->converter->convertToHtml($content);
    }
}
