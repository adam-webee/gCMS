<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Parsers;

trait ParserTrait
{
    protected array $params = [];

    public function setParam(?string $name = null, mixed $value = null): ContentParserInterface
    {
        if (null === $name) {
            $this->params = $value ?? [];

            return $this;
        }

        if (array_key_exists($name, $this->params) && null === $value) {
            unset($this->params[$name]);
        } else {
            $this->params[$name] = $value;
        }

        return $this;
    }

    protected function getExtension(): string
    {
        $ext = $this->params['path'] ?? [];
        $ext = ltrim($ext['extension'] ?? '', '.');

        return $ext ? ".$ext" : $ext;
    }

    protected function getBasePath(): string
    {
        $path = $this->params['path'] ?? [];
        $path = str_replace(['/', '\\'], '/', $path['base'] ?? '');

        return $path ? "$path/" : $path;
    }
}
