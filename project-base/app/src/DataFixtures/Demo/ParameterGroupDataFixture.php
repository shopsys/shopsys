<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade;

class ParameterGroupDataFixture extends AbstractReferenceFixture
{
    public const string PARAM_GROUP_MAIN_INFORMATION = 'param_group_main_information';
    public const string PARAM_GROUP_CONNECTION_METHOD = 'param_group_connection_method';
    public const string PARAM_GROUP_MAIN_INFORMATION_MOUSE = 'param_group_main_information_mouse';
    public const string PARAM_GROUP_PROPERTIES = 'param_group_properties';
    public const string PARAM_GROUP_FUNCTIONS = 'param_group_function';
    public const string PARAM_GROUP_SIZE_WEIGHT = 'param_group_size_weight';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupFacade $parameterGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupDataFactory $parameterGroupDataFactory
     */
    public function __construct(
        private readonly ParameterGroupFacade $parameterGroupFacade,
        private readonly ParameterGroupDataFactory $parameterGroupDataFactory,
    ) {
    }

    /**
     * @param string $locale
     * @return array<string, string>
     */
    private static function getParameterGroupNames(string $locale): array
    {
        return [
            self::PARAM_GROUP_MAIN_INFORMATION => t('Main information', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_GROUP_CONNECTION_METHOD => t('Connection method', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_GROUP_MAIN_INFORMATION_MOUSE => t('Main information mouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_GROUP_PROPERTIES => t('Properties', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_GROUP_FUNCTIONS => t('Functions', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            self::PARAM_GROUP_SIZE_WEIGHT => t('Size and weight', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
        ];
    }

    /**
     * @param string $referenceName
     * @param string $locale
     * @return string
     */
    public static function getParameterGroupNameByReferenceName(string $referenceName, string $locale): string
    {
        $parameterNames = self::getParameterGroupNames($locale);

        return $parameterNames[$referenceName] ?? throw new InvalidArgumentException('Parameter group name reference "' . $referenceName . '" not found');
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $parameterGroupKeys = array_keys(self::getParameterGroupNames('cs'));

        foreach ($parameterGroupKeys as $parameterGroupKey) {
            $parameterGroupNamesByLocale = [];

            foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataLocales() as $locale) {
                $parameterGroupNamesByLocale[$locale] = self::getParameterGroupNameByReferenceName($parameterGroupKey, $locale);
            }

            $parameterGroup = $this->createParameterGroup($parameterGroupNamesByLocale);
            $this->addReference($parameterGroupKey, $parameterGroup);
        }
    }

    /**
     * @param array<string, string> $parameterGroupNameByLocale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
     */
    private function createParameterGroup(array $parameterGroupNameByLocale): ParameterGroup
    {
        $parameterGroupData = $this->parameterGroupDataFactory->create();
        $parameterGroupData->name = $parameterGroupNameByLocale;

        return $this->parameterGroupFacade->create($parameterGroupData);
    }
}
