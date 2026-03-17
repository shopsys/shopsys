<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper;

use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

class ChangePersonalAndCompanyDataInputProvider
{
    public static function getInputArray(): array
    {
        return [
            ...self::getPersonalDataInputArray(),
            ...self::getCompanyDataInputArray(),
        ];
    }

    public static function getPersonalDataInputArray(): array
    {
        return [
            'telephone' => new PhoneData('CZ', '+420', '123456321'),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'newsletterSubscription' => false,
        ];
    }

    public static function getCompanyDataInputArray(): array
    {
        return [
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
}
