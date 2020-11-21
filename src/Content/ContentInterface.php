<?php

declare(strict_types=1);

namespace WeBee\gCMS\Content;

interface ContentInterface
{
    public const TITLE = 'title';
    public const SLUG = 'slug';
    public const TAGS = 'tags';
    public const CATEGORIES = 'categories';

    /**
     * Returns associative array of contents' attributes like title, slug, author etc.
     * Attribute name will serve as key.
     *
     * @return array<mixed>
     */
    public function attributes(): array;

    /**
     * Returns contents' title.
     *
     * @return null|string
     */
    public function title(): ?string;

    /**
     * Returns contents' slug.
     *
     * Slug must be composed from url safe characters:
     *  - letters: a-z, A-Z;
     *  - numbers: 0-9;
     *  - special characters: - (hyphen), _ (underscore), / (backslash);
     *
     * @see https://en.wikipedia.org/wiki/Clean_URL#Slug What is slug
     *
     * @return null|string
     */
    public function slug(): string;

    /**
     * Returns list of contents' related tags.
     *
     * @return array<string>
     */
    public function tags(): array;

    /**
     * Returns list of contents' related categories.
     *
     * @return array<string>
     */
    public function categories(): array;

    /**
     * Returns contents' content.
     *
     * @return string
     */
    public function content(): string;
}
