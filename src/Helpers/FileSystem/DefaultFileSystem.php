<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Helpers\FileSystem;

use Symfony\Component\Filesystem\Filesystem as RawFileSystem;

class DefaultFileSystem implements FileSystemInterface
{
    private RawFileSystem $fs;

    public function __construct()
    {
        $this->fs = new RawFileSystem();
    }

    public function mkdir($dirs, int $mode = 0777): FileSystemInterface
    {
        $this->fs->mkdir($dirs, $mode);

        return $this;
    }

    public function exists($files): bool
    {
        return $this->fs->exists($files);
    }

    public function remove($files): FileSystemInterface
    {
        $this->fs->remove($files);

        return $this;
    }

    public function dumpFile(string $filename, $content): FileSystemInterface
    {
        $this->fs->dumpFile($filename, $content);

        return $this;
    }

    public function mirror(string $originDir, string $targetDir, \Traversable $iterator = null, array $options = []): FileSystemInterface
    {
        $this->fs->mirror($originDir, $targetDir, $iterator, $options);

        return $this;
    }
}
