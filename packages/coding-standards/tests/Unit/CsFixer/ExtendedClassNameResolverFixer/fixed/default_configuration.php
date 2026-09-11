<?php

declare(strict_types=1);

namespace App\Model;

use Shopsys\Cli\Config\CoreDomainConfigValidator;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;

class Example
{
    public function run(): void
    {
        ExtendedClassNameResolver::resolve(ProductFacade::class)::doSomething();
        Money::create(1);
        CoreDomainConfigValidator::validateLocale('cs');
    }
}
