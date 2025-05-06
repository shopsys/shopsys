<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use App\DataFixtures\Demo\FlagDataFixture;
use App\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    protected FlagFacade $flagFacade;

    public function testFlagByUuid(): void
    {
        $flag = $this->getReference(FlagDataFixture::FLAG_PRODUCT_MADEIN_DE, Flag::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/FlagQuery.graphql', [
            'uuid' => $flag->getUuid(),
            'firstProducts' => 5,
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'flag');

        $this->assertSame(t('Made in DE', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()), $responseData['name']);
        $this->assertSame('#000000', $responseData['rgbColor']);
        $this->assertSame($this->urlGenerator->generate('front_flag_detail', ['id' => $flag->getId()]), $responseData['slug']);
        $this->assertSame([
            [
                'name' => t('Made in DE', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'slug' => $this->urlGenerator->generate('front_flag_detail', ['id' => $flag->getId()]),
            ],
        ], $responseData['breadcrumb']);
        $this->assertSame([
            'orderingMode' => 'PRIORITY',
            'edges' => [
                [
                    'node' => [
                        'name' => t('OLYMPUS VH-620', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                    ],
                ],
            ],
        ], $responseData['products']);
        $this->assertSame([
            [
                'name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
        ], $responseData['categories']);
    }

    public function testFlagByUuidFilteredByAnotherFlag(): void
    {
        $flagAction = $this->getReference(FlagDataFixture::FLAG_PRODUCT_ACTION, Flag::class);
        $flagNew = $this->getReference(FlagDataFixture::FLAG_PRODUCT_NEW, Flag::class);

        $limit = 5;
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/FlagQuery.graphql', [
            'uuid' => $flagAction->getUuid(),
            'firstProducts' => $limit,
            'filter' => ['flags' => [$flagNew->getUuid()]],
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'flag');

        $products = [
            [
                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Apple iPhone 5S 64GB, gold', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Book 55 best programs for burning CDs and DVDs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Book Computer for Dummies Digital Photography II', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
        ];

        $productsWithNodes = [];

        for ($i = 0; $i < $limit; $i++) {
            $productsWithNodes[] = [
                'node' => $products[$i],
            ];
        }

        $expectedCategories = [
            [
                'name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Newest toys in stock', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
        ];

        $this->assertSame($productsWithNodes, $responseData['products']['edges']);
        $this->assertSame($expectedCategories, $responseData['categories']);
    }
}
