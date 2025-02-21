<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use Closure;

abstract class AbstractAction
{
    protected ?string $label = null;

    protected ?string $icon = null;

    protected string $cssClass = '';

    /**
     * @var null|\Closure(?object $entity): bool
     */
    protected ?Closure $displayIf = null;

    /**
     * @param string $name
     * @param string $label
     * @return $this
     */
    abstract public static function create(string $name, string $label): self;

    /**
     * @return string
     */
    abstract protected function getTemplate(): string;

    /**
     * @param object|null $entity
     * @return array<string, mixed>
     */
    abstract protected function getTemplateParameters(?object $entity): array;

    /**
     * @param string $name
     * @param string $label
     */
    protected function __construct(protected string $name, string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name of action that will be shown to the users
     *
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set icon of action that will be shown next to label
     *
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set CSS class that will be added to action button
     *
     * @param string $cssClass
     * @return $this
     */
    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    /**
     * Set function that will determine if action should be displayed
     *
     * @param \Closure(?object $entity): bool $function Function must return boolean value. If function returns false, action will not be displayed
     * @return $this
     */
    public function displayIf(Closure $function): self
    {
        $this->displayIf = $function;

        return $this;
    }

    /**
     * @param object|null $entity
     * @return array|null
     */
    public function renderData(?object $entity): ?array
    {
        if ($this->displayIf !== null && call_user_func($this->displayIf, $entity) === false) {
            return null;
        }

        return [
            'template' => $this->getTemplate(),
            'parameters' => $this->getTemplateParameters($entity),
        ];
    }
}
