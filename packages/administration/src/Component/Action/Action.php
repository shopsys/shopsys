<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

use Override;

final class Action extends AbstractRoutableAction
{
    /**
     * @param string $name
     * @param string $label
     * @param string|null $icon
     * @return self
     */
    #[Override]
    public static function create(string $name, string $label, ?string $icon = null): self
    {
        return new self($name, $label, $icon);
    }

    /**
     * @return string
     */
    #[Override]
    protected function getTemplate(): string
    {
        return '@ShopsysAdministration/crud/inline/action.html.twig';
    }

    /**
     * @return array
     */
    #[Override]
    protected function getTemplateParameters(): array
    {
        $this->prepareRoutableAttributes();

        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'actionRoute' => $this->actionRoute,
        ];
    }
}
