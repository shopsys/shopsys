<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     * @inject
     */
    protected PaymentFacade $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    protected Payment $payment;

    protected function setUp(): void
    {
        $this->payment = $this->paymentFacade->getById(2);

        parent::setUp();
    }

    public function testPaymentNameByUuid(): void
    {
        $query = '
            query {
                payment(uuid: "' . $this->payment->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'payment' => [
                    'name' => t('Cash on delivery', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
