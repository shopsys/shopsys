<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use App\Model\Product\Flag\FlagFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Flag\Flag
     */
    protected $flag;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $flagFacade = $this->getContainer()->get(FlagFacade::class);
        $this->flag = $flagFacade->getById(6);

        parent::setUp();
    }

    public function testFlagByUuid(): void
    {
        $query = '
            query {
                flag(uuid: "' . $this->flag->getUuid() . '") {
                    name
                    rgbColor
                    slug
                    products {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                }
            }
        ';

        $jsonExpected = '{
    "data": {
        "flag": {
            "name": "' . t('Vyrobeno v DE', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
            "rgbColor": "#ffffff",
            "slug": "' . $this->urlGenerator->generate('front_flag_detail', ['id' => $this->flag->getId()]) . '",
            "products": {
                "edges": [
                    {
                        "node": {
                            "name": "' . t('OLYMPUS VH-620', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    }
                ]
            }
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
