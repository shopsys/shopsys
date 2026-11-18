<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterGroupType;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\Test\TransactionFunctionalTestCase;

class FilterListTest extends TransactionFunctionalTestCase
{
    use ProductReviewListTrait;

    private const string FILTER_PARAMETER = 'ProductReview_filter';

    /**
     * @inject
     */
    private ProductReviewEnabledChecker $productReviewEnabledChecker;

    public function testComposedRuleListsExactlyTheMatchingReviews(): void
    {
        $client = $this->createLoggedInClient();
        $pendingReviews = $this->em->getRepository(ProductReview::class)->findBy([
            'status' => ProductReviewStatusEnum::STATUS_PENDING,
            'domainId' => $this->productReviewEnabledChecker->getEnabledDomainIds(),
        ]);
        $this->assertNotEmpty($pendingReviews, 'The demo data have to contain pending reviews.');

        $pendingRows = $this->listRows($client, $this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]));

        $this->assertCount(count($pendingReviews), $pendingRows);

        foreach ($pendingReviews as $pendingReview) {
            $this->assertCount(1, $pendingRows->filter(sprintf('a[href="%s"]', $this->generateEditPath($client, $pendingReview))));
        }
    }

    public function testSearchTabIsOpenWithoutComposedFilter(): void
    {
        $crawler = $this->requestList();

        $this->assertCount(1, $crawler->filter('a[data-bs-toggle="tab"][href="#ProductReview_search_tab"].active'));
        $this->assertCount(1, $crawler->filter('a[data-bs-toggle="tab"][href="#ProductReview_filter_tab"]'));
    }

    public function testFilterStartsWithOneGroupOfOneRuleWaitingToBeFilled(): void
    {
        $crawler = $this->requestList();

        $this->assertGreaterThan(0, $crawler->filter('[data-controller="datagrid-filter"]')->count());
        $this->assertCount(1, $crawler->filter('form [data-datagrid-filter-target="group"]'));
        $this->assertCount(1, $crawler->filter('form [data-datagrid-filter-target="rule"]'));
    }

    public function testPrototypeOfEachFilterArityIsRendered(): void
    {
        $crawler = $this->requestList();
        $listPrototype = $crawler->filter('template[data-datagrid-filter-target="prototype"][data-filter="status"][data-arity="list"]');

        $this->assertGreaterThan(0, $listPrototype->count());
        $this->assertGreaterThan(0, $crawler->filter('template[data-datagrid-filter-target="prototype"][data-filter="isVerifiedPurchase"][data-arity="none"]')->count());
        // the page renames the fields of a prototype to those of a rule by this part of their names
        $this->assertCount(1, $listPrototype->filter('select[name$="[prototypes][status][list][operator]"]'));
        $this->assertCount(1, $listPrototype->filter('select[name$="[prototypes][status][list][value][]"]'));
        $this->assertCount(0, $listPrototype->filter('select[name$="[prototypes][status][list][filter]"]'));
    }

    public function testComposedFilterOpensItsTab(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeFilter('status', 'in', [ProductReviewStatusEnum::STATUS_PENDING]));

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(1, $crawler->filter('a[data-bs-toggle="tab"][href="#ProductReview_filter_tab"].active'));
        $this->assertCount(0, $crawler->filter('a[data-bs-toggle="tab"][href="#ProductReview_search_tab"].active'));
    }

    public function testRuleOfTheFlagRendersNoValueField(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeFilter('isVerifiedPurchase', 'yes'));

        $this->assertResponseStatusCodeSame(200);
        // the prototypes outside the form carry value inputs of their own
        $composedRule = $crawler->filter('form [data-datagrid-filter-target="rule"]');
        $this->assertCount(1, $composedRule);
        $this->assertSame(0, $composedRule->filter('[data-datagrid-filter-target="value"] select, [data-datagrid-filter-target="value"] input')->count());
        $this->assertSame('yes', $composedRule->filter('[data-datagrid-filter-target="operatorSelect"] option[selected]')->attr('value'));
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

    public function testRulesOverTheLimitAreReportedInsteadOfNarrowing(): void
    {
        $client = $this->createLoggedInClient();
        $rule = ['filter' => 'status', 'operator' => 'in', 'value' => [ProductReviewStatusEnum::STATUS_PENDING]];

        $allRows = $this->listRows($client, []);
        $crawler = $client->request('GET', $this->generateListPath($client), [
            self::FILTER_PARAMETER => [
                'groups' => [
                    ['rules' => array_fill(0, FilterGroupType::MAX_RULES + 1, $rule)],
                ],
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSame($allRows->count(), $crawler->filter('tr.js-grid-row')->count());
        $this->assertSelectorTextContains('[data-controller="datagrid-filter"] .alert-danger', 'A group of rules can have at most 10 rules.');
    }

    /**
     * Looking the product up would fail on an identifier beyond the range of its column, so the rule is reported
     * as invalid instead.
     */
    public function testProductIdentifierBeyondTheRangeOfTheDatabaseIsInvalidRule(): void
    {
        $client = $this->createLoggedInClient();

        $client->request('GET', $this->generateListPath($client), $this->composeFilter('product', 'equals', '2147483648'));

        $this->assertResponseStatusCodeSame(422);
    }

    private function requestList(): Crawler
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client));

        $this->assertResponseStatusCodeSame(200);

        return $crawler;
    }

    /**
     * @return array<string, mixed>
     */
    private function composeFilter(string $filter, string $operator, mixed $value = null): array
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
}
