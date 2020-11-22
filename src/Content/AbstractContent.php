<?php

declare(strict_types=1);

namespace WeBee\gCMS\Content;

abstract class AbstractContent implements ContentInterface
{
    /**
     * @var array<mixed> $attributes Contents' attributes [
     *  @var null|string ContentInterface::TITLE
     *  @var string ContentInterface::SLUG
     *  @var array<string> ContentInterface::TAGS
     *  @var array<string> ContentInterface::CATEGORIES
     * ]
     */
    protected $attributes = [
        ContentInterface::TITLE => null,
        ContentInterface::SLUG => '',
        ContentInterface::TAGS => [],
        ContentInterface::CATEGORIES => [],
    ];

    /**
     * @var string $content Contents' content
     */
    protected $content = '';

    public function __construct()
    {
        $this->parse();
    }

    /**
     * @inheritDoc
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * @inheritDoc
     */
    public function title(): ?string
    {
        return $this->attributes[ContentInterface::TITLE];
    }

    /**
     * @inheritDoc
     */
    public function excerpt(): ?string
    {
        return $this->attributes[ContentInterface::EXCERPT];
    }

    /**
     * @inheritDoc
     */
    public function author(): ?string
    {
        return array_key_exists(ContentInterface::AUTHOR, $this->attributes)
            ? $this->attributes[ContentInterface::AUTHOR]
            : null
        ;
    }

    /**
     * @inheritDoc
     */
    public function slug(): string
    {
        return $this->attributes[ContentInterface::SLUG];
    }

    /**
     * @inheritDoc
     */
    public function tags(): array
    {
        return $this->attributes[ContentInterface::TAGS];
    }

    /**
     * @inheritDoc
     */
    public function categories(): array
    {
        return $this->attributes[ContentInterface::CATEGORIES];
    }

    /**
     * @inheritDoc
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Must be called in constructor to parse out provided input into valid content.
     */
    abstract protected function parse();
}
