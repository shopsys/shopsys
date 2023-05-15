<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest;

use Shopsys\FrameworkBundle\Model\Category\Category;

class BaseClass5
{
    public const DEFAULT_VALUE = 'default';

    /**
     * This method accepts parameter with type that is registered in the class extension map and hence the "@method" annotation must be added to the child class
     *
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     */
    public function setCategory(?Category $category = null)
    {
    }

    /**
     * This method accepts parameter with type that is registered in the class extension map and hence the "@method" annotation must be added to the child class
     *
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param string $string
     * @param string $constant
     * @param bool $true
     * @param bool $false
     * @param ?string $null
     * @param mixed[] $emptyArray
     */
    public function setCategoryWithStringWithDefaultParameters(
        Category $category,
        string $string = 'default',
        string $constant = self::DEFAULT_VALUE,
        bool $true = true,
        bool $false = false,
        ?string $null = null,
        array $emptyArray = [],
    ) {
    }
}
