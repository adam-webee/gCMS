<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent\Types;

class Error extends Page
{
    protected function loadConfigDefinition()
    {
        $this->configDefinition = new ErrorConfig();
    }

    public function getRelationName(): string
    {
        return self::RELATION_TECH_CHILD;
    }
}
