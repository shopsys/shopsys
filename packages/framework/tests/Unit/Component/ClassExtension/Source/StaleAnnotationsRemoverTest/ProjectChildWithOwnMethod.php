<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest;

/**
 * @method void ownMethod()
 * @method void staleMethod()
 */
class ProjectChildWithOwnMethod extends FrameworkParentClass
{
    public function ownMethod(): void
    {
    }
}
