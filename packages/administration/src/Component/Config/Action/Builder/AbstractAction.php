<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\Action\ActionData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;

abstract class AbstractAction
{
    protected ?string $label = null;

    protected ?string $icon = null;

    protected string $cssClass = '';

    protected ?ActionRouteInterface $actionRoute = null;

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
     * @param string $name
     * @param string $label
     */
    protected function __construct(protected string $name, string $label)
    {
        $this->label = $label;
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
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\ActionData
     */
    public function getData(): ActionData
    {
        return new ActionData(
            $this->name,
            $this->label,
            $this->icon,
            $this->cssClass,
            $this->openInNewTab,
            $this->actionRoute,
            $this->displayIf,
        );
    }
}
