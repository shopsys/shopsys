<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;
use Shopsys\AdministrationBundle\Component\Search\SearchConfigFactory;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class CrudControllerSearchTest extends TestCase
{
    public function testSearchConfigIsBuiltFromControllerHook(): void
    {
        $crudController = $this->createCrudController();

        $searchConfig = $crudController->getSearchConfigForTest();

        $this->assertTrue($searchConfig->isQuickSearchEnabled());
        $this->assertSame(['name'], $searchConfig->getQuickSearchDefinition()->getFields());
    }

    public function testExtensionCanReconfigureSearch(): void
    {
        $crudController = $this->createCrudController([new TestSearchCrudControllerExtension()]);

        $searchConfig = $crudController->getSearchConfigForTest();

        $this->assertTrue($searchConfig->isQuickSearchEnabled());
        $this->assertSame(['name', 'catnum'], $searchConfig->getQuickSearchDefinition()->getFields());
    }

    public function testQuickSearchTextIsTrimmed(): void
    {
        $crudController = $this->createCrudController();
        $crudController->requestStack = $this->createRequestStackWithQuery([SearchConfig::QUICK_SEARCH_QUERY_PARAMETER => '  foo  ']);

        $this->assertSame('foo', $crudController->getQuickSearchTextForTest());
    }

    public function testQuickSearchTextIsNullWhenMissingOrBlank(): void
    {
        $crudController = $this->createCrudController();

        $crudController->requestStack = $this->createRequestStackWithQuery([]);
        $this->assertNull($crudController->getQuickSearchTextForTest());

        $crudController->requestStack = $this->createRequestStackWithQuery([SearchConfig::QUICK_SEARCH_QUERY_PARAMETER => '   ']);
        $this->assertNull($crudController->getQuickSearchTextForTest());
    }

    /**
     * @param \Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension[] $extensions
     */
    private function createCrudController(array $extensions = []): TestSearchCrudController
    {
        $crudController = new TestSearchCrudController();
        $crudController->setDefinition(new Definition(
            TestSearchCrudController::class,
            'TestSearchCrudController',
            stdClass::class,
            'Search test',
            (new CrudConfig('Search test'))->getConfig(),
            $extensions,
            [],
        ));
        $crudController->searchConfigFactory = new SearchConfigFactory();

        return $crudController;
    }

    /**
     * @param array<string, string> $queryParameters
     */
    private function createRequestStackWithQuery(array $queryParameters): RequestStack
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/', 'GET', $queryParameters));

        return $requestStack;
    }
}

final class TestSearchCrudController extends AbstractCrudController
{
    #[Override]
    public function configureSearch(SearchConfig $search): void
    {
        $search->enableQuickSearch(fields: ['name']);
    }

    public function getSearchConfigForTest(): SearchConfig
    {
        return $this->getSearchConfig();
    }

    public function getQuickSearchTextForTest(): ?string
    {
        return $this->getQuickSearchText();
    }
}

final class TestSearchCrudControllerExtension extends AbstractCrudControllerExtension
{
    #[Override]
    public function configureSearch(SearchConfig $search): void
    {
        $search->enableQuickSearch(fields: ['name', 'catnum']);
    }
}
