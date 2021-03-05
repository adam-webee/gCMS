<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMSTests\Parsers;

use ArrayIterator;
use WeBee\gCMS\Parsers\ContentParserInterface;
use WeBee\gCMS\Parsers\ParserManager;
use WeBee\gCMS\Parsers\ParserManagerInterface;
use WeBee\gCMS\Parsers\ParserTrait;

describe(
    'Parser Manager',
    function () {
        given(
            'toUpperParser',
            function () {
                return new class() implements ContentParserInterface {
                    use ParserTrait;

                    public function parse(?string $content): ?string
                    {
                        return strtoupper($content);
                    }
                };
            }
        );

        given(
            'replaceParser',
            function () {
                return new class() implements ContentParserInterface {
                    use ParserTrait;

                    public function parse(?string $content): ?string
                    {
                        $from = $this->params['from'] ?? 'AEIOUYaeiouy';
                        $to = $this->params['to'] ?? '';

                        return str_replace(str_split($from), $to, $content);
                    }
                };
            }
        );

        given(
            'removeFiveParser',
            function () {
                return new class() implements ContentParserInterface {
                    use ParserTrait;

                    public function parse(?string $content): ?string
                    {
                        return str_replace('5', '', $content);
                    }
                };
            }
        );

        it(
            'can be instantiated',
            function () {
                $pm = new ParserManager();

                expect($pm)->toBeAnInstanceOf(ParserManager::class);
                expect($pm)->toBeAnInstanceOf(ArrayIterator::class);
                expect($pm)->toBeAnInstanceOf(ParserManagerInterface::class);
            }
        );

        it(
            'can be instantiated with parser',
            function () {
                $pm = new ParserManager($this->toUpperParser);

                expect($pm)->toBeAnInstanceOf(ParserManager::class);
                expect($pm->count())->toBe(1);
                expect($pm[0])->toBe($this->toUpperParser);
            }
        );

        it(
            'can register parsers',
            function () {
                $pm = (new ParserManager())
                    ->register($this->toUpperParser)
                    ->register($this->replaceParser)
                    ->register($this->removeFiveParser);

                expect($pm->count())->toBe(3);
                expect($pm[0])->toBe($this->toUpperParser);
                expect($pm[1])->toBe($this->replaceParser);
                expect($pm[2])->toBe($this->removeFiveParser);
            }
        );

        it(
            'can parse with registered parser',
            function () {
                $pm = (new ParserManager())->register($this->toUpperParser);

                expect($pm->parse('abc'))->toBe('ABC');
            }
        );

        it(
            'can parse cascade',
            function () {
                $pm = (new ParserManager())
                    ->register($this->toUpperParser)
                    ->register($this->replaceParser)
                    ->register($this->removeFiveParser);

                expect($pm->parse('ab5c'))->toBe('BC');
            }
        );

        it(
            'can parse with params',
            function () {
                $pm = (new ParserManager($this->replaceParser))->setParam(null, ['from' => 'a5', 'to' => '1']);

                expect($pm->parse('ab5c'))->toBe('1b1c');
            }
        );
    }
);
