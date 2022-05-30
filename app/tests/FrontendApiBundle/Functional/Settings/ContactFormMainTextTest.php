<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ContactFormMainTextTest extends GraphQlTestCase
{
    public function testGetContactFormMainText(): void
    {
        $query = '
            query {
                settings {
                    contactFormMainText
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedText = t('Hi there, our team is happy and ready to answer your question. Please fill out the form below and we will get in touch as soon as possible.', [], 'dataFixtures', $firstDomainLocale);

        self::assertEquals($expectedText, $data['contactFormMainText']);
    }
}
