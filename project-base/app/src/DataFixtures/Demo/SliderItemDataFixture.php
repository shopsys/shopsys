<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Slider\SliderItemDataFactory;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
        private readonly SliderItemDataFactory $sliderItemDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
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
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Terms of promotion' . $domainId)->toString();

            $sliderItemData->name = 'Shopsys';
            $sliderItemData->link = 'https://www.shopsys.cz';
            $sliderItemData->description = t('This slider item promotes our latest offers and updates. Stay tuned for more exciting news and promotions. Click the link to learn more!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->rgbBackgroundColor = '#4844bd';
            $sliderItemData->opacity = 0.8;

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('Documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->description = t('Explore our comprehensive documentation to get the most out of our platform. Find guides, tutorials, and detailed information to help you navigate and utilize all features effectively. Click the link to access the full documentation.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->rgbBackgroundColor = '#808080';
            $sliderItemData->opacity = 0.8;
            $sliderItemData->link = 'https://docs.shopsys.com';
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Documentation' . $domainId)->toString();

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('Become one of us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->description = t('Join our team and be part of an innovative company. Explore exciting career opportunities and grow with us!', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->rgbBackgroundColor = '#b3bf45';
            $sliderItemData->opacity = 0.8;
            $sliderItemData->link = 'https://jobs.shopsys.cz';
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Become one of us' . $domainId)->toString();

            $this->sliderItemFacade->create($sliderItemData);
        }
    }
}
