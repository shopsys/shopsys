<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\PromoCode\PromoCodeCategoryFactory;
use App\Model\Order\PromoCode\PromoCodeLimitFactory;
use App\Model\Order\PromoCode\PromoCodeProductFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;

class PromoCodeDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeDataFactory
     */
    private $promoCodeDataFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductFactory
     */
    private $promoCodeProductFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeCategoryFactory
     */
    private $promoCodeCategoryFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitFactory
     */
    private PromoCodeLimitFactory $promoCodeLimitFactory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     * @param \App\Model\Order\PromoCode\PromoCodeProductFactory $promoCodeProductFactory
     * @param \App\Model\Order\PromoCode\PromoCodeCategoryFactory $promoCodeCategoryFactory
     * @param \App\Model\Order\PromoCode\PromoCodeLimitFactory $promoCodeLimitFactory
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        PromoCodeDataFactoryInterface $promoCodeDataFactory,
        PromoCodeProductFactory $promoCodeProductFactory,
        PromoCodeCategoryFactory $promoCodeCategoryFactory,
        PromoCodeLimitFactory $promoCodeLimitFactory,
        EntityManagerInterface $em
    ) {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->promoCodeDataFactory = $promoCodeDataFactory;
        $this->promoCodeProductFactory = $promoCodeProductFactory;
        $this->promoCodeCategoryFactory = $promoCodeCategoryFactory;
        $this->promoCodeLimitFactory = $promoCodeLimitFactory;
        $this->em = $em;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test';
        $promoCodeData->domainId = Domain::FIRST_DOMAIN_ID;
        $promoCodeData->identifier = 'GG';
        $promoCode = $this->promoCodeFacade->create($promoCodeData);

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->promoCodeProductFactory->create($promoCode, $product);

        /** @var \App\Model\Category\Category $category */
        $category = $this->getReference(CategoryDataFixture::CATEGORY_FOOD);
        $this->promoCodeCategoryFactory->create($promoCode, $category);

        $promoCodeLimit = $this->promoCodeLimitFactory->create('1.0', '10');
        $promoCodeLimit->setPromoCode($promoCode);
        $this->em->persist($promoCodeLimit);
        $this->em->flush();
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
