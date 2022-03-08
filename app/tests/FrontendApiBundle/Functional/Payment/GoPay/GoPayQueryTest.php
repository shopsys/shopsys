<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GoPayQueryTest extends GraphQlTestCase
{
    public function testGoPaySwiftsQuery()
    {
        $query = '
query {
  GoPaySwifts (currencyCode: "CZK"){
    swift
    name
  }
}        
        ';

        $expect = '
{
  "data": {
    "GoPaySwifts": [
      {
        "swift": "123456XZY",
        "name": "Airbank"
      },
      {
        "swift": "ABC123456",
        "name": "Aqua bank"
      }
    ]
  }
}        
        ';

        $this->assertQueryWithExpectedJson($query, $expect);
    }
}
