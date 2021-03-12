<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMSTests\Helpers\Git;

use DomainException;
use Kahlan\Arg;
use WeBee\gCMS\Helpers\Git\Git;
use WeBee\gCMS\Helpers\Git\GitInterface;

describe(
    'Git',
    function () {
        it(
            'can be instantiated',
            function () {
                allow('realpath')->toBeCalled()->andReturn('/path/to/git/repository');

                $g = new Git('/path/to/git/repository');

                expect($g)->toBeAnInstanceOf(Git::class);
                expect($g)->toBeAnInstanceOf(GitInterface::class);
            }
        );

        it(
            'throws error if path does not exists',
            function () {
                allow('realpath')->toBeCalled()->andReturn(false);

                $g = function () {
                    return new Git('/path/to/not/existing/directory');
                };

                expect($g)->toThrow(new DomainException());
            }
        );

        given(
            'g',
            function () {
                allow('realpath')->toBeCalled()->andReturn('/path/to/git/repository');

                return new Git('/path/to/git/repository');
            }
        );

        it(
            'can correctly verify if path is a git repository',
            function () {
                allow($this->g)->toReceive('executeGitCommand')->with('status')->andReturn('fatal: not a git repository (or any of the parent directories): .git');
                expect($this->g->isItGit())->toBe(false);

                allow($this->g)->toReceive('executeGitCommand')->with('status')->andReturn('bash: git: command not found');
                expect($this->g->isItGit())->toBe(false);

                allow($this->g)->toReceive('executeGitCommand')->with('status')->andReturn("'git' is not recognized as an internal or external command,
                operable program or batch file.");
                expect($this->g->isItGit())->toBe(false);
            }
        );

        it(
            'will throw error if target path has repository',
            function () {
                allow($this->g)->toReceive('isItGit')->andReturn(true);
                allow('file_exists')->toBeCalled()->andReturn(true);

                $g = function () {
                    return $this->g->clone('git@repo.addr');
                };

                expect($g)->toThrow(new DomainException('/path/to/git/repository already contains repository'));
            }
        );

        it(
            'will throw error if clone fails',
            function () {
                allow('file_exists')->toBeCalled()->andReturn(true);
                allow($this->g)->toReceive('isItGit')->andReturn(false);
                allow('preg_match')->toBeCalled()->andReturn(false);

                $g = function () {
                    return $this->g->clone('git@repo.addr');
                };

                expect($g)->toThrow(new DomainException('Can not clone repository "git@repo.addr" into /path/to/git/repository'));
            }
        );

        it(
            'will throw error if after clone repository is not clean',
            function () {
                allow('file_exists')->toBeCalled()->andReturn(true);
                allow($this->g)->toReceive('isItGit')->andReturn(false, true);
                allow($this->g)->toReceive('executeGitCommand')->andReturn("Cloning into\nResolving deltas\ndone.");
                allow($this->g)->toReceive('isDirty')->andReturn(true);

                $g = function () {
                    return $this->g->clone('git@repo.addr');
                };

                expect($g)->toThrow(new DomainException('Can not clone repository "git@repo.addr" into /path/to/git/repository'));
            }
        );

        it(
            'will throw error if clone command return something unexpected',
            function () {
                allow('file_exists')->toBeCalled()->andReturn(true);
                allow($this->g)->toReceive('isItGit')->andReturn(false, true);
                allow($this->g)->toReceive('executeGitCommand')->andReturn("Cloning into 'WeBeeLogger'...\nThe authenticity of host");
                allow($this->g)->toReceive('isDirty')->andReturn(true);

                $g = function () {
                    return $this->g->clone('git@repo.addr');
                };

                expect($g)->toThrow(new DomainException('Can not clone repository "git@repo.addr" into /path/to/git/repository'));
            }
        );

        it(
            'will clone repository',
            function () {
                allow('realpath')->toBeCalled()->andReturn('path/to/git/repository');
                allow('file_exists')->toBeCalled()->andReturn(false);
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('mkdir')->andReturn(null);
                expect('Symfony\Component\Filesystem\Filesystem')->toReceive('mkdir')
                    ->with(Arg::toBe('path/to/git/repository'), Arg::toBe(0777));

                $g = new Git('/path/to/git/repository');

                allow($g)->toReceive('isItGit')->andReturn(false, true);
                allow($g)->toReceive('executeGitCommand')->andReturn("Cloning into\nResolving deltas\ndone.");
                allow($g)->toReceive('isDirty')->andReturn(false);

                $g->clone('git@repo.addr');
            }
        );
    }
);
