<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Symfony\Component\HttpFoundation\Response;

/**
 * A custom action without any access control attribute, which must be rejected
 */
#[CrudAction(crudController: ReviewCrudController::class, name: 'publish')]
class UnguardedReviewAction
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
