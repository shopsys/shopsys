<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

abstract class GraphQlWithLoginTestCase extends CommonGraphQlWithLoginTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->login();
    }
}
