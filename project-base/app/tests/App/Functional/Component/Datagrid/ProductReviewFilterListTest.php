<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\Test\Client;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * The filters of the product review list — a rule whose value is the operation itself (the verified
 * purchase flag) narrows the list without any value field.
 */
class ProductReviewFilterListTest extends TransactionFunctionalTestCase
{
    private const string LIST_ROUTE = 'admin_crud_product_review_list';

    private const string FILTER_PARAMETER = 'ProductReview_filter';

    public function testVerifiedPurchaseFlagNarrowsTheReviewsByItsOperationAlone(): void
    {
        $client = $this->createLoggedInClient();

        $allRows = $this->listRows($client, []);
        $verifiedRows = $this->listRows($client, $this->composeRule('isVerifiedPurchase', 'yes'));
        $unverifiedRows = $this->listRows($client, $this->composeRule('isVerifiedPurchase', 'no'));

        $this->assertGreaterThan(0, $verifiedRows->count());
        $this->assertGreaterThan(0, $unverifiedRows->count());
        $this->assertLessThan($allRows->count(), $verifiedRows->count());
    }

    public function testRuleOfTheFlagRendersNoValueField(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client), $this->composeRule('isVerifiedPurchase', 'yes'));

        $this->assertResponseStatusCodeSame(200);
        // the prototypes outside the form carry value inputs of their own, only the composed rule inside the form matters
        $composedRule = $crawler->filter('form [data-datagrid-filter-target="rule"]');
        $this->assertCount(1, $composedRule);
        $this->assertSame(0, $composedRule->filter('[data-datagrid-filter-target="value"] select, [data-datagrid-filter-target="value"] input')->count());
        $this->assertSame('yes', $composedRule->filter('[data-datagrid-filter-target="operatorSelect"] option[selected]')->attr('value'));
    }

    /**
     * @return array<string, mixed>
     */
    private function composeRule(string $filter, string $operator): array
    {
        return [
            self::FILTER_PARAMETER => [
                'groups' => [
                    ['rules' => [['filter' => $filter, 'operator' => $operator]]],
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
