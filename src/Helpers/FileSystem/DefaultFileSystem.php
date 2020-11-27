<?php

declare(strict_types=1);

namespace WeBee\gCMS\Helpers\FileSystem;

use Symfony\Component\Filesystem\Filesystem;

class DefaultFileSystem implements FileSystemInterface
{
    /**
     * @var Filesystem $fs
     */
    private $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    /**
     * @inheritDoc
     */
    public function mkdir($dirs, int $mode = 0777): FileSystemInterface
    {
        $this->fs->mkdir($dirs, $mode);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exists($files): bool
    {
        return $this->fs->exists($files);
    }

    /**
     * @inheritDoc
     */
    public function remove($files): FileSystemInterface
    {
        $this->fs->remove($files);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function dumpFile(string $filename, $content): FileSystemInterface
    {
        $this->fs->dumpFile($filename, $content);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mirror(string $originDir, string $targetDir, \Traversable $iterator = null, array $options = []): FileSystemInterface
    {
        $this->fs->mirror($originDir, $targetDir, $iterator, $options);

        return $this;
    }
}
