<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

interface ContentParserInterface
{
    /**
     * Parses provided input to expected output form.
     *
     * @param string|null $content Content to be parsed
     *
     * @return string|null Parsed content
     */
    public function parse(?string $content): ?string;

    /**
     * Sets params needed by parser.
     *
     * @param string|null $name  Parameter name, if set to null $value will be set as parameter
     * @param mixed|null  $value Parameter value, if set to null parameter will be unset
     */
    public function setParam(?string $name = null, mixed $value = null): ContentParserInterface;
}
