<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\LanguageConstant;

use App\Model\LanguageConstant\LanguageConstantDataFactory;
use App\Model\LanguageConstant\LanguageConstantFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class LanguageConstantsTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\LanguageConstant\LanguageConstantFacade
     * @inject
     */
    private LanguageConstantFacade $languageConstantFacade;

    /**
     * @var \App\Model\LanguageConstant\LanguageConstantDataFactory
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
                            "key": "' . t('Add to cart', [], 'dataFixtures', 'en') . '",
                            "translation": "' . t('Add to cart', [], 'dataFixtures', 'cs') . '"
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
        $languageConstantData->key = t('Add to cart', [], 'dataFixtures', 'en');
        $languageConstantData->locale = 'cs';
        $languageConstantData->originalTranslation = t('Add to cart', [], 'dataFixtures', 'en');
        $languageConstantData->userTranslation = t('Add to cart', [], 'dataFixtures', 'cs');

        $this->languageConstantFacade->createOrEdit($languageConstantData, null);
    }
}
