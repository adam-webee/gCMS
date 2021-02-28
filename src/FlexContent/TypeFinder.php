<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent;

use DomainException;
use ReflectionClass;
use SplFileInfo;

class TypeFinder
{
    public const FILE_NAME_PATTERN = '/^.*\.(.*)\.md$/';

    private const NAMESPACE_FOR_CONTENT_TYPES = __NAMESPACE__.'\\Types\\';

    private static TypeFinder $instance;

    private array $contentTypes = [];

    protected function __construct()
    {
    }

    public static function find(): TypeFinder
    {
        return self::$instance ?? new static();
    }

    public function registerType(string $typeFullyQualifiedClassName, ?string $typeName = null): TypeFinder
    {
        $classReflection = new ReflectionClass($typeFullyQualifiedClassName);

        $this->contentTypes[$typeName ?? $this->typeNameFromReflection($classReflection)] = $classReflection->getName();

        return $this;
    }

    public function byFile(SplFileInfo $file): string
    {
        if (1 !== preg_match('/^.*\.(.*)\.md$/', $file->getFileName())) {
            throw new DomainException(sprintf('Content file does not match required pattern. File %s', $file->getPathname()));
        }

        $typeName = $this->typeNameFromFile($file);

        return $this->contentTypes[$typeName] ?? $this->registerType(self::NAMESPACE_FOR_CONTENT_TYPES.$typeName)->byFile($file);
    }

    private function typeNameFromReflection(ReflectionClass $classReflection): string
    {
        return strtolower($classReflection->getShortName());
    }

    private function typeNameFromFile(SplFileInfo $file): string
    {
        $typeName = [];
        preg_match(self::FILE_NAME_PATTERN, $file->getFileName(), $typeName);

        return str_replace(['-', '_'], '', strtolower($typeName[1]));
    }
}
