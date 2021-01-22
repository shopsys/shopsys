<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductOnCurrentDomainSqlFacadeCountDataTest extends ProductOnCurrentDomainFacadeCountDataTest
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainSqlFacade;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface
    {
        return $this->productOnCurrentDomainSqlFacade;
    }
}
