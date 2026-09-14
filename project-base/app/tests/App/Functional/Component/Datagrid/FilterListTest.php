<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\Test\Client;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The filter of a CRUD list from the URL down to the rows — the composed rules arrive in the form named
 * after the datagrid, narrow the listed product reviews, outrank the quick search, and the page renders the
 * tabs and the prototypes the filter is composed from.
 */
class FilterListTest extends TransactionFunctionalTestCase
{
    private const string LIST_ROUTE = 'admin_crud_product_review_list';

    private const string FILTER_PARAMETER = 'ProductReview_filter';

    private const string SEARCH_PARAMETER = 'ProductReview_search';

    public function testComposedRuleNarrowsTheListedReviews(): void
    {
        $client = $this->createLoggedInClient();

        $allRows = $this->listRows($client, []);
        $pendingRows = $this->listRows($client, $this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]));

        $this->assertGreaterThan(0, $pendingRows->count());
        $this->assertLessThan($allRows->count(), $pendingRows->count());
    }

    public function testComposedRuleOutranksTheQuickSearch(): void
    {
        $client = $this->createLoggedInClient();

        $filteredRows = $this->listRows($client, $this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]));
        $filteredAndSearchedRows = $this->listRows($client, [
            ...$this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]),
            self::SEARCH_PARAMETER => ['text' => 'something-no-review-mentions'],
        ]);

        $this->assertSame($filteredRows->count(), $filteredAndSearchedRows->count());
    }

    public function testListRendersTheSearchTabsAndThePrototypesOfEveryFilter(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(1, $crawler->filter('a[data-bs-toggle="tab"][href="#datagrid-quick-search"].active'));
        $this->assertCount(1, $crawler->filter('a[data-bs-toggle="tab"][href="#datagrid-filter"]'));
        $this->assertGreaterThan(0, $crawler->filter('[data-controller="datagrid-filter"]')->count());
        // the starting point: one group with one rule waiting to be filled
        $this->assertCount(1, $crawler->filter('form [data-datagrid-filter-target="group"]'));
        $this->assertCount(1, $crawler->filter('form [data-datagrid-filter-target="rule"]'));
        $this->assertGreaterThan(0, $crawler->filter('template[data-datagrid-filter-target="prototype"][data-filter="status"][data-arity="list"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('template[data-datagrid-filter-target="prototype"][data-filter="isVerifiedPurchase"][data-arity="none"]')->count());
    }

    public function testComposedFilterOpensItsTab(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(1, $crawler->filter('a[data-bs-toggle="tab"][href="#datagrid-filter"].active'));
        $this->assertCount(0, $crawler->filter('a[data-bs-toggle="tab"][href="#datagrid-quick-search"].active'));
    }

    public function testInvalidRuleIsReportedInsteadOfNarrowing(): void
    {
        $client = $this->createLoggedInClient();

        $allRows = $this->listRows($client, []);
        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeFilter('status', 'in', ['no-such-status']));

        // a page rendering a submitted form with errors answers 422, the way Symfony renders forms
        $this->assertResponseStatusCodeSame(422);
        $this->assertSame($allRows->count(), $crawler->filter('tr.js-grid-row')->count());
        $this->assertGreaterThan(0, $crawler->filter('.invalid-feedback, .form-error-message, .is-invalid')->count());
    }

    /**
     * @return array<string, mixed>
     */
    private function composeFilter(string $filter, string $operator, mixed $value): array
    {
        return [
            self::FILTER_PARAMETER => [
                'operator' => 'and',
                'groups' => [
                    ['operator' => 'and', 'rules' => [['filter' => $filter, 'operator' => $operator, 'value' => $value]]],
                ],
            ],
        ];
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
