<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Parameter\ParameterGroup;
use App\Model\Product\Parameter\ParameterGroupDataFactory;
use App\Model\Product\Parameter\ParameterGroupFacade;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ParameterGroupDataFixture extends AbstractReferenceFixture
{
    public const string PARAM_GROUP_MAIN_INFORMATION = 'param_group_main_information';
    public const string PARAM_GROUP_CONNECTION_METHOD = 'param_group_connection_method';
    public const string PARAM_GROUP_MAIN_INFORMATION_MOUSE = 'param_group_main_information_mouse';

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupFacade $parameterGroupFacade
     * @param \App\Model\Product\Parameter\ParameterGroupDataFactory $parameterGroupDataFactory
     * @param \Faker\Generator $faker
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly ParameterGroupFacade $parameterGroupFacade,
        private readonly ParameterGroupDataFactory $parameterGroupDataFactory,
        private readonly Generator $faker,
        private readonly Domain $domain,
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

            foreach ($this->domain->getAllLocales() as $locale) {
                $parameterGroupNamesByLocale[$locale] = self::getParameterGroupNameByReferenceName($parameterGroupKey, $locale);
            }

            $parameterGroup = $this->createParameterGroup($parameterGroupNamesByLocale);
            $this->addReference($parameterGroupKey, $parameterGroup);
        }
    }

    /**
     * @param array<string, string> $parameterGroupNameByLocale
     * @return \App\Model\Product\Parameter\ParameterGroup
     */
    private function createParameterGroup(array $parameterGroupNameByLocale): ParameterGroup
    {
        $parameterGroupData = $this->parameterGroupDataFactory->create();
        $parameterGroupData->names = $parameterGroupNameByLocale;
        $parameterGroupData->akeneoCode = $this->faker->lexify('???????');

        return $this->parameterGroupFacade->create($parameterGroupData);
    }
}
