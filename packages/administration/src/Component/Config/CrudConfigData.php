<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use RuntimeException;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * @phpstan-import-type MenuItemPosition from \Shopsys\FrameworkBundle\Model\AdminNavigation\MenuItemPositioner
 */
final readonly class CrudConfigData
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType[] $enabledActions
     * @param MenuItemPosition $menuSectionPosition
     * @param array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, null|class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface>> $handlerClasses
     */
    public function __construct(
        private ?string $entityNameSingular,
        private ?string $entityNamePlural,
        private ?string $menuTitle,
        private string $entityName,
        private bool $fullDisabled,
        private array $enabledActions,
        private string $menuSection,
        private ?string $submenuSection,
        private string|array $menuSectionPosition,
        private bool $visibleInMenu,
        private ?string $routePrefix,
        private ?string $customRoleConstant,
        private ?string $customRoleSection,
        private array $handlerClasses,
        private ?string $menuIcon,
    ) {
        foreach ($this->enabledActions as $action) {
            if (array_key_exists($action->value, $this->handlerClasses) && $this->handlerClasses[$action->value] === null) {
                throw new RuntimeException(sprintf(
                    'Enabling "%s" action requires corresponding handler to be registered. Use "$crudConfig->registerHandler()" to register the required handler first.',
                    $action->value,
                ));
            }
        }
    }

    public function getTitle(ActionType $pageType, ?string $recordName = null): string
    {
        return match ($pageType) {
            ActionType::LIST => $this->getPluralEntityName(),
            ActionType::CREATE => $this->getSingularEntityName(),
            ActionType::EDIT, ActionType::DETAIL => implode(' · ', array_filter([
                $this->getSingularEntityName(),
                $recordName,
            ])),
            default => '',
        };
    }

    public function getBreadcrumbTitle(ActionType $pageType): string
    {
        return match ($pageType) {
            ActionType::CREATE => t('New record'),
            ActionType::EDIT => t('Editing a record'),
            ActionType::DETAIL => t('Record detail'),
            default => '',
        };
    }

    public function getMenuTitle(): string
    {
        if ($this->menuTitle !== null) {
            return $this->menuTitle;
        }

        return $this->getPluralEntityName();
    }

    private function getSingularEntityName(): string
    {
        if ($this->entityNameSingular !== null) {
            return $this->entityNameSingular;
        }

        return Translator::staticTrans(CrudTransformationHelper::toSingularEntityName($this->entityName));
    }

    private function getPluralEntityName(): string
    {
        if ($this->entityNamePlural !== null) {
            return $this->entityNamePlural;
        }

        return Translator::staticTrans(CrudTransformationHelper::toPluralEntityName($this->entityName));
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Config\ActionType[]
     */
    public function getActions(): array
    {
        if ($this->fullDisabled === true) {
            return [];
        }

        return $this->enabledActions;
    }

    public function isActionEnabled(ActionType $actionType): bool
    {
        return in_array($actionType, $this->enabledActions, true);
    }

    public function isFullDisabled(): bool
    {
        return $this->fullDisabled || count($this->enabledActions) === 0;
    }

    public function getMenuSection(): string
    {
        return $this->menuSection;
    }

    public function getSubmenuSection(): ?string
    {
        return $this->submenuSection;
    }

    /**
     * @return MenuItemPosition
     */
    public function getMenuSectionPosition(): string|array
    {
        return $this->menuSectionPosition;
    }

    public function isVisibleInMenu(): bool
    {
        return $this->visibleInMenu && $this->isActionEnabled(ActionType::LIST);
    }

    public function getRoutePrefix(): ?string
    {
        return $this->routePrefix;
    }

    public function getCustomRoleConstant(): ?string
    {
        return $this->customRoleConstant;
    }

    public function getCustomRoleSection(): ?string
    {
        return $this->customRoleSection;
    }

    /**
     * @return array<value-of<\Shopsys\AdministrationBundle\Component\Config\ActionType>, class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface>>
     */
    public function getHandlerClasses(): array
    {
        return $this->handlerClasses;
    }

    public function getMenuIcon(): ?string
    {
        return $this->menuIcon;
    }
}
