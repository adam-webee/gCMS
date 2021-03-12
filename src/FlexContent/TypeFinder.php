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
        self::$instance ??= new static();

        return self::$instance;
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

        $className = $this->classNameFromFile($file);
        $typeName = strtolower($className);

        return $this->contentTypes[$typeName] ?? $this->registerType($this->getNamespace().$className)->byFile($file);
    }

    private function typeNameFromReflection(ReflectionClass $classReflection): string
    {
        return strtolower($classReflection->getShortName());
    }

    private function classNameFromFile(SplFileInfo $file): string
    {
        $className = [];
        preg_match(self::FILE_NAME_PATTERN, $file->getFileName(), $className);
        $className = str_replace(['-', '_'], ' ', strtolower($className[1]));

        return str_replace(' ', '', ucwords($className));
    }

    private function getNamespace(): string
    {
        return self::NAMESPACE_FOR_CONTENT_TYPES;
    }
}
