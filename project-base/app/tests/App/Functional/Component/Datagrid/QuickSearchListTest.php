<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Datagrid;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Tests\App\Test\TransactionFunctionalTestCase;

class QuickSearchListTest extends TransactionFunctionalTestCase
{
    use ProductReviewListTrait;

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

    public function testReviewIsFoundByItsUuid(): void
    {
        $client = $this->createLoggedInClient();
        $review = $this->getFirstDomainReview();

        $foundRows = $this->listRows($client, [self::SEARCH_PARAMETER => ['text' => mb_strtoupper($review->getUuid())]]);

        $this->assertCount(1, $foundRows);
        $this->assertCount(1, $foundRows->filter(sprintf('a[href="%s"]', $this->generateEditPath($client, $review))));
    }

    /**
     * A part of a UUID is no UUID, so the database is never asked to compare it with one.
     */
    public function testPartOfUuidFindsNothing(): void
    {
        $client = $this->createLoggedInClient();
        $review = $this->getFirstDomainReview();

        $foundRows = $this->listRows($client, [self::SEARCH_PARAMETER => ['text' => substr($review->getUuid(), 0, 13)]]);

        $this->assertCount(0, $foundRows);
    }

    public function testPlaceholderNamesWhatIsSearchedIn(): void
    {
        $client = $this->createLoggedInClient();

        $crawler = $client->request('GET', $this->generateListPath($client));

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame(
            'Search in: Product name or catalog number, Reviewer name, UUID',
            $crawler->filter(sprintf('input[name="%s[text]"]', self::SEARCH_PARAMETER))->attr('placeholder'),
        );
    }

    private function getFirstDomainReview(): ProductReview
    {
        $review = $this->em->getRepository(ProductReview::class)->findOneBy(['domainId' => Domain::FIRST_DOMAIN_ID]);
        $this->assertNotNull($review, 'The demo data have to contain a review on the first domain.');

        return $review;
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
}
