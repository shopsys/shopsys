<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemFacade;

class SliderItemDataFixture extends AbstractReferenceFixture
{
    private const string UUID_NAMESPACE = 'fbef66ee-a418-4376-aa37-d02a8a12976a';

    /**
     * @param \App\Model\Slider\SliderItemFacade $sliderItemFacade
     * @param \App\Model\Slider\SliderItemDataFactory $sliderItemDataFactory
     */
    public function __construct(
        private readonly SliderItemFacade $sliderItemFacade,
        private readonly SliderItemDataFactoryInterface $sliderItemDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();

            /** @var \App\Model\Slider\SliderItemData $sliderItemData */
            $sliderItemData = $this->sliderItemDataFactory->create();
            $sliderItemData->domainId = $domainId;
            $sliderItemData->hidden = false;
            $sliderItemData->gtmId = 'sliderItemTest';
            $sliderItemData->sliderExtendedText = t('Terms of promotion', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->sliderExtendedTextLink = 'https://www.shopsys.cz';
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Terms of promotion' . $domainId)->toString();

            $sliderItemData->name = 'Shopsys';
            $sliderItemData->link = 'https://www.shopsys.cz';

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('Documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->link = 'https://docs.shopsys.com';
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Documentation' . $domainId)->toString();

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('Become one of us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->link = 'https://jobs.shopsys.cz';
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Become one of us' . $domainId)->toString();

            $this->sliderItemFacade->create($sliderItemData);
        }
    }
}
