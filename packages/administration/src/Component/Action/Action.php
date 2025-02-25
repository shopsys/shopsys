<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

final class Action extends AbstractAction
{
    use ActionRouteTrait;

    /**
     * @param string $name
     * @param string $label
     * @param string|null $icon
     * @return self
     */
    public static function create(string $name, string $label, ?string $icon = null): self
    {
        return new self($name, $label, $icon);
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return '@ShopsysAdministration/crud/inline/action.html.twig';
    }

    /**
     * @return array
     */
    protected function getTemplateParameters(): array
    {
        if ($this->openInNewTab === true) {
            $this->attributes['target'] = '_blank';
        }

        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'actionRoute' => $this->actionRoute,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    protected function getForbiddenAttributes(): array
    {
        return [
            ...$this->actionRouteForbiddenAttributes,
        ];
    }
}
