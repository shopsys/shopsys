<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LanguageConstant;

class LanguageConstantDataFactory
{
    public function create(): LanguageConstantData
    {
        $languageConstantData = new LanguageConstantData();
        $languageConstantData->namespace = LanguageConstant::NAMESPACE_COMMON;

        return $languageConstantData;
    }

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
