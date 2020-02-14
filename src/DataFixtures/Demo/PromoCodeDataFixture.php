<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Order\PromoCode\PromoCodeProductFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

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
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;
    /**
     * @var \App\Model\Order\PromoCode\PromoCodeProductFactory
     */
    private $promoCodeProductFactory;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        PromoCodeDataFactoryInterface $promoCodeDataFactory,
        ProductRepository $productRepository,
        PromoCodeProductFactory $promoCodeProductFactory
    ) {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->promoCodeDataFactory = $promoCodeDataFactory;
        $this->productRepository = $productRepository;
        $this->promoCodeProductFactory = $promoCodeProductFactory;
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
        $product = $this->productRepository->getById(1);
        $this->promoCodeProductFactory->create($promoCode, $product);


    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
