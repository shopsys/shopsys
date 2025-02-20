<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper;

class ChangePersonalAndCompanyDataInputProvider
{
    public const array INPUT_ARRAY = [
        ...self::PERSONAL_DATA_INPUT_ARRAY,
        ...self::COMPANY_DATA_INPUT_ARRAY,
    ];

    public const array PERSONAL_DATA_INPUT_ARRAY = [
        'telephone' => '123456321',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'newsletterSubscription' => false,
    ];

    public const array COMPANY_DATA_INPUT_ARRAY = [
        'street' => '123 Fake street',
        'city' => 'Springfield',
        'country' => 'CZ',
        'postcode' => '54321',
        'companyCustomer' => true,
        'companyName' => 'Whatever',
        'companyNumber' => '1234567487975152',
        'companyTaxNumber' => 'AL987654321',
    ];
}
