<?php

declare(strict_types = 1);

namespace Shopsys\ShopBundle\DataFixtures\Translations;

use Shopsys\ShopBundle\DataFixtures\Translations\Exception\DataFixturesAttributeTranslationNotFound;

class DataFixturesTranslations
{
    public const TRANSLATED_ENTITY_AVAILABILITY = 'availability';
    public const TRANSLATED_ENTITY_BRAND = 'brand';

    public const TRANSLATED_ATTRIBUTE_NAME = 'name';
    public const TRANSLATED_ATTRIBUTE_DESCRIPTION = 'description';

    /**
     * @var \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslationInterface[]
     */
    private $registeredLanguageTranslations;

    /**
     * @param \Shopsys\ShopBundle\DataFixtures\Translations\DataFixturesTranslationInterface $translationService
     */
    public function registerTranslation($translationService): void
    {
        $this->registeredLanguageTranslations[] = new $translationService();
    }

    /**
     * @param string $translatedEntity
     * @param string $translatedAttribute
     * @param string $referenceName
     * @return array
     */
    public function getEntityAttributeTranslationsByReferenceName(
        string $translatedEntity,
        string $translatedAttribute,
        string $referenceName
    ): array {
        $entityAttributeTranslationsIndexedByLocale = [];
        foreach ($this->registeredLanguageTranslations as $registeredLanguageTranslation) {
            $translations = $registeredLanguageTranslation->getTranslations();

            if (!isset($translations[$translatedEntity][$translatedAttribute][$referenceName])) {
                throw new DataFixturesAttributeTranslationNotFound(
                    sprintf(
                        "Missing -%s- translation for entity '%s', attribute '%s' for reference name '%s'",
                        $registeredLanguageTranslation->getLocale(),
                        ucfirst($translatedEntity),
                        $translatedAttribute,
                        $referenceName
                    )
                );
            }

            $entityAttributeTranslationsIndexedByLocale[$registeredLanguageTranslation->getLocale()] = $translations[$translatedEntity][$translatedAttribute][$referenceName];
        }

        return $entityAttributeTranslationsIndexedByLocale;
    }

    /**
     * @param string $translatedEntity
     * @param string $translatedAttribute
     * @return array
     */
    public function getEntityAttributeTranslations(
        string $translatedEntity,
        string $translatedAttribute
    ): array {
        $entityAttributeTranslationsIndexedByLocale = [];
        foreach ($this->registeredLanguageTranslations as $registeredLanguageTranslation) {
            $translations = $registeredLanguageTranslation->getTranslations();

            if (!isset($translations[$translatedEntity][$translatedAttribute])) {
                throw new DataFixturesAttributeTranslationNotFound(
                    sprintf(
                        "Missing -%s- translation for entity '%s', attribute '%s'",
                        $registeredLanguageTranslation->getLocale(),
                        ucfirst($translatedEntity),
                        $translatedAttribute
                    )
                );
            }

            $entityAttributeTranslationsIndexedByLocale[$registeredLanguageTranslation->getLocale()] = $translations[$translatedEntity][$translatedAttribute];
        }

        return $entityAttributeTranslationsIndexedByLocale;
    }
}
