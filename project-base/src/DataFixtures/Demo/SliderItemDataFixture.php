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
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory $sliderItemDataFactory
     */
    public function __construct(
        private readonly SliderItemFacade $sliderItemFacade,
        private readonly SliderItemDataFactoryInterface $sliderItemDataFactory
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $sliderItemData = $this->sliderItemDataFactory->create();
        $sliderItemData->domainId = Domain::FIRST_DOMAIN_ID;

        $sliderItemData->name = 'Shopsys';
        $sliderItemData->link = 'https://www.shopsys.cz';
        $sliderItemData->hidden = false;

        $this->sliderItemFacade->create($sliderItemData);

        $sliderItemData->name = 'Twitter';
        $sliderItemData->link = 'https://twitter.com/ShopsysFW';

        $this->sliderItemFacade->create($sliderItemData);

        $sliderItemData->name = 'Pojďte s námi růst';
        $sliderItemData->link = 'https://jobs.shopsys.cz';

        $this->sliderItemFacade->create($sliderItemData);
    }
}
