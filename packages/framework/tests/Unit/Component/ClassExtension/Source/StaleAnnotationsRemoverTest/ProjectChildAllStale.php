<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest;

/**
 * @property string $staleProperty
 * @method void staleMethod()
 */
class ProjectChildAllStale extends FrameworkParentClass
{
}
