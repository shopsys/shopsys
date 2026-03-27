<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest;

/**
 * @property string $validProperty
 * @property string $staleProperty
 * @method void validMethod()
 * @method \App\Model\Category\CategoryFacade getCategory()
 * @method void staleMethod()
 */
class ProjectChildClass extends FrameworkParentClass
{
}
