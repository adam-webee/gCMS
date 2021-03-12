<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMSTests\Helpers\FileSystem;

use function Kahlan\allow;
use Kahlan\Arg;
use WeBee\gCMS\Helpers\FileSystem\DefaultFileSystem;
use WeBee\gCMS\Helpers\FileSystem\FileSystemInterface;

describe(
    'Default File System',
    function () {
        it(
            'can be instantiated',
            function () {
                $dfs = new DefaultFileSystem();

                expect($dfs)->toBeAnInstanceOf(DefaultFileSystem::class);
                expect($dfs)->toBeAnInstanceOf(FileSystemInterface::class);
            }
        );

        it(
            'can create directory',
            function () {
                expect('Symfony\Component\Filesystem\Filesystem')->toReceive('mkdir')->with(Arg::toBe('/var/www'), Arg::toBe(0777));
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('mkdir')->andReturn(null);
                $fs = new DefaultFileSystem();
                expect($fs->mkdir('/var/www'))->toBe($fs);
            }
        );

        it(
            'can create directory with requested mode',
            function () {
                expect('Symfony\Component\Filesystem\Filesystem')->toReceive('mkdir')->with(Arg::toBe('/var/www'), Arg::toBe(0644));
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('mkdir')->andReturn(null);
                $fs = new DefaultFileSystem();
                expect($fs->mkdir('/var/www', 0644))->toBe($fs);
            }
        );

        it(
            'can check if file exists',
            function () {
                expect('Symfony\Component\Filesystem\Filesystem')->toReceive('exists')->with(Arg::toBe('/var/www'));
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('exists')->andReturn(true);
                $fs = new DefaultFileSystem();
                expect($fs->exists('/var/www'))->toBe(true);
            }
        );

        it(
            'can delete directory',
            function () {
                expect('Symfony\Component\Filesystem\Filesystem')->toReceive('remove')->with(Arg::toBe('/var/www'));
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('remove')->andReturn(null);
                $fs = new DefaultFileSystem();
                expect($fs->remove('/var/www'))->toBe($fs);
            }
        );

        it(
            'can dump content into the file',
            function () {
                expect('Symfony\Component\Filesystem\Filesystem')->toReceive('dumpFile')->with(Arg::toBe('/var/www'), Arg::toBe('content'));
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('dumpFile')->andReturn(null);
                $fs = new DefaultFileSystem();
                expect($fs->dumpFile('/var/www', 'content'))->toBe($fs);
            }
        );

        it(
            'can dump content into the file',
            function () {
                expect('Symfony\Component\Filesystem\Filesystem')
                    ->toReceive('mirror')
                    ->with(
                        Arg::toBe('/source/dir'),
                        Arg::toBe('/target/dir'),
                        Arg::toBeNull(),
                        Arg::toHaveLength(0)
                    );
                allow('Symfony\Component\Filesystem\Filesystem')->toReceive('mirror')->andReturn(null);

                $fs = new DefaultFileSystem();
                expect($fs->mirror('/source/dir', '/target/dir'))->toBe($fs);
            }
        );
    }
);
