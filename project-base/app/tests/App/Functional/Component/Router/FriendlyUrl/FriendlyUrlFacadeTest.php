<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Router\FriendlyUrl;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use App\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListData;
use Tests\App\Test\TransactionFunctionalTestCase;

final class FriendlyUrlFacadeTest extends TransactionFunctionalTestCase
{
    private const string CATEGORY_ROUTE_NAME = 'front_product_list';

    /**
     * @inject
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @inject
     */
    private CategoryFacade $categoryFacade;

    /**
     * @inject
     */
    private CategoryDataFactory $categoryDataFactory;

    public function testMainFriendlyUrlIsKeptWhenEntityIsRenamed(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $mainFriendlyUrlSlugBeforeRename = $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId())->getSlug();
        $friendlyUrlCountBeforeRename = count($this->friendlyUrlFacade->getAllByRouteNameAndEntityId(self::CATEGORY_ROUTE_NAME, $category->getId()));
        $categoryData = $this->categoryDataFactory->createFromCategory($category);
        $categoryData->name[$this->getFirstDomainLocale()] = 'Renamed electronics';

        $this->categoryFacade->edit($category->getId(), $categoryData);
        $this->em->clear();

        $mainFriendlyUrlAfterRename = $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId());
        $friendlyUrlCountAfterRename = count($this->friendlyUrlFacade->getAllByRouteNameAndEntityId(self::CATEGORY_ROUTE_NAME, $category->getId()));
        $this->assertSame($mainFriendlyUrlSlugBeforeRename, $mainFriendlyUrlAfterRename->getSlug());
        $this->assertSame($friendlyUrlCountBeforeRename, $friendlyUrlCountAfterRename);
    }

    public function testCreateFriendlyUrlsDoesNotCreateAnythingWhenMainFriendlyUrlExists(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $mainFriendlyUrlSlugBefore = $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId())->getSlug();
        $friendlyUrlCountBefore = count($this->friendlyUrlFacade->getAllByRouteNameAndEntityId(self::CATEGORY_ROUTE_NAME, $category->getId()));

        $this->friendlyUrlFacade->createFriendlyUrls(
            self::CATEGORY_ROUTE_NAME,
            $category->getId(),
            [$this->getFirstDomainLocale() => 'Completely different category name'],
        );
        $this->em->clear();

        $mainFriendlyUrlAfter = $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId());
        $friendlyUrlCountAfter = count($this->friendlyUrlFacade->getAllByRouteNameAndEntityId(self::CATEGORY_ROUTE_NAME, $category->getId()));
        $this->assertSame($mainFriendlyUrlSlugBefore, $mainFriendlyUrlAfter->getSlug());
        $this->assertSame($friendlyUrlCountBefore, $friendlyUrlCountAfter);
    }

    public function testMainFriendlyUrlsAreGeneratedForNewEntityAndKeptOnRename(): void
    {
        $categoryData = $this->categoryDataFactory->create();
        $categoryData->name[$this->getFirstDomainLocale()] = 'Guarded test category';
        $categoryData->name[$this->getSecondDomainLocale()] = 'Guarded test category second';

        $category = $this->categoryFacade->create($categoryData);

        $this->assertSame(
            'guarded-test-category',
            $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId())->getSlug(),
        );
        $this->assertSame(
            'guarded-test-category-second',
            $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::SECOND_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId())->getSlug(),
        );

        $friendlyUrlCountBeforeRename = count($this->friendlyUrlFacade->getAllByRouteNameAndEntityId(self::CATEGORY_ROUTE_NAME, $category->getId()));
        $categoryDataForRename = $this->categoryDataFactory->createFromCategory($category);
        $categoryDataForRename->name[$this->getFirstDomainLocale()] = 'Guarded test category renamed';

        $this->categoryFacade->edit($category->getId(), $categoryDataForRename);
        $this->em->clear();

        $friendlyUrlCountAfterRename = count($this->friendlyUrlFacade->getAllByRouteNameAndEntityId(self::CATEGORY_ROUTE_NAME, $category->getId()));
        $mainFriendlyUrlAfterRename = $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId());
        $this->assertSame('guarded-test-category', $mainFriendlyUrlAfterRename->getSlug());
        $this->assertSame($friendlyUrlCountBeforeRename, $friendlyUrlCountAfterRename);
    }

    public function testMainFriendlyUrlIsGeneratedWhenNameIsFilledInPreviouslyEmptyLocale(): void
    {
        $categoryData = $this->categoryDataFactory->create();
        $categoryData->name[$this->getFirstDomainLocale()] = 'First locale only category';

        $category = $this->categoryFacade->create($categoryData);

        $this->assertNull($this->friendlyUrlFacade->findMainFriendlyUrl(Domain::SECOND_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId()));

        $categoryDataWithSecondLocaleName = $this->categoryDataFactory->createFromCategory($category);
        $categoryDataWithSecondLocaleName->name[$this->getSecondDomainLocale()] = 'Second locale category name';

        $this->categoryFacade->edit($category->getId(), $categoryDataWithSecondLocaleName);
        $this->em->clear();

        $this->assertSame(
            'first-locale-only-category',
            $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId())->getSlug(),
        );
        $this->assertSame(
            'second-locale-category-name',
            $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::SECOND_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId())->getSlug(),
        );
    }

    public function testMainFriendlyUrlIsChangedByManualUrlManagement(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $originalMainFriendlyUrlSlug = $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId())->getSlug();
        $urlListDataWithNewUrl = new UrlListData();
        $urlListDataWithNewUrl->newUrls[] = [
            UrlListData::FIELD_DOMAIN => Domain::FIRST_DOMAIN_ID,
            UrlListData::FIELD_SLUG => 'manually-managed-category-url',
        ];

        $this->friendlyUrlFacade->saveUrlListFormData(self::CATEGORY_ROUTE_NAME, $category->getId(), $urlListDataWithNewUrl);
        $newFriendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug(Domain::FIRST_DOMAIN_ID, 'manually-managed-category-url');
        $this->assertNotNull($newFriendlyUrl);
        $urlListDataWithNewMain = new UrlListData();
        $urlListDataWithNewMain->mainFriendlyUrlsByDomainId[Domain::FIRST_DOMAIN_ID] = $newFriendlyUrl;
        $this->friendlyUrlFacade->saveUrlListFormData(self::CATEGORY_ROUTE_NAME, $category->getId(), $urlListDataWithNewMain);
        $this->em->clear();

        $mainFriendlyUrlAfterManualChange = $this->friendlyUrlFacade->getMainFriendlyUrl(Domain::FIRST_DOMAIN_ID, self::CATEGORY_ROUTE_NAME, $category->getId());
        $originalFriendlyUrl = $this->friendlyUrlFacade->findByDomainIdAndSlug(Domain::FIRST_DOMAIN_ID, $originalMainFriendlyUrlSlug);
        $this->assertSame('manually-managed-category-url', $mainFriendlyUrlAfterManualChange->getSlug());
        $this->assertNotNull($originalFriendlyUrl);
        $this->assertFalse($originalFriendlyUrl->isMain());
    }

    private function getSecondDomainLocale(): string
    {
        return $this->domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID)->getLocale();
    }
}
