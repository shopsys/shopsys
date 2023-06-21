<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\PersonalData;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PersonalDataMutationTest extends GraphQlTestCase
{
    public function testPersonalDataAccessRequestForExport(): void
    {
        $query = $this->getQuery(PersonalDataAccessRequest::TYPE_EXPORT);

        $this->assertQueryWithExpectedJson($query, $this->getExpectedResponseJson());
    }

    public function testPersonalDataAccessRequestForDisplay(): void
    {
        $query = $this->getQuery(PersonalDataAccessRequest::TYPE_DISPLAY);

        $this->assertQueryWithExpectedJson($query, $this->getExpectedResponseJson());
    }

    public function testPersonalDataAccessRequestForNonExistingType(): void
    {
        $query = $this->getQuery('wrongType');
        $response = $this->getResponseContentForQuery($query);

        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * @param string $routeName
     * @return string
     */
    private function getLink(string $routeName): string
    {
        return $this->getLocalizedPathOnFirstDomainByRouteName($routeName, pathType: UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * @return string
     */
    private function getExpectedResponseJson(): string
    {
        return '{
            "data": {
                "RequestPersonalDataAccess": {
                    "displaySiteContent": "' . t('By entering an email below, you can view your personal information that we register in our online store. An email with a link will be sent to you after entering your email address, to verify your identity. Clicking on the link will take you to a page listing all the personal details we have connected to your email address.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                    "displaySiteSlug": "' . $this->getLink('front_personal_data') . '",
                    "exportSiteContent": "' . t('By entering an email below, you can download your personal and other information (for example, order history) from our online store. An email with a link will be sent to you after entering your email address, to verify your identity. Clicking on the link will take you to a page where you’ll be able to download these informations in readable format - it will be the data registered to given email address on this online store domain.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                    "exportSiteSlug": "' . $this->getLink('front_personal_data_export') . '"
                }
            }
        }';
    }

    /**
     * @param string $type
     * @return string
     */
    private function getQuery(string $type): string
    {
        return '
            mutation {
                RequestPersonalDataAccess(input: { email:"no-reply@shopsys.com", type: ' . $type . ' }) {
                    displaySiteContent
                    displaySiteSlug
                    exportSiteContent
                    exportSiteSlug
                }
            }
        ';
    }
}
