<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use Override;

abstract class GraphQlWithLoginTestCase extends CommonGraphQlWithLoginTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->login();
    }
}
