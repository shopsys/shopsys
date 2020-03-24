<?php

declare(strict_types=1);

namespace Tests\App\Smoke;

use App\DataFixtures\Demo\AvailabilityDataFixture;
use App\DataFixtures\Demo\ProductTypeDataFixture;
use App\DataFixtures\Demo\UnitDataFixture;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
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

        /** @var \App\Model\Product\Type\ProductType $productType */
        $productType = $this->getReference(ProductTypeDataFixture::TYPE_COMMON);

        /** @var \Symfony\Component\DomCrawler\Field\InputFormField[] $nameForms */
        $nameForms = $form->get('product_form[name]');
        foreach ($nameForms as $nameForm) {
            $nameForm->setValue('testProduct');
        }
        $form['product_form[basicInformationGroup][catnum]'] = '123456';
        $form['product_form[basicInformationGroup][partno]'] = '123456';
        $form['product_form[basicInformationGroup][ean]'] = '123456';
        $form['product_form[basicInformationGroup][productType][1]']->setValue($productType->getId());
        $form['product_form[basicInformationGroup][productType][2]']->setValue($productType->getId());
        $form['product_form[descriptionsGroup][descriptions][1]'] = 'test description';
        $this->fillAkeneoPrices($form);
        $form['product_form[displayAvailabilityGroup][sellingFrom]'] = '1.1.1990';
        $form['product_form[displayAvailabilityGroup][sellingTo]'] = '1.1.2000';
        $form['product_form[displayAvailabilityGroup][unit]']->setValue($unit->getId());
        $form['product_form[displayAvailabilityGroup][availability]']->setValue($availability->getId());
        $form['product_form[stocksGroup][stockProductData][1][productQuantity]'] = '1';
        $form['product_form[stocksGroup][stockProductData][2][productQuantity]'] = '2';
        $form['product_form[stocksGroup][stockProductData][3][productQuantity]'] = '3';
        $form['product_form[stocksGroup][stockProductData][4][productQuantity]'] = '4';
        $form['product_form[stocksGroup][stockProductData][5][productQuantity]'] = '5';
        $form['product_form[stocksGroup][stockProductData][6][productQuantity]'] = '6';
        $form['product_form[stocksGroup][stockProductData][7][productQuantity]'] = '7';
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
    private function fillAkeneoPrices(Form $form): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $inputName = sprintf(
                'product_form[pricesGroup][lowPriceWithVat][%s]',
                $domainId
            );
            $form[$inputName] = (string)($domainId * 5000);

            $inputName = sprintf(
                'product_form[pricesGroup][highPriceWithVat][%s]',
                $domainId
            );
            $form[$inputName] = (string)($domainId * 10000);
        }
    }
}
