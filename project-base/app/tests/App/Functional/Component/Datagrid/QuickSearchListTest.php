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
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The quick search of a CRUD list from the URL down to the rows — the searched text arrives in the form
 * named after the datagrid, narrows the listed product reviews and stays in the rendered input.
 */
class QuickSearchListTest extends TransactionFunctionalTestCase
{
    private const string LIST_ROUTE = 'admin_crud_product_review_list';

    /**
     * The datagrid of a CRUD controller is named after its entity, so its quick search form is `ProductReview_search`.
     */
    private const string SEARCH_PARAMETER = 'ProductReview_search';

    public function testTypedTextNarrowsTheListedReviewsIgnoringCase(): void
    {
        $client = $this->createLoggedInClient();
        $catnum = $this->findSearchableCatnum();

        $allRows = $this->listRows($client, []);
        $foundRows = $this->listRows($client, [self::SEARCH_PARAMETER => ['text' => mb_strtoupper($catnum)]]);

        $this->assertGreaterThan(0, $foundRows->count());
        $this->assertLessThan($allRows->count(), $foundRows->count());
    }

    public function testSearchedTextStaysInTheInput(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), [self::SEARCH_PARAMETER => ['text' => 'hrnek']]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('hrnek', $crawler->filter(sprintf('input[name="%s[text]"]', self::SEARCH_PARAMETER))->attr('value'));
    }

    /**
     * A catalogue number carried by some of the demo reviews, not by all of them, so that a search by it narrows the list.
     */
    private function findSearchableCatnum(): string
    {
        /** @var \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview[] $reviews */
        $reviews = $this->em->getRepository(ProductReview::class)->findAll();
        $reviewCountsByCatnum = [];

        foreach ($reviews as $review) {
            if ($review->getCatnum() !== null) {
                $reviewCountsByCatnum[$review->getCatnum()] = ($reviewCountsByCatnum[$review->getCatnum()] ?? 0) + 1;
            }
        }

        $this->assertGreaterThan(1, count($reviewCountsByCatnum), 'The demo data have to contain reviews of several products.');

        return (string)array_key_first($reviewCountsByCatnum);
    }

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

        return $router->generate(self::LIST_ROUTE);
    }
}
