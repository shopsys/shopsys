<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\StaleAnnotationsRemoverTest;

class FrameworkParentClass
{
    protected string $validProperty;

    public function validMethod(): void
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    public function getCategory()
    {
    }
}
