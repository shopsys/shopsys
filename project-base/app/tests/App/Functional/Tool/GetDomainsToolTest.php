<?php

declare(strict_types=1);

namespace Tests\App\Functional\Tool;

use Shopsys\McpBundle\Tool\GetDomainsTool;
use Tests\App\Test\FunctionalTestCase;

class GetDomainsToolTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private GetDomainsTool $getDomainsTool;

    public function testReturnsAllDomainsWithTheirLocales(): void
    {
        $expectedDomains = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $expectedDomains[] = ['id' => $domainConfig->getId(), 'locale' => $domainConfig->getLocale()];
        }

        $domains = $this->getDomainsTool->getDomains()['domains'];

        $this->assertSame($expectedDomains, array_map(
            static fn (array $domain) => ['id' => $domain['id'], 'locale' => $domain['locale']],
            $domains,
        ));
    }
}
