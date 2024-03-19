<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Customer\User\CustomerUser;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class DeleteDeliveryAddressTest extends GraphQlWithLoginTestCase
{
    public function testDeleteDeliveryAddress(): void
    {
        $customer = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH, CustomerUser::class);
        $deliveryAddressUuid = $customer->getDefaultDeliveryAddress()->getUuid();

        $query = '
mutation {
    DeleteDeliveryAddress(deliveryAddressUuid: "' . $deliveryAddressUuid . '") {
        uuid
    }
}';

        $jsonExpected = '
{
    "data": {
        "DeleteDeliveryAddress":[]
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
