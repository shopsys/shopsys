<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Parameter\ParameterRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory;

class ParameterColorValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     */
    public function __construct(
        private readonly ParameterRepository $parameterRepository,
        private readonly ParameterValueDataFactory $parameterValueDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
            $parameterValueData = $this->parameterValueDataFactory->create();
            $parameterValueData->locale = $locale;
            $parameterValueData->text = t('black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $parameterValueData->rgbHex = '#000000';
            $this->parameterRepository->findOrCreateParameterValueByParameterValueData($parameterValueData);

            $parameterValueData = $this->parameterValueDataFactory->create();
            $parameterValueData->locale = $locale;
            $parameterValueData->text = t('white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $parameterValueData->rgbHex = '#ffffff';
            $this->parameterRepository->findOrCreateParameterValueByParameterValueData($parameterValueData);

            $parameterValueData = $this->parameterValueDataFactory->create();
            $parameterValueData->locale = $locale;
            $parameterValueData->text = t('red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $parameterValueData->rgbHex = '#ff0000';
            $this->parameterRepository->findOrCreateParameterValueByParameterValueData($parameterValueData);
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
