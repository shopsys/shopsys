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
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactoryInterface $sliderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        SliderItemFacade $sliderItemFacade,
        SliderItemDataFactoryInterface $sliderItemDataFactory,
        Domain $domain
    ) {
        $this->sliderItemFacade = $sliderItemFacade;
        $this->sliderItemDataFactory = $sliderItemDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

            /** @var \App\Model\Slider\SliderItemData $sliderItemData */
            $sliderItemData = $this->sliderItemDataFactory->create();
            $sliderItemData->domainId = $domainId;
            $sliderItemData->hidden = false;
            $sliderItemData->gtmId = 'sliderItemTest';
            $sliderItemData->sliderExtendedText = t('Pravidla akce', [], 'dataFixtures', $locale);
            $sliderItemData->sliderExtendedTextLink = 'https://www.shopsys.cz';

            $sliderItemData->name = t('40% SLEVA NA ÚLOŽNÉ PROSTORY', [], 'dataFixtures', $locale);
            $sliderItemData->link = 'https://www.shopsys.cz';

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('40% SLEVA NA POSTELE, MATRACE A ROŠTY', [], 'dataFixtures', $locale);
            $sliderItemData->link = 'https://shopsys.cz';

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('SLEVA 20% + 21% DPH NAVÍC', [], 'dataFixtures', $locale);
            $sliderItemData->link = 'https://shopsys.cz';

            $this->sliderItemFacade->create($sliderItemData);
        }
    }
}
