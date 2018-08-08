<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

class Column
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $sourceColumnName;

    /**
     * @var string
     */
    private $title;

    /**
     * @var bool
     */
    private $sortable;

    /**
     * @var string
     */
    private $classAttribute;

    /**
     * @var string
     */
    private $orderSourceColumnName;

    /**
     * @param string $id
     * @param string $sourceColumnName
     * @param string $title
     * @param bool $sortable
     */
    public function __construct($id, $sourceColumnName, $title, $sortable)
    {
        $this->id = $id;
        $this->sourceColumnName = $sourceColumnName;
        $this->title = $title;
        $this->sortable = $sortable;
        $this->classAttribute = '';
        $this->orderSourceColumnName = $sourceColumnName;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSourceColumnName(): string
    {
        return $this->sourceColumnName;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getClassAttribute(): string
    {
        return $this->classAttribute;
    }

    /**
     * @param string $class
     */
    public function setClassAttribute($class): \Shopsys\FrameworkBundle\Component\Grid\Column
    {
        $this->classAttribute = $class;

        return $this;
    }

    public function getOrderSourceColumnName(): string
    {
        return $this->orderSourceColumnName;
    }
}
