<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     * @inject
     */
    protected $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    protected $payment;

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
