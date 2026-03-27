<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest;

/**
 * @property string $validProperty
 * @method void validMethod()
 */
class ProjectChildAllValid extends FrameworkParentClass
{
}
