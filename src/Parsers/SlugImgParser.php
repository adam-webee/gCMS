<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

class SlugImgParser implements ContentParserInterface
{
    use ParserTrait;

    protected const IMG_PATTERN = '#(!\[.*?\]\()({{ img:([\w\d\/\-\_]+?\.(?:jpg|png|svg|gif)) }}(\)))#ims';
    protected const IMG_REPLACE_TEMPLATE = '${1}%s${3}${4}';

    /**
     * Will parse img link placeholders into actual img link.
     *
     * Img link placeholder: ![Image alternative]({{ img:path/to/image.png }})
     */
    public function parse(?string $content): ?string
    {
        $parsedContent = preg_replace(
            self::IMG_PATTERN,
            sprintf(self::IMG_REPLACE_TEMPLATE, $this->getBasePath()),
            $content
        );

        return $parsedContent ?? $content;
    }
}
