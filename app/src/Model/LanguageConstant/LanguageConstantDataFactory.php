<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

class LanguageConstantDataFactory
{
    /**
     * @return \App\Model\LanguageConstant\LanguageConstantData
     */
    public function create(): LanguageConstantData
    {
        return new LanguageConstantData();
    }

    /**
     * @param string $key
     * @param string $locale
     * @param string $translation
     * @param \App\Model\LanguageConstant\LanguageConstant|null $languageConstant
     * @return \App\Model\LanguageConstant\LanguageConstantData
     */
    public function createFromDataOrLanguageConstant(
        string $key,
        string $locale,
        string $translation,
        ?LanguageConstant $languageConstant
    ): LanguageConstantData {
        return $languageConstant === null
            ? $this->createFromData($key, $locale, $translation)
            : $this->createFromLanguageConstant($languageConstant, $locale, $translation);
    }

    /**
     * @param string $key
     * @param string $locale
     * @param string $originalTranslation
     * @return \App\Model\LanguageConstant\LanguageConstantData
     */
    private function createFromData(string $key, string $locale, string $originalTranslation): LanguageConstantData
    {
        $languageConstantData = $this->create();
        $languageConstantData->key = $key;
        $languageConstantData->locale = $locale;
        $languageConstantData->originalTranslation = $originalTranslation;

        return $languageConstantData;
    }

    /**
     * @param \App\Model\LanguageConstant\LanguageConstant $languageConstant
     * @param string $locale
     * @param string $originalTranslation
     * @return \App\Model\LanguageConstant\LanguageConstantData
     */
    private function createFromLanguageConstant(
        LanguageConstant $languageConstant,
        string $locale,
        string $originalTranslation
    ): LanguageConstantData {
        $languageConstantData = $this->create();
        $languageConstantData->key = $languageConstant->getKey();
        $languageConstantData->locale = $locale;
        $languageConstantData->originalTranslation = $originalTranslation;
        $languageConstantData->userTranslation = $languageConstant->getTranslation($locale);

        return $languageConstantData;
    }
}
