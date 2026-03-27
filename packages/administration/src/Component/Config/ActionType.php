<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Shopsys\AdministrationBundle\Component\Crud\Handler\CreateHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\DeleteHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\EditHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Webmozart\Assert\Assert;

enum ActionType: string
{
    case LIST = 'list';
    case DETAIL = 'detail';
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';

    public function toPermission(): Permission
    {
        return match ($this) {
            self::LIST, self::DETAIL => Permission::VIEW,
            self::CREATE => Permission::CREATE,
            self::EDIT => Permission::EDIT,
            self::DELETE => Permission::DELETE,
        };
    }

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Security\Attribute\PermissionAttributeInterface>
     */
    public function toAccessControlRules(): array
    {
        return match ($this) {
            self::LIST, self::DETAIL => [new CanView()],
            self::CREATE => [new CanCreate()],
            self::EDIT => [
                new CanEdit(methods: [HttpMethod::POST]),
                new CanView(methods: [HttpMethod::GET]),
            ],
            self::DELETE => [new CanDelete()],
        };
    }

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface> $handlerClass
     * @return array<\Shopsys\AdministrationBundle\Component\Config\ActionType>
     */
    public static function getActionsForHandlerClass(string $handlerClass): array
    {
        Assert::implementsInterface($handlerClass, HandlerInterface::class);
        $interfaces = class_implements($handlerClass);

        $actionsForHandlerInterface = [
            DeleteHandlerInterface::class => [self::DELETE],
            EditHandlerInterface::class => [self::EDIT],
            CreateHandlerInterface::class => [self::CREATE],
            CrudHandlerInterface::class => [self::DELETE, self::EDIT, self::CREATE],
        ];

        $actions = [];

        foreach ($interfaces as $interface) {
            if (isset($actionsForHandlerInterface[$interface])) {
                $actions = array_merge($actions, $actionsForHandlerInterface[$interface]);
            }
        }

        return array_values(array_unique($actions, flags: SORT_REGULAR));
    }
}
