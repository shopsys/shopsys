<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\LanguageConstant;

use App\Model\LanguageConstant\LanguageConstantDataFactory;
use App\Model\LanguageConstant\LanguageConstantFacade;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class LanguageConstantsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private LanguageConstantFacade $languageConstantFacade;

    /**
     * @inject
     */
    private LanguageConstantDataFactory $languageConstantDataFactory;

    public function testLanguageConstants(): void
    {
        $this->createLanguageConstant();

        $query = '
            query {
                languageConstants {
                    key
                    translation
                }
            }
        ';

        $jsonExpected = '
            {
                "data": {
                    "languageConstants": [
                        {
                            "key": "' . t('Add to cart', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, 'en') . '",
                            "translation": "' . t('Add to cart', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, 'cs') . '"
                        }
                    ]
                }
            }
        ';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    private function createLanguageConstant(): void
    {
        $languageConstantData = $this->languageConstantDataFactory->create();
        $languageConstantData->key = t('Add to cart', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, 'en');
        $languageConstantData->locale = $this->getFirstDomainLocale();
        $languageConstantData->originalTranslation = t('Add to cart', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, 'en');
        $languageConstantData->userTranslation = t('Add to cart', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $this->languageConstantFacade->createOrEdit($languageConstantData, null);
    }
}
