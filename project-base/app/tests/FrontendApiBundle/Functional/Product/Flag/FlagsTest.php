<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagDetailFriendlyUrlDataProvider;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private FlagFacade $flagFacade;

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @inject
     */
    private SeoSettingFacade $seoSettingFacade;

    public function testFlags(): void
    {
        $flags = array_reverse($this->flagFacade->getAll());

        foreach ($flags as $position => $flag) {
            $flag->setPosition($position);
        }

        $this->em->flush();

        $query = '
            query {
                flags {
                    name
                }
            }
        ';

        $flags = array_map(
            fn ($flag) => [
                'name' => $flag->getName($this->getFirstDomainLocale()),
            ],
            $flags,
        );

        $arrayExpected = [
            'data' => [
                'flags' => $flags,
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testBatchLoadedFieldsOfAllFlagsBelongToTheirFlag(): void
    {
        $this->seoSettingFacade->setAllAlternativeDomains([]);

        $expectedFlags = [];

        foreach ($this->flagFacade->getAllVisibleFlags($this->getFirstDomainLocale()) as $flag) {
            $seoAttributes = $flag->getSeoAttributes(Domain::FIRST_DOMAIN_ID);
            $slug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
                Domain::FIRST_DOMAIN_ID,
                FlagDetailFriendlyUrlDataProvider::ROUTE_NAME,
                $flag->getId(),
            );

            $expectedFlags[] = [
                'name' => $flag->getName($this->getFirstDomainLocale()),
                'slug' => '/' . $slug,
                'seo' => [
                    'title' => $seoAttributes->getTitle(),
                    'metaDescription' => $seoAttributes->getMetaDescription(),
                    'h1' => $seoAttributes->getH1(),
                ],
                'hreflangLinks' => [
                    [
                        'hreflang' => $this->getFirstDomainLocale(),
                        'href' => $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
                            Domain::FIRST_DOMAIN_ID,
                            FlagDetailFriendlyUrlDataProvider::ROUTE_NAME,
                            $flag->getId(),
                        ),
                    ],
                ],
            ];
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/FlagsWithBatchLoadedFieldsQuery.graphql');

        $this->assertSame($expectedFlags, $this->getResponseDataForGraphQlType($response, 'flags'));
    }
}
