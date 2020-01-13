<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;

class PromoCodeDataFixture extends AbstractReferenceFixture
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
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     */
    public function __construct(
        PromoCodeFacade $promoCodeFacade,
        PromoCodeDataFactoryInterface $promoCodeDataFactory
    ) {
        $this->promoCodeFacade = $promoCodeFacade;
        $this->promoCodeDataFactory = $promoCodeDataFactory;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $promoCodeData = $this->promoCodeDataFactory->create();
        $promoCodeData->code = 'test';
        $promoCodeData->percent = 10.0;
        $promoCodeData->domainId = 1;
        $this->promoCodeFacade->create($promoCodeData);
    }
}
