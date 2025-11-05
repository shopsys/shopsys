<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Handler;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Base marker interface for all CRUD handlers
 *
 * Do not implement this interface directly. Instead, implement one of the extended interfaces
 *
 * @internal
 */
#[AutoconfigureTag('shopsys.admin.crud_handler')]
interface HandlerInterface
{
}
