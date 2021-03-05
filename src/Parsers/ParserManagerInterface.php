<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

interface ParserManagerInterface extends ContentParserInterface, \Iterator
{
    /**
     * Order of registration matters. Parsers will be executed in same order as registered.
     */
    public function register(ContentParserInterface $parser): ParserManagerInterface;
}
