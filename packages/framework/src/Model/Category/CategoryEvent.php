<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Symfony\Contracts\EventDispatcher\Event;

class CategoryEvent extends Event
{
    /**
     * The CREATE event occurs once a category was created.
     *
     * This event allows you to run jobs dependent on the category creation.
     */
    public const CREATE = 'category.create';
    /**
     * The UPDATE event occurs once a category was changed.
     *
     * This event allows you to run jobs dependent on the category change.
     */
    public const UPDATE = 'category.update';
    /**
     * The DELETE event occurs once a category was deleted.
     *
     * This event allows you to run jobs dependent on the category deletion.
     */
    public const DELETE = 'category.delete';

    public function __construct(
        protected readonly Category $category,
    ) {
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
