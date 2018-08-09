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

    public function getId()
    {
        return $this->id;
    }

    public function getSourceColumnName()
    {
        return $this->sourceColumnName;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function getClassAttribute()
    {
        return $this->classAttribute;
    }

    /**
     * @param string $class
     * @return \Shopsys\FrameworkBundle\Component\Grid\Column
     */
    public function setClassAttribute($class)
    {
        $this->classAttribute = $class;

        return $this;
    }

    public function getOrderSourceColumnName()
    {
        return $this->orderSourceColumnName;
    }
}
