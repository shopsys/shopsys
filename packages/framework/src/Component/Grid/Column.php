<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

class Column
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $sourceColumnName;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $sortable;

    /**
     * @var string
     */
    protected $classAttribute;

    /**
     * @var string
     */
    protected $orderSourceColumnName;

    /**
     * @param string $id
     * @param string $sourceColumnName
     * @param string $title
     * @param bool $sortable
     */
    public function __construct(string $id, string $sourceColumnName, string $title, bool $sortable)
    {
        $this->id = $id;
        $this->sourceColumnName = $sourceColumnName;
        $this->title = $title;
        $this->sortable = $sortable;
        $this->classAttribute = '';
        $this->orderSourceColumnName = $sourceColumnName;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceColumnName(): string
    {
        return $this->sourceColumnName;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @return string
     */
    public function getClassAttribute(): string
    {
        return $this->classAttribute;
    }

    /**
     * @param string $class
     * @return \Shopsys\FrameworkBundle\Component\Grid\Column
     */
    public function setClassAttribute(string $class): self
    {
        $this->classAttribute = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderSourceColumnName(): string
    {
        return $this->orderSourceColumnName;
    }
}
