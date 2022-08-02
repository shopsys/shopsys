<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller;

use App\DataFixtures\Demo\ProductDataFixture;
use Faker\Provider\Text;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\App\Test\ApplicationTestCase;

class ProductRenameRedirectPreviousUrlTest extends ApplicationTestCase
{
    private const TESTED_PRODUCT_ID = 100;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private ProductDataFactoryInterface $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     * @inject
     */
    protected EntityManagerDecorator $em;

    public function testPreviousUrlRedirect(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::TESTED_PRODUCT_ID);

        $previousFriendlyUrlSlug = $this->friendlyUrlFacade->findMainFriendlyUrl(
            Domain::FIRST_DOMAIN_ID,
            'front_product_detail',
            self::TESTED_PRODUCT_ID
        )->getSlug();

        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->name[$this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()] = Text::asciify();

        $this->productFacade->edit(self::TESTED_PRODUCT_ID, $productData);

        $firstDomainUrl = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getUrl();

        $isSecured = parse_url($firstDomainUrl, PHP_URL_SCHEME) === 'https';

        $firstDomainUrl = preg_replace(
            '#^https?://#',
            '',
            $firstDomainUrl
        );

        $client = self::getCurrentClient();
        $this->configureCurrentClient(null, null, [
            'HTTP_HOST' => $firstDomainUrl,
            'HTTPS' => $isSecured,
        ]);
        $client->request('GET', '/' . $previousFriendlyUrlSlug);

        // Should be 301 (moved permanently), because old product urls should be permanently redirected
        $this->assertEquals(301, $client->getResponse()->getStatusCode());
    }
}
