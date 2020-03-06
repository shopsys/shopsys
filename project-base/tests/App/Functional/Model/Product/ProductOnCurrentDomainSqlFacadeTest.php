<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductOnCurrentDomainSqlFacadeTest extends ProductOnCurrentDomainFacadeTest
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade
     * @inject
     */
    private $productOnCurrentDomainFacade;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface
    {
        return $this->productOnCurrentDomainFacade;
    }
}
