<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use stdClass;

/**
 * Inherits the ForRole attribute of its parent
 */
#[CrudController(stdClass::class)]
class InheritedModeratedReviewCrudController extends ModeratedReviewCrudController
{
}
