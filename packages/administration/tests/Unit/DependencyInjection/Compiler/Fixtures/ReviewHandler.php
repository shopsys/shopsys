<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use stdClass;

/**
 * Registered in test configurations so all built-in actions can be enabled
 */
class ReviewHandler implements CrudHandlerInterface
{
    #[Override]
    public function getById(int $id): Presentable
    {
        return new class() implements Presentable {
            public function toHumanReadable(): string
            {
                return 'review';
            }
        };
    }

    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        return new stdClass();
    }

    #[Override]
    public function edit(object $entity, object $data): void
    {
    }

    #[Override]
    public function createData(): object
    {
        return new stdClass();
    }

    #[Override]
    public function create(object $data): Presentable
    {
        return $this->getById(0);
    }

    #[Override]
    public function delete(Presentable $entity): void
    {
    }
}
