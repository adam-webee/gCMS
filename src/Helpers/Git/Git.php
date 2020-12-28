<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Helpers\Git;

use DomainException;
use WeBee\gCMS\Helpers\FileSystem\DefaultFileSystem;
use WeBee\gCMS\Helpers\FileSystem\FileSystemInterface;
use WeBee\gCMS\Helpers\Git\GitInterface;

class Git implements GitInterface
{
    /**
     * @var FileSystemInterface $fs
     */
    private $fs;

    /**
     * @var string $repositoryPath
     */
    private $repositoryPath;

    /**
     * @inheritDoc
     */
    public function __construct(string $repositoryPath)
    {
        $this->repositoryPath = realpath($repositoryPath);

        if (false === $this->repositoryPath) {
            throw new DomainException(sprintf('Repository path "%s" does not exists', $repositoryPath));
        }

        $this->fs = new DefaultFileSystem();
    }

    /**
     * @inheritDoc
     */
    public function isItGit(): bool
    {
        $result = $this->execute('status');

        return preg_match('/.*not a git repository.*/', $result) ? false : true;
    }

    /**
     * @inheritDoc
     */
    public function clone(string $uri)
    {
        if (false == file_exists($this->repositoryPath)) {
            $this->fs->mkdir($this->repositoryPath);
        }

        if ($this->isItGit()) {
            throw new DomainException(sprintf('%s already contains repository', $this->repositoryPath));
        }

        $result = $this->execute(sprintf('clone %s %s', $uri, $this->repositoryPath));
        $cloned = preg_match(sprintf("/Cloning into/", $this->repositoryPath), $result) ? true : false;

        if (
            !$cloned
            || !$this->isItGit()
            || $this->isDirty()
        ) {
            throw new DomainException(sprintf('Can not clone repository "%s" into %s', $uri, $this->repositoryPath));
        }
    }

    /**
     * @inheritDoc
     */
    public function checkout(string $branch = 'master', bool $force = false)
    {
        if (!$this->isItGit()) {
            throw new DomainException(sprintf('%s is not a git repository', $this->repositoryPath));
        }

        if ($this->isDirty()) {
            $this->execute('checkout -- .');
            $this->execute('reset HEAD .');
            $this->execute('checkout -- .');

            if ($this->isDirty()) {
                throw new DomainException(sprintf('Can not change branch to "%s" in repository: %s', $branch, $this->repositoryPath));
            }
        }

        $result = $this->execute('checkout ' . $branch);

        if (
            preg_match("/Already on '$branch'/", $result)
            || preg_match("/Switched to branch '$branch'/", $result)
        ) {
            return;
        }

        throw new DomainException(sprintf('Cannot switch to branch "%s" in repository: %s', $branch, $this->repositoryPath));
    }

    /**
     * @inheritDoc
     */
    public function pull(bool $fastForward = true)
    {
        if ($this->isDirty()) {
            throw new DomainException(sprintf('Can not pull on dirty repository: %s', $this->repositoryPath));
        }

        $pullCommand = 'pull';

        if ($fastForward) {
            $pullCommand .= ' -ff';
        }

        $result = $this->execute($pullCommand);

        $alreadyUpdated = preg_match('/Already up to date/', $result) ? true : false;
        $pullApplied = preg_match('/From[\s\S]*Updating/m', $result) ? true : false;

        if (!$alreadyUpdated || !$pullApplied) {
            throw new DomainException(sprintf('Can not pull changes into repository: %s', $this->repositoryPath));
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $this->fs->remove($this->repositoryPath);
    }

    /**
     * @inheritDoc
     */
    public function isDirty(): bool
    {
        $result = $this->execute('status');

        return preg_match('/.*nothing to commit, working tree clean.*/', $result) ? false : true;
    }

    /**
     * Will execute provided command.
     *
     * @param string $command Command to execute
     *
     * @return null|string Result of command execution (e.g. command output)
     */
    private function execute(string $command): ?string
    {
        $command = sprintf('git -C %s %s 2>&1', $this->repositoryPath, $command);
        $output = [];
        $exeCode = 0;
        exec($command, $output, $exeCode);

        return join("\n", $output);
    }
}
