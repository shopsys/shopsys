<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\LanguageConstant;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant;
use Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstantData;

class LanguageConstantTest extends TestCase
{
    public function testGetTranslationDoesNotCreateMissingLocaleTranslation(): void
    {
        $languageConstantData = new LanguageConstantData();
        $languageConstantData->key = 'Stores';
        $languageConstantData->namespace = LanguageConstant::NAMESPACE_COMMON;
        $languageConstantData->locale = 'en';
        $languageConstantData->userTranslation = 'Stores override';

        $languageConstant = new LanguageConstant($languageConstantData);

        $this->assertNull($languageConstant->getTranslation('cs'));
        $this->assertCount(1, $languageConstant->getTranslations());
    }
}
