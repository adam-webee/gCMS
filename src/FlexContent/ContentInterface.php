<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent;

interface ContentInterface
{
    /**
     * @var string AUTHOR
     */
    public const AUTHOR = 'author';

    /**
     * @var string TITLE
     */
    public const TITLE = 'title';

    /**
     * @var string SLUG
     */
    public const SLUG = 'slug';

    /**
     * @var string TAGS
     */
    public const TAGS = 'tags';

    /**
     * @var string CATEGORIES
     */
    public const CATEGORIES = 'categories';

    /**
     * @var string EXCERPT
     */
    public const EXCERPT = 'excerpt';

    /**
     * @var string CREATE_DATE
     */
    public const CREATE_DATE = 'createDate';

    /**
     * @var string MENU_ITEM_NUMBER
     */
    public const MENU_ITEM_NUMBER = 'menuItemNumber';

    /**
     * @var string SKIP_FROM_LISTING
     */
    public const SKIP_FROM_LISTING = 'skipFromListings';

    /**
     * Returns contents' slug.
     *
     * Slug must be composed from url safe characters:
     *  - letters: a-z,A-Z;
     *  - numbers: 0-9;
     *  - special characters: - (hyphen), _ (underscore), or / (backslash);
     *
     * @see https://en.wikipedia.org/wiki/Clean_URL#Slug What is slug
     *
     * @param string|null $slug if provided method will work like setter and will set provided slug
     */
    public function slug(?string $slug = null): string;

    /**
     * Adds new content of requested type to current content instance.
     *
     * @param string       $rawContent     Raw (un-rendered) content
     * @param array<mixed> $additionalData Any additional data that content might need (e.g. additional configuration, etc.)
     * @param string       $relation       relation type
     * @param string|null  $typeName       [opt] Name of content type, must be a class name that represents it. By default same as current class.
     *
     * @return ContentInterface This new content instance
     */
    public function loadPart(
        string $rawContent,
        array $additionalData = [],
        string $relation = ContentRelationInterface::RELATION_CHILD,
        ?string $typeName = null
    ): self;

    /**
     * Loads raw (un-rendered) content.
     *
     * @param array<mixed> $additionalData Any additional data that content might need (e.g. additional configuration, etc.)
     */
    public function load(string $rawContent, array $additionalData = []): self;

    /**
     * Universal content getter. Can get anything that content want to give.
     *
     * @param string|null $whatToGet        name of that what must be returned
     * @param mixed|null  $valueIfNotExists value that will be returned if requested element does not exists
     */
    public function get(?string $whatToGet = null, $valueIfNotExists = null): mixed;

    /**
     * Render content to its string representation.
     */
    public function __toString(): string;
}
