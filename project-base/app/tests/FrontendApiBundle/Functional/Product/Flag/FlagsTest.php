<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private FlagFacade $flagFacade;

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
}
