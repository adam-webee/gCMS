<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMSTests\Parsers;

use WeBee\gCMS\Parsers\ContentParserInterface;
use WeBee\gCMS\Parsers\SlugLinksParser as Parser;

describe(
    'Link Parser',
    function () {
        given(
            'lp',
            function () {
                return new Parser();
            }
        );

        it(
            'can be instantiated',
            function () {
                expect($this->lp)->toBeAnInstanceOf(Parser::class);
                expect($this->lp)->toBeAnInstanceOf(ContentParserInterface::class);
            }
        );

        it(
            'can parse link',
            function () {
                expect($this->lp->parse('test string'))->toBe('test string');
                expect($this->lp->parse('this is (Best blog ever)[https://webee.school]'))->toBe('this is (Best blog ever)[https://webee.school]');
                expect($this->lp->parse('this is [Best blog ever]({{ slug:index }})'))->toBe('this is [Best blog ever](index)');

                $this->lp->setParam('path', ['base' => 'C:\temp\blog', 'extension' => 'html']);
                expect($this->lp->parse('this is [Best blog ever]({{ slug:index }})'))->toBe('this is [Best blog ever](C:/temp/blog/index.html)');
                expect($this->lp->parse('this is [Best blog ever] ({{ slug:index }})'))->toBe('this is [Best blog ever] ({{ slug:index }})');

                $this->lp->setParam('path', ['base' => '/var/www/html', 'extension' => 'jpg']);
                expect($this->lp->parse('this is [Image link]({{ slug:best/image/link }})'))->toBe('this is [Image link](/var/www/html/best/image/link.jpg)');

                expect($this->lp->parse('this is ![Image link]({{ slug:best/image/link }})'))->toBe('this is ![Image link]({{ slug:best/image/link }})');
                expect($this->lp->parse('this is ![Image link]({{ slug:best/image/link.jpg }})'))->toBe('this is ![Image link]({{ slug:best/image/link.jpg }})');
            }
        );
    }
);
