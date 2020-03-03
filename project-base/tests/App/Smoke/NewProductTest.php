<?php

declare(strict_types=1);

namespace Tests\App\Smoke;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Security\Csrf\CsrfToken;
use Tests\App\Test\FunctionalTestCase;

class NewProductTest extends FunctionalTestCase
{
    public function createOrEditProductProvider()
    {
        return [['admin/product/new/'], ['admin/product/edit/1']];
    }

    /**
     * @dataProvider createOrEditProductProvider
     * @param mixed $relativeUrl
     */
    public function testCreateOrEditProduct($relativeUrl)
    {
        $domainUrl = $this->getContainer()->getParameter('overwrite_domain_url');
        $server = [
            'HTTP_HOST' => sprintf('%s:%d', parse_url($domainUrl, PHP_URL_HOST), parse_url($domainUrl, PHP_URL_PORT)),
        ];

        $client1 = $this->findClient(false, 'admin', 'admin123');
        $crawler = $client1->request('GET', $relativeUrl, [], [], $server);

        $form = $crawler->filter('form[name=product_form]')->form();
        $this->fillForm($form);

        $client2 = $this->findClient(true, 'admin', 'admin123');
        /** @var \Doctrine\ORM\EntityManager $em2 */
        $em2 = $client2->getContainer()->get('doctrine.orm.entity_manager');

        $em2->beginTransaction();

        /** @var \Symfony\Component\Security\Csrf\CsrfTokenManager $tokenManager */
        $tokenManager = $client2->getContainer()->get('security.csrf.token_manager');
        $token = $tokenManager->getToken(ProductFormType::CSRF_TOKEN_ID);
        $this->setFormCsrfToken($form, $token);

        $client2->submit($form);

        $em2->rollback();

        /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag */
        $flashBag = $client2->getContainer()->get('session')->getFlashBag();

        $this->assertSame(302, $client2->getResponse()->getStatusCode());
        $this->assertNotEmpty($flashBag->get(FlashMessage::KEY_SUCCESS));
        $this->assertEmpty($flashBag->get(FlashMessage::KEY_ERROR));
    }

    /**
     * @param \Symfony\Component\DomCrawler\Form $form
     */
    private function fillForm(Form $form)
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
        $form['product_form[displayAvailabilityGroup][sellingFrom]'] = '1.1.1990';
        $form['product_form[displayAvailabilityGroup][sellingTo]'] = '1.1.2000';
        $form['product_form[displayAvailabilityGroup][stockGroup][stockQuantity]'] = '10';
        $form['product_form[displayAvailabilityGroup][unit]']->setValue($unit->getId());
        $form['product_form[displayAvailabilityGroup][availability]']->setValue($availability->getId());
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
    private function fillManualInputPrices(Form $form)
    {
        $pricingGroupFacade = $this->getContainer()->get(PricingGroupFacade::class);
        foreach ($pricingGroupFacade->getAll() as $pricingGroup) {
            $inputName = sprintf(
                'product_form[pricesGroup][productCalculatedPricesGroup][manualInputPricesByPricingGroupId][%s]',
                $pricingGroup->getId()
            );
            $form[$inputName] = '10000';
        }
    }

    /**
     * @param \Symfony\Component\DomCrawler\Form $form
     */
    private function fillVats(Form $form)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat */
            $vat = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, $domainId);
            $inputName = sprintf(
                'product_form[pricesGroup][productCalculatedPricesGroup][vatsIndexedByDomainId][%s]',
                $domainId
            );
            $form->setValues([
                $inputName => $vat->getId(),
            ]);
        }
    }
}
