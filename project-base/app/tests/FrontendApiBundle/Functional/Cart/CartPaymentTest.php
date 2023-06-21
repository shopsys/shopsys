<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Payment\Payment;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CartPaymentTest extends GraphQlTestCase
{
    public function testPaymentIsReturnedFromCart(): void
    {
        /** @var \App\Model\Payment\Payment $paymentGoPay */
        $paymentGoPay = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY);
        $swift = 'ABCDEFGH';
        $this->addPaymentToDemoCart($paymentGoPay, $swift);
        $getCartQuery = '{
            cart(cartInput: {
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                }
            ) {
                paymentGoPayBankSwift
                payment {
                    uuid
                }
            }
        }';
        $response = $this->getResponseContentForQuery($getCartQuery);
        $responseData = $this->getResponseDataForGraphQlType($response, 'cart');

        $this->assertSame($paymentGoPay->getUuid(), $responseData['payment']['uuid']);
        $this->assertSame($swift, $responseData['paymentGoPayBankSwift']);
    }

    public function testPaymentIsReturnedAfterAddingToCart()
    {
        /** @var \App\Model\Payment\Payment $paymentGoPay */
        $paymentGoPay = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY);
        $swift = 'ABCDEFGH';
        $response = $this->addPaymentToDemoCart($paymentGoPay, $swift);
        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePaymentInCart');

        $this->assertSame($paymentGoPay->getUuid(), $responseData['payment']['uuid']);
        $this->assertSame($swift, $responseData['paymentGoPayBankSwift']);
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @param string $goPayBankSwift
     * @return array
     */
    private function addPaymentToDemoCart(Payment $payment, string $goPayBankSwift): array
    {
        $changePaymentInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    paymentUuid: "' . $payment->getUuid() . '"
                    paymentGoPayBankSwift: "' . $goPayBankSwift . '"
                }) {
                    payment {
                        uuid
                    }
                    paymentGoPayBankSwift
                }
            }
        ';

        return $this->getResponseContentForQuery($changePaymentInCartMutation);
    }
}
