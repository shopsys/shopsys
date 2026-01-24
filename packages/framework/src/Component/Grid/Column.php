<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

class Column
{
    protected string $classAttribute;

    protected string $orderSourceColumnName;

    protected ?string $template;

    protected ?string $help;

    /**
     * @param array{ template?: string, help?: string }&array<string, mixed> $options
     */
    public function __construct(
        protected string $id,
        protected string $sourceColumnName,
        protected string $title,
        protected bool $sortable,
        array $options = [],
    ) {
        $this->classAttribute = '';
        $this->orderSourceColumnName = $sourceColumnName;
        $this->template = $options['template'] ?? null;
        $this->help = $options['help'] ?? null;
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

    public function setClassAttribute(string $class): self
    {
        $this->classAttribute = $class;

        return $this;
    }

    public function getOrderSourceColumnName(): string
    {
        return $this->orderSourceColumnName;
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }
}
