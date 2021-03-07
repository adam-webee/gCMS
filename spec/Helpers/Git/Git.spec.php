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
    }
);
