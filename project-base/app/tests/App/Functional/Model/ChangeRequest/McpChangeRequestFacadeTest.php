<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\ChangeRequest;

use App\DataFixtures\Demo\AdministratorDataFixture;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Model\ChangeRequest\Exception\McpChangeRequestNotFoundException;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequest;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestDataFactory;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFacade;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFactory;
use stdClass;
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

        $this->assertSame([], $this->mcpChangeRequestFacade->getOpenByAdministrator($otherAdministrator));
        $this->assertSame(0, $this->mcpChangeRequestFacade->countOpenByAdministrator($otherAdministrator));
    }

    private function createChangeRequest(Administrator $administrator): McpChangeRequest
    {
        $mcpChangeRequestData = $this->mcpChangeRequestDataFactory->create();
        $mcpChangeRequestData->administrator = $administrator;
        $mcpChangeRequestData->toolName = 'editPublishedBlogArticle';
        $mcpChangeRequestData->argumentsClass = stdClass::class;
        $mcpChangeRequestData->arguments = ['blogArticleId' => 1];
        $mcpChangeRequestData->change = ['subject' => 'Blog article', 'subjectParameters' => [], 'subjectUrl' => null, 'fields' => []];
        $mcpChangeRequestData->createdAt = new DateTimeImmutable();

        $mcpChangeRequest = $this->mcpChangeRequestFactory->create($mcpChangeRequestData);
        $this->em->persist($mcpChangeRequest);
        $this->em->flush();

        return $mcpChangeRequest;
    }

    private function getAdministrator(string $reference): Administrator
    {
        return $this->getReference($reference, Administrator::class);
    }
}
