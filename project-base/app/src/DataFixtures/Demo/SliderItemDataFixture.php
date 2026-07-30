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
    private const string PLATFORM_BACKGROUND_COLOR = '#0f00a0';
    private const string DOCUMENTATION_BACKGROUND_COLOR = '#1136ae';
    private const string CAREERS_BACKGROUND_COLOR = '#0054bd';
    private const float BRAND_BACKGROUND_OPACITY = 0.9;

    /**
     * @param \App\Model\Slider\SliderItemFacade $sliderItemFacade
     */
    public function __construct(
        private readonly SliderItemFacade $sliderItemFacade,
        private readonly SliderItemDataFactory $sliderItemDataFactory,
    ) {
    }

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

            $sliderItemData->name = t('Shopsys Platform', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->link = 'https://www.shopsys.cz';
            $sliderItemData->description = t('Build scalable B2C and B2B stores on an open commerce platform designed for complex projects.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->rgbBackgroundColor = self::PLATFORM_BACKGROUND_COLOR;
            $sliderItemData->opacity = self::BRAND_BACKGROUND_OPACITY;

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('Explore the documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->description = t('Find practical guides, architecture concepts, and step-by-step instructions for building with Shopsys Platform.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->rgbBackgroundColor = self::DOCUMENTATION_BACKGROUND_COLOR;
            $sliderItemData->opacity = self::BRAND_BACKGROUND_OPACITY;
            $sliderItemData->link = 'https://docs.shopsys.com';
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Documentation' . $domainId)->toString();

            $this->sliderItemFacade->create($sliderItemData);

            $sliderItemData->name = t('Join our team', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->description = t('Help us build ambitious e-commerce projects. Explore open roles and grow with Shopsys.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $sliderItemData->rgbBackgroundColor = self::CAREERS_BACKGROUND_COLOR;
            $sliderItemData->opacity = self::BRAND_BACKGROUND_OPACITY;
            $sliderItemData->link = 'https://jobs.shopsys.cz';
            $sliderItemData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, 'Become one of us' . $domainId)->toString();

            $this->sliderItemFacade->create($sliderItemData);
        }
    }
}
