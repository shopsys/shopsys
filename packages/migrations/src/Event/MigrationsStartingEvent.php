<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched by shopsys:migrations:migrate inside the migration transaction, before any migration is executed.
 * Listeners can remove database objects that would block the migrations (e.g. views depending on migrated columns).
 */
class MigrationsStartingEvent extends Event
{
}
