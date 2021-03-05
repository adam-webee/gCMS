<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

final class ParserManager extends \ArrayIterator implements ParserManagerInterface
{
    use ParserTrait;

    public function __construct(ContentParserInterface ...$parsers)
    {
        parent::__construct($parsers);
    }

    public function register(ContentParserInterface $parser): ParserManagerInterface
    {
        $this->append($parser);

        return $this;
    }

    public function parse(?string $content): ?string
    {
        $result = $content;

        foreach ($this as $parser) {
            $parser->setParam(null, $this->params);
            $result = $parser->parse($result);
        }

        return $result;
    }
}
