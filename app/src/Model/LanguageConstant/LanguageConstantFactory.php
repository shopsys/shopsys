<?php

declare(strict_types=1);

namespace App\Model\LanguageConstant;

class LanguageConstantFactory
{
    /**
     * @param \App\Model\LanguageConstant\LanguageConstantData $languageConstantData
     * @return \App\Model\LanguageConstant\LanguageConstant
     */
    public function create(LanguageConstantData $languageConstantData): LanguageConstant
    {
        return new LanguageConstant($languageConstantData);
    }
}
