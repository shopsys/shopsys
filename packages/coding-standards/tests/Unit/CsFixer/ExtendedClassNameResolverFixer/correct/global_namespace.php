<?php

declare(strict_types=1);

use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyFacade;

function dummy(string $value): string
{
    return DummyFacade::doSomething($value);
}
