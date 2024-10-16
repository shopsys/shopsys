<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use App\DataFixtures\Demo\CompanyDataFixture;

abstract class GraphQlB2bDomainWithLoginTestCase extends CommonGraphQlWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL;

    protected function setUp(): void
    {
        parent::setUp();

        $b2bDomain = $this->domain->findFirstB2bDomain();

        if ($b2bDomain === null) {
            $this->markTestSkipped('No B2B domain found');
        }

        $this->domain->switchDomainById($b2bDomain->getId());

        $this->login();
    }
}
