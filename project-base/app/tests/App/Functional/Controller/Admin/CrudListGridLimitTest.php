<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Admin;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Tests\App\Test\TransactionFunctionalTestCase;

final class CrudListGridLimitTest extends TransactionFunctionalTestCase
{
    private const string BLOG_ARTICLE_AUTHOR_LIST_GRID_ID = 'BlogArticleAuthor';

    public function testCrudListRemembersAndRestoresPageSizeOfAdministrator(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient([], ['HTTP_HOST' => '127.0.0.1:8000']);
        $administrationClientTestHelper = new AdministrationClientTestHelper($client);
        $superadministrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $administrationClientTestHelper->logInAdministrator($superadministrator->getId());
        $blogArticleAuthorListPath = $administrationClientTestHelper->generatePath('admin_crud_blog_article_author_list');

        $client->request('GET', $blogArticleAuthorListPath, [
            Grid::GET_PARAMETER => [self::BLOG_ARTICLE_AUTHOR_LIST_GRID_ID => ['limit' => 100]],
        ]);

        $this->assertResponseStatusCodeSame(200);

        $crawler = $client->request('GET', $blogArticleAuthorListPath);

        $this->assertResponseStatusCodeSame(200);
        $this->assertSame('100', trim($crawler->filter('.text-secondary > .dropdown > a.dropdown-toggle')->text()));
    }
}
