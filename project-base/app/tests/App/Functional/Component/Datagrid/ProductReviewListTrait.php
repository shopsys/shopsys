<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\Test\Client;

trait ProductReviewListTrait
{
    /**
     * @param array<string, mixed> $query
     */
    private function listRows(Client $client, array $query): Crawler
    {
        $crawler = $client->request('GET', $this->generateListPath($client), $query);
        $this->assertResponseStatusCodeSame(200);

        return $crawler->filter('tr.js-grid-row');
    }

    private function createLoggedInClient(): Client
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $client->catchExceptions(false);

        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $administrator = $client->getContainer()->get(AdministratorFacade::class)->getById($administrator->getId());
        $client->getContainer()->get(AdministratorActivityFacade::class)->create($administrator, '127.0.0.1');
        $client->loginUser($administrator, 'administration');

        return $client;
    }

    private function generateListPath(Client $client): string
    {
        /** @var \Symfony\Component\Routing\RouterInterface $router */
        $router = $client->getContainer()->get('router');

        return $router->generate('admin_crud_product_review_list');
    }

    private function generateEditPath(Client $client, ProductReview $review): string
    {
        /** @var \Symfony\Component\Routing\RouterInterface $router */
        $router = $client->getContainer()->get('router');

        return $router->generate('admin_crud_product_review_edit', ['id' => $review->getId()]);
    }
}
