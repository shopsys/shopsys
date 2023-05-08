<?php

declare(strict_types=1);

namespace Tests\App\Smoke;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Security\Csrf\CsrfToken;
use Tests\App\Test\ApplicationTestCase;

class NewProductTest extends ApplicationTestCase
{
    /**
     * @return iterable
     */
    public function createOrEditProductProvider(): iterable
    {
        yield ['admin/product/new/'];

        yield ['admin/product/edit/1'];
    }

    /**
     * @dataProvider createOrEditProductProvider
     * @param string $relativeUrl
     */
    public function testCreateOrEditProduct(string $relativeUrl): void
    {
        $domainUrl = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getUrl();
        $isDomainSecured = parse_url($domainUrl, PHP_URL_SCHEME) === 'https';

        $server = [
            'HTTP_HOST' => preg_replace('#^https?://#', '', $domainUrl),
            'HTTPS' => $isDomainSecured,
        ];

        $client1 = $this->createNewClient('admin', 'admin123');
        $crawler = $client1->request('GET', $relativeUrl, [], [], $server);

        $form = $crawler->filter('form[name=product_form]')->form();
        $this->fillForm($form);

        $client2 = $this->createNewClient('admin', 'admin123');
        /** @var \Doctrine\ORM\EntityManager $em2 */
        $em2 = $client2->getContainer()->get('doctrine.orm.entity_manager');

        $em2->beginTransaction();

        /** @var \Symfony\Component\Security\Csrf\CsrfTokenManager $tokenManager */
        $tokenManager = $client2->getContainer()->get('security.csrf.token_manager');
        // if domain is on HTTPS, previously created token is prefixed with https-
        $tokenId = ($isDomainSecured ? 'https-' : '') . ProductFormType::CSRF_TOKEN_ID;
        $token = $tokenManager->getToken($tokenId);
        $this->setFormCsrfToken($form, $token);

        $client2->submit($form);

        $em2->rollback();

        $this->assertSame(302, $client2->getResponse()->getStatusCode());
        $this->assertStringStartsWith($domainUrl . '/admin/product/list', $client2->followRedirect()->getUri());
    }

    /**
     * @param \Symfony\Component\DomCrawler\Form $form
     */
    private function fillForm(Form $form): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit */
        $unit = $this->getReference(UnitDataFixture::UNIT_CUBIC_METERS);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability */
        $availability = $this->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK);

        /** @var \Symfony\Component\DomCrawler\Field\InputFormField[] $nameForms */
        $nameForms = $form->get('product_form[name]');

        foreach ($nameForms as $nameForm) {
            $nameForm->setValue('testProduct');
        }
        $form['product_form[basicInformationGroup][catnum]'] = '123456';
        $form['product_form[basicInformationGroup][partno]'] = '123456';
        $form['product_form[basicInformationGroup][ean]'] = '123456';
        $form['product_form[descriptionsGroup][descriptions][1]'] = 'test description';
        $this->fillManualInputPrices($form);
        $this->fillVats($form);
        $form['product_form[displayAvailabilityGroup][sellingFrom]'] = '01.01.1990';
        $form['product_form[displayAvailabilityGroup][sellingTo]'] = '01.01.2000';
        $form['product_form[displayAvailabilityGroup][stockGroup][stockQuantity]'] = '10';
        $form['product_form[displayAvailabilityGroup][unit]']->setValue((string)$unit->getId());
        $form['product_form[displayAvailabilityGroup][availability]']->setValue((string)$availability->getId());
    }

    /**
     * @param \Symfony\Component\DomCrawler\Form $form
     * @param \Symfony\Component\Security\Csrf\CsrfToken $token
     */
    private function setFormCsrfToken(Form $form, CsrfToken $token)
    {
        $form['product_form[_token]'] = $token->getValue();
    }

    /**
     * @param \Symfony\Component\DomCrawler\Form $form
     */
    private function fillManualInputPrices(Form $form): void
    {
        $priceInputFieldFormPath = 'product_form[pricesGroup][productCalculatedPricesGroup][manualInputPricesByPricingGroupId]';

        $priceInputFields = $form->get($priceInputFieldFormPath);

        foreach ($priceInputFields as $priceInputField) {
            $priceInputField->setValue('10000');
        }
    }

    /**
     * @param \Symfony\Component\DomCrawler\Form $form
     */
    private function fillVats(Form $form): void
    {
        $vatInputFieldFormPath = 'product_form[pricesGroup][productCalculatedPricesGroup][vatsIndexedByDomainId]';

        $vatInputFields = $form->get($vatInputFieldFormPath);

        foreach ($vatInputFields as $domainId => $vatInputField) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);

            $vatInputField->setValue($vat->getId());
        }
    }
}
