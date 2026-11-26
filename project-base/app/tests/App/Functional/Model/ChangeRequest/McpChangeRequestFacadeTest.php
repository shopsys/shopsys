<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\ChangeRequest;

use App\DataFixtures\Demo\AdministratorDataFixture;
use DateTimeImmutable;
use Mcp\Exception\ToolCallException;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Model\ChangeRequest\Exception\McpChangeRequestNotFoundException;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequest;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestData;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestDataFactory;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFacade;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFactory;
use Shopsys\McpBundle\Tool\Blog\EditBlogArticleArguments;
use Shopsys\McpBundle\Tool\Blog\EditBlogArticleTool;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;

class McpChangeRequestFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private McpChangeRequestFacade $mcpChangeRequestFacade;

    /**
     * @inject
     */
    private McpChangeRequestFactory $mcpChangeRequestFactory;

    /**
     * @inject
     */
    private McpChangeRequestDataFactory $mcpChangeRequestDataFactory;

    /**
     * @inject
     */
    private EditBlogArticleTool $editBlogArticleTool;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    public function testOwnerGetsOpenChangeRequest(): void
    {
        $owner = $this->getAdministrator(AdministratorDataFixture::ADMINISTRATOR);
        $mcpChangeRequest = $this->createChangeRequest($owner);

        $foundMcpChangeRequest = $this->mcpChangeRequestFacade->getOpenByIdAndAdministrator($mcpChangeRequest->getId(), $owner);

        $this->assertSame($mcpChangeRequest, $foundMcpChangeRequest);
    }

    public function testDiscardDeletesChangeRequest(): void
    {
        $owner = $this->getAdministrator(AdministratorDataFixture::ADMINISTRATOR);
        $mcpChangeRequest = $this->createChangeRequest($owner);
        $mcpChangeRequestId = $mcpChangeRequest->getId();

        $this->mcpChangeRequestFacade->discard($mcpChangeRequest);

        $this->assertSame(0, $this->mcpChangeRequestFacade->countOpenByAdministrator($owner));
        $this->expectException(McpChangeRequestNotFoundException::class);
        $this->mcpChangeRequestFacade->getOpenByIdAndAdministrator($mcpChangeRequestId, $owner);
    }

    public function testApproveDoesNothingWhenChangeRequestWasDiscardedMeanwhile(): void
    {
        $mcpChangeRequest = $this->createChangeRequest($this->getAdministrator(AdministratorDataFixture::ADMINISTRATOR));
        $this->mcpChangeRequestFacade->discard($mcpChangeRequest);

        $this->assertFalse($this->mcpChangeRequestFacade->approve($mcpChangeRequest));
    }

    public function testOtherAdministratorCannotGetChangeRequest(): void
    {
        $mcpChangeRequest = $this->createChangeRequest($this->getAdministrator(AdministratorDataFixture::ADMINISTRATOR));
        $otherAdministrator = $this->getAdministrator(AdministratorDataFixture::SUPERVISOR);

        $this->expectException(McpChangeRequestNotFoundException::class);

        $this->mcpChangeRequestFacade->getOpenByIdAndAdministrator($mcpChangeRequest->getId(), $otherAdministrator);
    }

    public function testOtherAdministratorDoesNotSeeChangeRequestInList(): void
    {
        $this->createChangeRequest($this->getAdministrator(AdministratorDataFixture::ADMINISTRATOR));
        $otherAdministrator = $this->getAdministrator(AdministratorDataFixture::SUPERVISOR);

        $this->assertSame([], $this->mcpChangeRequestFacade->getOpenByAdministratorPaginationResult($otherAdministrator, 1, 20)->getResults());
        $this->assertSame(0, $this->mcpChangeRequestFacade->countOpenByAdministrator($otherAdministrator));
    }

    public function testListReturnsOnlyRequestedPage(): void
    {
        $owner = $this->getAdministrator(AdministratorDataFixture::ADMINISTRATOR);
        $this->createChangeRequest($owner);
        $this->createChangeRequest($owner);
        $this->createChangeRequest($owner);

        $paginationResult = $this->mcpChangeRequestFacade->getOpenByAdministratorPaginationResult($owner, 2, 2);

        $this->assertCount(1, $paginationResult->getResults());
        $this->assertSame(3, $paginationResult->getTotalCount());
    }

    public function testCreateFailsWhenTooManyChangesWaitForApproval(): void
    {
        $owner = $this->getAdministrator(AdministratorDataFixture::ADMINISTRATOR);
        $this->tokenStorage->setToken(new UsernamePasswordToken($owner, 'administration', $owner->getRoles()));

        for ($i = 0; $i < 500; $i++) {
            $this->em->persist($this->mcpChangeRequestFactory->create($this->createChangeRequestData($owner)));
        }

        $this->em->flush();

        $this->expectException(ToolCallException::class);
        $this->expectExceptionMessage('500 changes already wait for approval');

        $this->mcpChangeRequestFacade->create($this->editBlogArticleTool, new EditBlogArticleArguments(blogArticleId: 1));
    }

    private function createChangeRequest(Administrator $administrator): McpChangeRequest
    {
        $mcpChangeRequest = $this->mcpChangeRequestFactory->create($this->createChangeRequestData($administrator));
        $this->em->persist($mcpChangeRequest);
        $this->em->flush();

        return $mcpChangeRequest;
    }

    private function createChangeRequestData(Administrator $administrator): McpChangeRequestData
    {
        $mcpChangeRequestData = $this->mcpChangeRequestDataFactory->create();
        $mcpChangeRequestData->administrator = $administrator;
        $mcpChangeRequestData->toolName = 'editBlogArticle';
        $mcpChangeRequestData->argumentsClass = stdClass::class;
        $mcpChangeRequestData->arguments = ['blogArticleId' => 1];
        $mcpChangeRequestData->change = ['subject' => 'Blog article', 'subjectParameters' => [], 'subjectUrl' => null, 'fields' => []];
        $mcpChangeRequestData->createdAt = new DateTimeImmutable();

        return $mcpChangeRequestData;
    }

    private function getAdministrator(string $reference): Administrator
    {
        return $this->getReference($reference, Administrator::class);
    }
}
