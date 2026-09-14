<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Shopsys\AdministrationBundle\Component\Crud\Handler\CreateHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\DeleteHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\EditHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Handler\HandlerInterface;
use Webmozart\Assert\Assert;

enum ActionType: string
{
    case LIST = 'list';
    case DETAIL = 'detail';
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';

    public function isSubMenuRouteItem(): bool
    {
        return match ($this) {
            self::DETAIL, self::CREATE, self::EDIT => true,
            self::LIST, self::DELETE => false,
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
