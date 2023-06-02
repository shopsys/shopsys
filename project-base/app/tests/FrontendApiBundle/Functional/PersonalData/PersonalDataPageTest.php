<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\PersonalData;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PersonalDataPageTest extends GraphQlTestCase
{
    public function testPersonalDataPage(): void
    {
        $query = '
            query {
                personalDataPage {
                    displaySiteContent
                    displaySiteSlug
                    exportSiteContent
                    exportSiteSlug
                }
            }
        ';

        $jsonExpected = '{
            "data": {
                "personalDataPage": {
                    "displaySiteContent": "' . t('By entering an email below, you can view your personal information that we register in our online store. An email with a link will be sent to you after entering your email address, to verify your identity. Clicking on the link will take you to a page listing all the personal details we have connected to your email address.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                    "displaySiteSlug": "' . $this->getLink('front_personal_data') . '",
                    "exportSiteContent": "' . t('By entering an email below, you can download your personal and other information (for example, order history) from our online store. An email with a link will be sent to you after entering your email address, to verify your identity. Clicking on the link will take you to a page where you’ll be able to download these informations in readable format - it will be the data registered to given email address on this online store domain.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                    "exportSiteSlug": "' . $this->getLink('front_personal_data_export') . '"
                }
            }
        }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    /**
     * @param string $routeName
     * @return string
     */
    private function getLink(string $routeName): string
    {
        return $this->getLocalizedPathOnFirstDomainByRouteName($routeName, pathType: UrlGeneratorInterface::ABSOLUTE_PATH);
    }
}
