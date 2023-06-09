<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    protected PaymentFacade $paymentFacade;

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
                    'name' => t('Cash on delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
