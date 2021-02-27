<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\Helpers\Git;

interface GitInterface
{
    /**
     * Initialize class instance.
     *
     * @param string $repositoryPath Relative or full path to the folder for repository
     */
    public function __construct(string $repositoryPath);

    /**
     * Verifies if provided repository path holds actual repository or not.
     *
     * @return bool True, if it is git repository
     */
    public function isItGit(): bool;

    /**
     * Clones repository from provided source.
     *
     * @param string $uri Remote repository address
     */
    public function clone(string $uri);

    /**
     * Change current branch to defined one.
     *
     * @param string $branchName [opt] Name of the branch to switch to. By default 'master'
     * @param bool   $force      [opt] If true, reverts/removes blocking changes to allow checkout. Default 'false'
     *
     * @throws DomainException if cannot change branch
     */
    public function checkout(string $branch = 'master', bool $force = false);

    /**
     * Pulls current version from remote repository.
     *
     * @param bool $fastForward will use fast forward strategy for merge
     */
    public function pull(bool $fastForward = true);

    /**
     * Deletes local repository - by deleting repository folder and all its sub-folders.
     */
    public function delete();

    /**
     * @return bool True, if current repository state is not clean (e.g. local changes)
     */
    public function isDirty(): bool;
}
