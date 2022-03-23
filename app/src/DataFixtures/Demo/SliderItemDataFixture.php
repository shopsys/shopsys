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
     * @var string[]
     */
    private array $uuidPool = [
        'fbef66ee-a418-4376-aa37-d02a8a12976a',
        'e5dad6f2-8912-4ff0-9c1e-f25741172e74',
        'e4a3b9d3-e343-4739-b2ab-433adfe3a3fc',
        '82490a96-ebb0-495d-ad7e-196835f6ea26',
        '4d13b242-6174-455f-8566-43bc02df266b',
        '86005368-6963-4645-80be-e3ae36aaae60',
        '0b85c702-a08a-4862-82a9-03cb364632f6',
        '82b7659c-a3d4-4912-a342-c7003306dd4a',
        '11ac7864-7c29-4f50-802a-2c4662d15c07',
        '16491f92-746f-43f1-9cba-7aebc718ca97',
        'd9fd8c36-4d39-4331-a656-1805a2f89b45',
        '85a00b20-bb49-49a5-90ad-193b3dbd7982',
    ];

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
            $sliderItemData->uuid = array_pop($this->uuidPool);
            $sliderItemData->domainId = $domainId;
            $sliderItemData->hidden = false;
            $sliderItemData->gtmId = 'sliderItemTest';
            $sliderItemData->sliderExtendedText = t('Pravidla akce', [], 'dataFixtures', $locale);
            $sliderItemData->sliderExtendedTextLink = 'https://www.shopsys.cz';

            $sliderItemData->name = t('40% SLEVA NA ÚLOŽNÉ PROSTORY', [], 'dataFixtures', $locale);
            $sliderItemData->link = 'https://www.shopsys.cz';

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->uuid = array_pop($this->uuidPool);
            $sliderItemData->name = t('40% SLEVA NA POSTELE, MATRACE A ROŠTY', [], 'dataFixtures', $locale);
            $sliderItemData->link = 'https://shopsys.cz';

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->uuid = array_pop($this->uuidPool);
            $sliderItemData->name = t('SLEVA 20% + 21% DPH NAVÍC', [], 'dataFixtures', $locale);
            $sliderItemData->link = 'https://shopsys.cz';

            $this->sliderItemFacade->create($sliderItemData);
        }
    }
}
