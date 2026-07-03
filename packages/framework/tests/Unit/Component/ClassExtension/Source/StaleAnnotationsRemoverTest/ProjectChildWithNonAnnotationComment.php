<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest;

/**
 * This is a description of the class.
 *
 * @method void staleMethod()
 * @property string $validProperty
 */
class ProjectChildWithNonAnnotationComment extends FrameworkParentClass
{
}
