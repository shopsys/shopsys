<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use stdClass;

#[CrudController(stdClass::class)]
#[ForRole('ROLE_MODERATOR')]
class ModeratedReviewCrudController extends AbstractCrudController
{
}
