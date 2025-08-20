<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

class LanguageConstantDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData
     */
    public function create(): LanguageConstantData
    {
        $languageConstantData = new LanguageConstantData();
        $languageConstantData->namespace = LanguageConstant::NAMESPACE_COMMON;

        return $languageConstantData;
    }

    /**
     * @param string $key
     * @param string $locale
     * @param string $translation
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant|null $languageConstant
     * @param string $namespace
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData
     */
    public function createFromDataOrLanguageConstant(
        string $key,
        string $locale,
        string $translation,
        ?LanguageConstant $languageConstant,
        string $namespace,
    ): LanguageConstantData {
        return $languageConstant === null
            ? $this->createFromData($key, $locale, $translation, $namespace)
            : $this->createFromLanguageConstant($languageConstant, $locale, $translation);
    }

    /**
     * @param string $key
     * @param string $locale
     * @param string $originalTranslation
     * @param string $namespace
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData
     */
    protected function createFromData(
        string $key,
        string $locale,
        string $originalTranslation,
        string $namespace,
    ): LanguageConstantData {
        $languageConstantData = $this->create();
        $languageConstantData->key = $key;
        $languageConstantData->namespace = $namespace;
        $languageConstantData->locale = $locale;
        $languageConstantData->originalTranslation = $originalTranslation;

        return $languageConstantData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant $languageConstant
     * @param string $locale
     * @param string $originalTranslation
     * @return \Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData
     */
    protected function createFromLanguageConstant(
        LanguageConstant $languageConstant,
        string $locale,
        string $originalTranslation,
    ): LanguageConstantData {
        $languageConstantData = $this->create();
        $languageConstantData->key = $languageConstant->getKey();
        $languageConstantData->namespace = $languageConstant->getNamespace();
        $languageConstantData->locale = $locale;
        $languageConstantData->originalTranslation = $originalTranslation;
        $languageConstantData->userTranslation = $languageConstant->getTranslation($locale);

        return $languageConstantData;
    }
}
