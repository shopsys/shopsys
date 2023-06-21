<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MergeCartsInfoTest extends GraphQlTestCase
{
    public function testDoNotShowInfoWhenMergingAnonymousCartWithEmptyCartAfterLogin()
    {
        $loginMutationWithCartUuid = 'mutation {
                Login(input: {
                    email: "no-reply@shopsys.com"
                    password: "user123"
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                }) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                    showCartMergeInfo
                }
            }
        ';

        $response = $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($loginMutationWithCartUuid),
            'Login',
        );

        self::AssertFalse($response['showCartMergeInfo']);
    }
}
