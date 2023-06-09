<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ProductOnCurrentDomainElasticFacadeCountDataTest extends ProductOnCurrentDomainFacadeCountDataTest
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    public function getProductOnCurrentDomainFacade(): ProductOnCurrentDomainFacadeInterface
    {
        return $this->productOnCurrentDomainElasticFacade;
    }
}
