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
 * toolbar and the prototypes the filter is composed from.
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

    public function testListRendersTheToolbarAndThePrototypesOfEveryFilter(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client));

        $this->assertResponseStatusCodeSame(200);
        // the quick search and the button opening the filter panel stand side by side in one toolbar
        $this->assertCount(1, $crawler->filter('.datagrid-toolbar form.datagrid-quick-search'));
        $this->assertCount(1, $crawler->filter('.datagrid-toolbar [data-bs-toggle="offcanvas"]'));
        $this->assertCount(1, $crawler->filter('.offcanvas.datagrid-filter-panel'));
        $this->assertCount(0, $crawler->filter('.datagrid-chips'));
        $this->assertGreaterThan(0, $crawler->filter('[data-controller="datagrid-filter"]')->count());
        // the starting point: one group with one rule waiting to be filled
        $this->assertCount(1, $crawler->filter('form [data-datagrid-filter-target="group"]'));
        $this->assertCount(1, $crawler->filter('form [data-datagrid-filter-target="rule"]'));
        $this->assertGreaterThan(0, $crawler->filter('template[data-datagrid-filter-target="prototype"][data-filter="status"][data-arity="list"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('template[data-datagrid-filter-target="prototype"][data-filter="isVerifiedPurchase"][data-arity="none"]')->count());
    }

    public function testComposedFilterIsSpelledOutInTheBarOfConditions(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]));

        $this->assertResponseStatusCodeSame(200);
        // the composed rule is named on a chip, so a closed panel never narrows the records without a reason
        $this->assertCount(1, $crawler->filter('.datagrid-chips .datagrid-chip'));
        $this->assertSame('1', $crawler->filter('.datagrid-toolbar__filter-toggle .badge')->text());
    }

    public function testEveryComposedRuleGetsItsOwnChip(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), [
            self::FILTER_PARAMETER => [
                'operator' => 'and',
                'groups' => [
                    [
                        'operator' => 'and',
                        'rules' => [
                            ['filter' => 'status', 'operator' => 'in', 'value' => [ProductReviewStatusEnum::STATUS_PENDING]],
                            ['filter' => 'rating', 'operator' => 'in', 'value' => ['3']],
                        ],
                    ],
                    [
                        'operator' => 'and',
                        'rules' => [['filter' => 'isVerifiedPurchase', 'operator' => 'yes']],
                    ],
                ],
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        // no rule is ever summarised away, not even in a group of several
        $this->assertCount(3, $crawler->filter('.datagrid-chips .datagrid-chip'));
        $this->assertSame('3', $crawler->filter('.datagrid-toolbar__filter-toggle .badge')->text());
    }

    public function testChipRemovesOnlyItsOwnRule(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeTwoRules());
        $this->assertResponseStatusCodeSame(200);

        // the whole chip is the link taking its rule back
        $removalUrl = $crawler->filter('.datagrid-chips .datagrid-chip')->eq(0)->attr('href');
        $crawler = $client->request('GET', (string)$removalUrl);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(1, $crawler->filter('.datagrid-chips .datagrid-chip'));
        // the rule that stayed is the one the chip did not belong to
        $this->assertStringContainsString('Rating', $crawler->filter('.datagrid-chips .datagrid-chip')->text());
    }

    public function testRemovingTheLastRuleDropsTheWholeFilter(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]));
        $this->assertResponseStatusCodeSame(200);

        $removalUrl = $crawler->filter('.datagrid-chips .datagrid-chip')->attr('href');
        $crawler = $client->request('GET', (string)$removalUrl);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(0, $crawler->filter('.datagrid-chips'));

        parse_str((string)parse_url((string)$removalUrl, PHP_URL_QUERY), $remainingParameters);
        $this->assertArrayNotHasKey(self::FILTER_PARAMETER, $remainingParameters);
    }

    /**
     * @return array<string, mixed>
     */
    private function composeTwoRules(): array
    {
        return [
            self::FILTER_PARAMETER => [
                'operator' => 'and',
                'groups' => [
                    [
                        'operator' => 'and',
                        'rules' => [
                            ['filter' => 'status', 'operator' => 'in', 'value' => [ProductReviewStatusEnum::STATUS_PENDING]],
                            ['filter' => 'rating', 'operator' => 'in', 'value' => ['3']],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testResetDropsTheNarrowingFromTheUrl(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), [
            ...$this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]),
            self::SEARCH_PARAMETER => ['text' => 'canon'],
        ]);

        $this->assertResponseStatusCodeSame(200);

        $resetQuery = parse_url($crawler->filter('.datagrid-chips__reset')->attr('href'), PHP_URL_QUERY);
        parse_str((string)$resetQuery, $resetParameters);

        $this->assertArrayNotHasKey(self::FILTER_PARAMETER, $resetParameters);
        $this->assertArrayNotHasKey(self::SEARCH_PARAMETER, $resetParameters);
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
