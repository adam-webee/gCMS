<?php

/**
 * This file is part of the gCMS package. For full copyright and licence information,
 * please view the LICENCE file that was distributed with this source code.
 *
 * (c) Adam Wojciechowski <adam@webee.online>
 */

declare(strict_types=1);

namespace WeBee\gCMS\FlexContent;

interface ContentRelationInterface
{
    /**
     * Describe relation between two contents that are somehow connected but not as parent and child.
     *
     * @var string RELATION_RELATED
     */
    public const RELATION_RELATED = 'related';

    /**
     * Describe relation from parent content to its descendants - children.
     *
     * @var string RELATION_CHILD
     */
    public const RELATION_CHILD = 'child';

    /**
     * Describe relation from parent content to its descendants - technical children.
     *
     * @var string RELATION_CHILD
     */
    public const RELATION_TECH_CHILD = 'techChild';

    /**
     * Describes relation from descendant (child) content to its parent.
     *
     * @var string RELATION_PARENT
     */
    public const RELATION_PARENT = 'parent';

    /**
     * Sets contents' parent.
     */
    public function setParent(ContentInterface $parentContent): self;

    /**
     * Gets contents' parent.
     *
     * return null|ContentInterface Returns parent or null if content have no parent
     */
    public function getParent(): ?ContentInterface;

    /**
     * Appends child to contents' list of children.
     */
    public function appendChild(ContentInterface $childContent): self;

    /**
     * Gets all contents' children.
     *
     * @return array<ContentInterface>
     */
    public function getChildren(): array;

    /**
     * Appends related content.
     */
    public function appendRelated(ContentInterface $relatedContent): self;

    /**
     * Gets contents' related content.
     *
     * @return array<ContentInterface>
     */
    public function getRelated(): array;

    /**
     * Gets all contents' related content inc. parent and children.
     *
     * @return array<ContentInterface>
     */
    public function getAll(): array;
}
