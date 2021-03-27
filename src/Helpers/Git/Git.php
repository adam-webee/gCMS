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

class Git implements GitInterface
{
    private FileSystemInterface $fs;

    private string $repositoryPath;

    public function __construct(string $repositoryPath)
    {
        $realpath = realpath($repositoryPath);

        if (false === $realpath) {
            throw new DomainException(sprintf('Repository path "%s" does not exists', $repositoryPath));
        }

        $this->repositoryPath = $realpath;
        $this->fs = new DefaultFileSystem();
    }

    public function isItGit(): bool
    {
        $result = $this->executeGitCommand('status');

        if (preg_match('/.*not a git repository.*/', $result)) {
            return false;
        }

        if (preg_match('/.*(command not found|is not recognized).*/', $result)) {
            return false;
        }

        return true;
    }

    public function clone(string $uri)
    {
        if (false == file_exists($this->repositoryPath)) {
            $this->fs->mkdir($this->repositoryPath);
        }

        if ($this->isItGit()) {
            throw new DomainException(sprintf('%s already contains repository', $this->repositoryPath));
        }

        $result = $this->executeGitCommand(sprintf('clone %s %s', $uri, $this->repositoryPath));
        $cloned = preg_match('/^Cloning into[\S\s]*Resolving deltas[\S\s]*done\.$/', $result) ? true : false;

        if (
            !$cloned
            || !$this->isItGit()
            || $this->isDirty()
        ) {
            throw new DomainException(sprintf('Can not clone repository "%s" into %s', $uri, $this->repositoryPath));
        }
    }

    public function checkout(string $branch = 'master', bool $force = false)
    {
        if (!$this->isItGit()) {
            throw new DomainException(sprintf('%s is not a git repository', $this->repositoryPath));
        }

        if ($this->isDirty()) {
            if ($force) {
                $this->clearDirty();
            }

            if ($this->isDirty()) {
                throw new DomainException(sprintf('Dirty repository. Can not change branch to "%s" in repository: %s', $branch, $this->repositoryPath));
            }
        }

        $result = $this->executeGitCommand('checkout '.$branch);

        if (
            preg_match("#Already on '$branch'#", $result)
            || preg_match("#Switched to branch '$branch'#", $result)
        ) {
            return;
        }

        throw new DomainException(sprintf('Can not switch to branch "%s" in repository: %s', $branch, $this->repositoryPath));
    }

    private function clearDirty(): void
    {
        $this->executeGitCommand('checkout -- .');
        $this->executeGitCommand('reset HEAD .');
        $this->executeGitCommand('checkout -- .');
    }

    public function pull(bool $fastForward = true)
    {
        if ($this->isDirty()) {
            throw new DomainException(sprintf('Can not pull on dirty repository: %s', $this->repositoryPath));
        }

        $pullCommand = 'pull';

        if ($fastForward) {
            $pullCommand .= ' -ff';
        }

        $result = $this->executeGitCommand($pullCommand);

        $alreadyUpdated = preg_match('/Already up to date/', $result) ? true : false;
        $pullApplied = preg_match('/From[\s\S]*Updating/m', $result) ? true : false;

        if ($alreadyUpdated || $pullApplied) {
            return;
        }

        throw new DomainException(sprintf('Can not pull changes into repository: %s', $this->repositoryPath));
    }

    public function delete()
    {
        $this->fs->remove($this->repositoryPath);
    }

    public function isDirty(): bool
    {
        $result = $this->executeGitCommand('status');

        return preg_match('/.*nothing to commit, working tree clean.*/', $result) ? false : true;
    }

    private function executeGitCommand(string $command): ?string
    {
        $command = sprintf('git -C %s %s 2>&1', $this->repositoryPath, $command);
        $output = [];
        $exeCode = 0;
        exec($command, $output, $exeCode);

        return join("\n", $output);
    }
}
