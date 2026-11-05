<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture;

use Doctrine\DBAL\Types\StringType;

/**
 * A Doctrine type of a project storing text.
 */
final class DummyTextType extends StringType
{
    public const string NAME = 'dummy_text';
}
