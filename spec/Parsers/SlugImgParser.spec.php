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
use WeBee\gCMS\Parsers\SlugImgParser as Parser;

describe(
    'Img Parser',
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
                expect($this->lp->parse('this is (Best image ever)[https://webee.school/logo.png]'))->toBe('this is (Best image ever)[https://webee.school/logo.png]');
                expect($this->lp->parse('this is ![Best image ever]({{ img:logo }})'))->toBe('this is ![Best image ever]({{ img:logo }})');
                expect($this->lp->parse('this is ![Best image ever]({{ img:logo.jpg }})'))->toBe('this is ![Best image ever](logo.jpg)');

                $this->lp->setParam('path', ['base' => 'C:\temp\blog', 'extension' => 'svg']);
                expect($this->lp->parse('this is ![Best image ever]({{ img:logo.png }})'))->toBe('this is ![Best image ever](C:/temp/blog/logo.png)');
                expect($this->lp->parse('this is ![Best image ever] ({{ img:logo.png }})'))->toBe('this is ![Best image ever] ({{ img:logo.png }})');

                $this->lp->setParam('path', ['base' => '/var/www/html', 'extension' => 'jpg']);
                expect($this->lp->parse('this is ![Image link]({{ img:best/image/link.gif }})'))->toBe('this is ![Image link](/var/www/html/best/image/link.gif)');
                expect($this->lp->parse('this is ![Image link]({{ img:best/image/link.png }})'))->toBe('this is ![Image link](/var/www/html/best/image/link.png)');
                expect($this->lp->parse('this is ![Image link]({{ img:best/image/link.svg }})'))->toBe('this is ![Image link](/var/www/html/best/image/link.svg)');
                expect($this->lp->parse('this is ![Image link]({{ img:best/image/link.jpg }})'))->toBe('this is ![Image link](/var/www/html/best/image/link.jpg)');

                expect($this->lp->parse('this is ![Image link]({{ img:best/image/link }})'))->toBe('this is ![Image link]({{ img:best/image/link }})');
                expect($this->lp->parse('this is ![Image link]({{ img:best/image/link.doc }})'))->toBe('this is ![Image link]({{ img:best/image/link.doc }})');
            }
        );
    }
);
