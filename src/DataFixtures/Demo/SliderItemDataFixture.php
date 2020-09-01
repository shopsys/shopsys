<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;

class SliderItemDataFixture extends AbstractReferenceFixture
{
    /**
     * @var \App\Model\Slider\SliderItemFacade
     */
    private $sliderItemFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactoryInterface
     */
    private $sliderItemDataFactory;

    /**
     * @param \App\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactoryInterface $sliderItemDataFactory
     */
    public function __construct(
        SliderItemFacade $sliderItemFacade,
        SliderItemDataFactoryInterface $sliderItemDataFactory
    ) {
        $this->sliderItemFacade = $sliderItemFacade;
        $this->sliderItemDataFactory = $sliderItemDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var \App\Model\Slider\SliderItemData $sliderItemData */
        $sliderItemData = $this->sliderItemDataFactory->create();
        $sliderItemData->domainId = Domain::FIRST_DOMAIN_ID;
        $sliderItemData->hidden = false;
        $sliderItemData->gtmId = 'sliderItemTest';
        $sliderItemData->sliderExtendedText = 'Pravidla akce';
        $sliderItemData->sliderExtendedTextLink = 'https://www.sconto.cz';

        $sliderItemData->name = '40% SLEVA NA ÚLOŽNÉ PROSTORY';
        $sliderItemData->link = 'https://www.sconto.cz/skrine-a-komody';

        $this->sliderItemFacade->create($sliderItemData);

        $sliderItemData->name = '40% SLEVA NA POSTELE, MATRACE A ROŠTY';
        $sliderItemData->link = 'https://prod.scontodev.cz/matrace-a-rosty';

        $this->sliderItemFacade->create($sliderItemData);

        $sliderItemData->name = 'SLEVA 20% + 21% DPH NAVÍC';
        $sliderItemData->link = 'https://prod.scontodev.cz/';

        $this->sliderItemFacade->create($sliderItemData);

        $sliderItemData->name = '40% na jídelní stoly a židle';
        $sliderItemData->link = 'https://prod.scontodev.cz/jidelni-sety-zidle-a-stul';

        $this->sliderItemFacade->create($sliderItemData);

        $sliderItemData->name = 'Informace o otevírací době';
        $sliderItemData->link = 'https://prod.eshop.scontodev.sk/';
        $sliderItemData->domainId = Domain::SECOND_DOMAIN_ID;

        $this->sliderItemFacade->create($sliderItemData);
    }
}
