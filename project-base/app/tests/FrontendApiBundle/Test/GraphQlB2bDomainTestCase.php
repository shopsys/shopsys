<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

abstract class GraphQlB2bDomainTestCase extends GraphQlTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $b2bDomain = $this->domain->findFirstB2bDomain();

        if ($b2bDomain === null) {
            $this->markTestSkipped('No B2B domain found');
        }

        $this->domain->switchDomainById($b2bDomain->getId());
    }
}
