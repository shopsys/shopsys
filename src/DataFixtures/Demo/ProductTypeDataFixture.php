<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Type\ProductTypeFacade;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class ProductTypeDataFixture extends AbstractReferenceFixture
{
    public const TYPE_COMMON = 'product_type_common';
    public const TYPE_OVERSIZED = 'product_type_oversized';

    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

    /**
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     */
    public function __construct(
        ProductTypeFacade $productTypeFacade
    ) {
        $this->productTypeFacade = $productTypeFacade;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {

        /**
         * Default product types are created in database migration.
         * @see \App\Migrations\Version20200214104810
         */
        $commonProductType = $this->productTypeFacade->findByAkeneoCode('zasilka_2');
        $oversizedProductType = $this->productTypeFacade->findByAkeneoCode('zasilka_1');
        $this->addReference(self::TYPE_COMMON, $commonProductType);
        $this->addReference(self::TYPE_OVERSIZED, $oversizedProductType);
    }
}
