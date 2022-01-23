<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

/**
 * @deprecated use WeBee\gCMS\Parsers\LinksParser instead
 */
class SlugLinksParser implements ContentParserInterface
{
    use ParserTrait;

    private const LINKS_PATTERN = '#([^!]\[.*?\]\()({{ slug:([\w\d\/\-\_]+?) }})(\))#ims';
    private const LINK_REPLACE_TEMPLATE = '${1}%s${3}%s${4}';

    /**
     * Will parse link placeholders into actual links.
     *
     * Link placeholder: [Link name]({{ slug:page/slug/without/extension }})
     */
    public function parse(?string $content): ?string
    {
        $parsedContent = preg_replace(
            self::LINKS_PATTERN,
            sprintf(self::LINK_REPLACE_TEMPLATE, $this->getBasePath(), $this->getExtension()),
            $content
        );

        return $parsedContent ?? $content;
    }
}
