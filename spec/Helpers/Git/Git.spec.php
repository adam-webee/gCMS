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

use function Kahlan\allow;

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
            'can correctly delete repository',
            function () {
                allow('realpath')->toBeCalled()->andReturn('path/to/git/repository');
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('remove')->andReturn(null);
                expect('Symfony\Component\Filesystem\Filesystem')->toReceive('remove')
                    ->with(Arg::toBe('path/to/git/repository'));

                (new Git('/path/to/git/repository'))->delete();
            }
        );

        it(
            'can check if repository is dirty',
            function () {
                allow($this->g)->toReceive('executeGitCommand')->with('status')
                    ->andReturn("On branch main
                    Your branch is ahead of 'origin/main' by 1 commit.
                      (use \"git push\" to publish your local commits)

                    Changes not staged for commit:
                      (use \"git add <file>...\" to update what will be committed)
                      (use \"git checkout -- <file>...\" to discard changes in working directory)

                            modified:   spec/Helpers/Git/Git.spec.php

                    no changes added to commit (use \"git add\" and/or \"git commit -a\")");
                expect($this->g->isDirty())->toBe(true);

                allow($this->g)->toReceive('executeGitCommand')->with('status')
                    ->andReturn("On branch master
                    nothing to commit, working tree clean");
                expect($this->g->isDirty())->toBe(false);
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

                allow($this->g)->toReceive('executeGitCommand')->with('status')->andReturn("On branch main
                Your branch is ahead of 'origin/main' by 1 commit.
                  (use \"git push\" to publish your local commits)

                nothing to commit, working tree clean");
                expect($this->g->isItGit())->toBe(true);
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

        it(
            'will throw error in case of pulling on dirty repository',
            function () {
                allow('realpath')->toBeCalled()->andReturn('path/to/git/repository');
                allow($this->g)->toReceive('isDirty')->andReturn(true);

                $g = function () {
                    return $this->g->pull();
                };

                expect($g)->toThrow(new DomainException('Can not pull on dirty repository: /path/to/git/repository'));
            }
        );

        it(
            'will throw error for unknown git response',
            function () {
                allow('realpath')->toBeCalled()->andReturn('path/to/git/repository');
                allow($this->g)->toReceive('isDirty')->andReturn(false);
                allow($this->g)->toReceive('executeGitCommand')->andReturn('');

                $g = function () {
                    return $this->g->pull();
                };

                expect($g)->toThrow(new DomainException('Can not pull changes into repository: /path/to/git/repository'));
            }
        );

        it(
            'can correctly update repository',
            function () {
                allow($this->g)->toReceive('isDirty')->andReturn(false);
                allow($this->g)->toReceive('executeGitCommand')->andReturn('Already up to date.');
                expect($this->g)->toReceive('executeGitCommand')->with(Arg::toBe('pull -ff'));
                $this->g->pull();

                allow($this->g)->toReceive('isDirty')->andReturn(false);
                allow($this->g)->toReceive('executeGitCommand')->andReturn('From xxx. Updating');
                expect($this->g)->toReceive('executeGitCommand')->with(Arg::toBe('pull'));
                $this->g->pull(false);
            }
        );

        it(
            'will throw error on checkout outside git repository',
            function () {
                allow('realpath')->toBeCalled()->andReturn('path/to/git/repository');
                allow($this->g)->toReceive('isItGit')->andReturn(false);
                $g = function () {
                    return $this->g->checkout();
                };

                expect($g)->toThrow(new DomainException('/path/to/git/repository is not a git repository'));
            }
        );

        it(
            'will throw error if unable to cleanup before checkout',
            function () {
                allow($this->g)->toReceive('isItGit')->andReturn(true);
                allow($this->g)->toReceive('isDirty')->andReturn(true, true);
                allow($this->g)->toReceive('executeGitCommand')->andReturn(null, null, null);

                expect($this->g)->toReceive('executeGitCommand')->with(Arg::toBe('checkout -- .'))->ordered;
                expect($this->g)->toReceive('executeGitCommand')->with(Arg::toBe('reset HEAD .'))->ordered;
                expect($this->g)->toReceive('executeGitCommand')->with(Arg::toBe('checkout -- .'))->ordered;

                $g = function () {
                    return $this->g->checkout('master', true);
                };

                expect($g)->toThrow(new DomainException('Dirty repository. Can not change branch to "master" in repository: /path/to/git/repository'));
            }
        );

        it(
            'will throw error if unsuccessful checkout',
            function () {
                allow($this->g)->toReceive('isItGit')->andReturn(true);
                allow($this->g)->toReceive('isDirty')->andReturn(false);
                allow($this->g)->toReceive('executeGitCommand')->andReturn('Not a git repository');
                expect($this->g)->toReceive('executeGitCommand')->with(Arg::toBe('checkout master'));

                $g = function () {
                    return $this->g->checkout();
                };

                expect($g)->toThrow(new DomainException('Can not switch to branch "master" in repository: /path/to/git/repository'));
            }
        );

        it(
            'can correctly checkout branch',
            function () {
                allow($this->g)->toReceive('isItGit')->andReturn(true);
                allow($this->g)->toReceive('isDirty')->andReturn(false);
                allow($this->g)->toReceive('executeGitCommand')->andReturn("Already on 'feature/unit-test'");

                expect($this->g->checkout('feature/unit-test', true))->not->toThrow(new DomainException());

                allow($this->g)->toReceive('isItGit')->andReturn(true);
                allow($this->g)->toReceive('isDirty')->andReturn(false);
                allow($this->g)->toReceive('executeGitCommand')->andReturn("Switched to branch 'feature/unit/test'");

                expect($this->g->checkout('feature/unit/test', true))->not->toThrow(new DomainException());
            }
        );

        it(
            'will not cleanup before checkout without force parameter set to true',
            function () {
                allow($this->g)->toReceive('isItGit')->andReturn(true);
                allow($this->g)->toReceive('isDirty')->andReturn(true, true);
                expect($this->g)->not->toReceive('clearDirty');

                $g = function () {
                    return $this->g->checkout('feature/dirty');
                };

                expect($g)->toThrow(new DomainException('Dirty repository. Can not change branch to "feature/dirty" in repository: /path/to/git/repository'));
            }
        );
    }
);
