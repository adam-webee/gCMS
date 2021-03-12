<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMSTests\FlexContent;

use DomainException;
use function Kahlan\allow;
use SplFileInfo;
use WeBee\gCMS\FlexContent\TypeFinder as TF;

describe(
    'Link Parser',
    function () {
        it(
            'can be instantiated',
            function () {
                expect(TF::find())->toBeAnInstanceOf(TF::class);
            }
        );

        it(
            'is a singleton',
            function () {
                $i1 = TF::find();
                $i2 = TF::find();

                expect($i1)->toBe($i2);
            }
        );

        it(
            'can register a class',
            function () {
                $f = new SplFileInfo('\var\www\title.dummy-test_class.md');
                $tf = TF::find();
                $tf->registerType('WeBee\\gCMSTests\\FlexContent\\DummyTestClass');

                expect($tf->byFile($f))->toBe('WeBee\\gCMSTests\\FlexContent\\DummyTestClass');
            }
        );

        it(
            'will throw error for incorrect file name',
            function () {
                $g = function () {
                    return TF::find()->byFile(new SplFileInfo('\var\www\dupa.md'));
                };

                expect($g)->toThrow(new DomainException());
            }
        );

        it(
            'can register a class from file name',
            function () {
                $tf = TF::find();
                $f = new SplFileInfo('\var\www\title.dummy-test_class.md');

                allow($tf)->toReceive('getNamespace')->andReturn('WeBee\\gCMSTests\\FlexContent\\');

                expect($tf->byFile($f))->toBe('WeBee\\gCMSTests\\FlexContent\\DummyTestClass');
            }
        );
    }
);
