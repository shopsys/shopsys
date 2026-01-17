<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Parameter\ParameterRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory;

class ParameterColorValueDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string PARAMETER_VALUE_RED_REFERENCE_PREFIX = 'parameter_value_red_';

    public function __construct(
        private readonly ParameterRepository $parameterRepository,
        private readonly ParameterValueDataFactory $parameterValueDataFactory,
    ) {
    }

    #[Override]
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
            $parameterValueRed = $this->parameterRepository->findOrCreateParameterValueByParameterValueData($parameterValueData);
            $this->addReference(self::PARAMETER_VALUE_RED_REFERENCE_PREFIX . $locale, $parameterValueRed);
        }
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
