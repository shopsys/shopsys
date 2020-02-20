<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\PromoCode\PromoCodeCategoryFactory;
use App\Model\Order\PromoCode\PromoCodeProductFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;

class PromoCodeDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    protected $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeDataFactory
     */
    protected $promoCodeDataFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductFactory
     */
    private $promoCodeProductFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryFactory
     */
    private $promoCodeCategoryFactory;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     * @param \App\Model\Order\PromoCode\PromoCodeProductFactory $promoCodeProductFactory
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryFactory $promoCodeCategoryFactory
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        PromoCodeDataFactoryInterface $promoCodeDataFactory,
        PromoCodeProductFactory $promoCodeProductFactory,
        PromoCodeCategoryFactory $promoCodeCategoryFactory
    ) {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->promoCodeDataFactory = $promoCodeDataFactory;
        $this->promoCodeProductFactory = $promoCodeProductFactory;
        $this->promoCodeCategoryFactory = $promoCodeCategoryFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test';
        $promoCodeData->percent = 10.0;
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCode = $this->promoCodeFacade->create($promoCodeData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->promoCodeProductFactory->create($promoCode, $product);

        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_FOOD);
        $this->promoCodeCategoryFactory->create($promoCode, $category);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            CategoryDataFixture::class,
        ];
    }
}
