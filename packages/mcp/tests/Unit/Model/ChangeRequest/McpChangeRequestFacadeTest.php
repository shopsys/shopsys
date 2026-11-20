<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Model\ChangeRequest;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Mcp\Exception\ToolCallException;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionProperty;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;
use Shopsys\McpBundle\Model\ChangeRequest\Exception\McpChangeRequestApprovalFailedException;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequest;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestData;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestDataFactory;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFacade;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFactory;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestRepository;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestStatusEnum;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Tests\McpBundle\Unit\Component\WriteTool\data\TestApprovableWriteTool;
use Tests\McpBundle\Unit\Component\WriteTool\data\TestApprovableWriteToolArguments;

class McpChangeRequestFacadeTest extends TestCase
{
    protected const string NOW = '2026-11-16 12:00:00';

    protected TestApprovableWriteTool $tool;

    protected ?McpChangeRequest $storedMcpChangeRequest = null;

    #[Override]
    protected function setUp(): void
    {
        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translator);

        $this->tool = new TestApprovableWriteTool();
    }

    public function testApproveExecutesToolAndDeletesRequest(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();

        $isApproved = $this->createFacade()->approve($mcpChangeRequest);

        $this->assertTrue($isApproved);
        $this->assertNull($this->storedMcpChangeRequest);
        $this->assertEquals([new TestApprovableWriteToolArguments(1, 'New name')], $this->tool->appliedArguments);
    }

    public function testApproveDoesNotExecuteToolAgainWhenRequestWasResolvedMeanwhile(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();
        $this->storedMcpChangeRequest = null;

        $isApproved = $this->createFacade()->approve($mcpChangeRequest);

        $this->assertFalse($isApproved);
        $this->assertSame([], $this->tool->appliedArguments);
    }

    public function testApproveFailsWhenToolNoLongerExists(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();
        $facade = $this->createFacade(hasTool: false);

        $this->assertApprovalFails($facade, $mcpChangeRequest, 'The action no longer exists.');
    }

    public function testApproveFailsWhenDataChanged(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();
        $this->tool->currentName = 'Name changed in administration';

        $this->assertApprovalFails($this->createFacade(), $mcpChangeRequest, 'The data has changed since the request was created.');
        $this->assertSame([], $this->tool->appliedArguments);
    }

    public function testApproveFailsAsNoLongerApplicableWhenToolRejectsStoredInput(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();
        $this->tool->applyException = new ToolCallException('The article no longer exists.');

        $this->assertApprovalFails(
            $this->createFacade(),
            $mcpChangeRequest,
            'The change can no longer be applied, discard it.',
            McpChangeRequestStatusEnum::INAPPLICABLE,
        );
        $this->assertFalse($mcpChangeRequest->isRefreshable());
    }

    public function testApproveFailsWithGenericMessageOnUnexpectedError(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();
        $this->tool->applyException = new RuntimeException('SQLSTATE[23505]: Unique violation');

        $this->assertApprovalFails($this->createFacade(), $mcpChangeRequest, 'The change could not be applied.');
    }

    public function testRefreshDescribesChangeAgainstCurrentDataAndAllowsApproval(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();
        $this->tool->currentName = 'Name changed in administration';
        $facade = $this->createFacade();
        $this->assertApprovalFails($facade, $mcpChangeRequest, 'The data has changed since the request was created.');

        $facade->refresh($mcpChangeRequest);

        $this->assertSame(McpChangeRequestStatusEnum::PENDING, $mcpChangeRequest->getStatus());
        $this->assertNull($mcpChangeRequest->getErrorMessage());
        $this->assertSame('Name changed in administration', $mcpChangeRequest->getChange()['fields'][0]['before']);

        $facade->approve($mcpChangeRequest);

        $this->assertNull($this->storedMcpChangeRequest);
        $this->assertCount(1, $this->tool->appliedArguments);
    }

    public function testRefreshFailsWhenToolNoLongerExists(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();
        $mcpChangeRequest->fail('The data has changed since the request was created.');

        try {
            $this->createFacade(hasTool: false)->refresh($mcpChangeRequest);
            $this->fail('Refresh was expected to fail.');
        } catch (McpChangeRequestApprovalFailedException $exception) {
            $this->assertSame('The action no longer exists.', $exception->getMessage());
        }

        $this->assertSame(McpChangeRequestStatusEnum::FAILED, $mcpChangeRequest->getStatus());
        $this->assertSame('The action no longer exists.', $mcpChangeRequest->getErrorMessage());
    }

    public function testDiscardDeletesRequest(): void
    {
        $mcpChangeRequest = $this->createChangeRequest();

        $this->createFacade()->discard($mcpChangeRequest);

        $this->assertNull($this->storedMcpChangeRequest);
        $this->assertSame([], $this->tool->appliedArguments);
    }

    protected function assertApprovalFails(
        McpChangeRequestFacade $facade,
        McpChangeRequest $mcpChangeRequest,
        string $expectedMessage,
        string $expectedStatus = McpChangeRequestStatusEnum::FAILED,
    ): void {
        try {
            $facade->approve($mcpChangeRequest);
            $this->fail('Approval was expected to fail.');
        } catch (McpChangeRequestApprovalFailedException $exception) {
            $this->assertSame($expectedMessage, $exception->getMessage());
        }

        $this->assertSame($expectedStatus, $mcpChangeRequest->getStatus());
        $this->assertSame($expectedMessage, $mcpChangeRequest->getErrorMessage());
        $this->assertSame($mcpChangeRequest, $this->storedMcpChangeRequest);
    }

    protected function createChangeRequest(): McpChangeRequest
    {
        $arguments = new TestApprovableWriteToolArguments(1, 'New name');

        $mcpChangeRequestData = new McpChangeRequestData();
        $mcpChangeRequestData->administrator = $this->createStub(Administrator::class);
        $mcpChangeRequestData->toolName = TestApprovableWriteTool::TOOL_NAME;
        $mcpChangeRequestData->argumentsClass = TestApprovableWriteToolArguments::class;
        $mcpChangeRequestData->arguments = ['articleId' => 1, 'name' => 'New name'];
        $mcpChangeRequestData->change = $this->tool->describeChange($arguments)->toArray();
        $mcpChangeRequestData->createdAt = new DateTimeImmutable('2026-11-16 10:00:00');

        $mcpChangeRequest = new McpChangeRequest($mcpChangeRequestData);
        (new ReflectionProperty(McpChangeRequest::class, 'id'))->setValue($mcpChangeRequest, 1);
        $this->storedMcpChangeRequest = $mcpChangeRequest;

        return $mcpChangeRequest;
    }

    protected function createFacade(bool $hasTool = true): McpChangeRequestFacade
    {
        $tool = $this->tool;
        $repository = $this->createStub(McpChangeRequestRepository::class);
        $repository->method('findPendingByIdForUpdate')->willReturnCallback(
            fn (): ?McpChangeRequest => $this->storedMcpChangeRequest?->isPending() === true ? $this->storedMcpChangeRequest : null,
        );
        $repository->method('delete')->willReturnCallback(function (): void {
            $this->storedMcpChangeRequest = null;
        });

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('wrapInTransaction')->willReturnCallback(static fn (callable $callback): mixed => $callback($em));

        return new McpChangeRequestFacade(
            $em,
            $repository,
            $this->createStub(McpChangeRequestFactory::class),
            new McpChangeRequestDataFactory(),
            new McpToolCallLogger(new NullLogger()),
            new MockClock(self::NOW),
            $this->createStub(Security::class),
            new ServiceLocator($hasTool ? [TestApprovableWriteTool::TOOL_NAME => static fn () => $tool] : []),
            new ObjectNormalizer(),
            new ObjectNormalizer(),
        );
    }
}
