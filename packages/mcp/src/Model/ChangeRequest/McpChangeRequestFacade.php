<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\ChangeRequest;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Mcp\Exception\ToolCallException;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Component\Logger\McpToolCallLogger;
use Shopsys\McpBundle\Component\WriteTool\McpApprovableToolInterface;
use Shopsys\McpBundle\Model\ChangeRequest\Exception\McpChangeRequestApprovalFailedException;
use Shopsys\McpBundle\Model\ChangeRequest\Exception\McpChangeRequestNotFoundException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;
use Webmozart\Assert\Assert;

class McpChangeRequestFacade
{
    protected const int MAX_OPEN_COUNT_PER_ADMINISTRATOR = 500;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly McpChangeRequestRepository $mcpChangeRequestRepository,
        protected readonly McpChangeRequestFactory $mcpChangeRequestFactory,
        protected readonly McpChangeRequestDataFactory $mcpChangeRequestDataFactory,
        protected readonly McpToolCallLogger $mcpToolCallLogger,
        protected readonly ClockInterface $clock,
        protected readonly Security $security,
        #[AutowireLocator(McpApprovableToolInterface::TAG, defaultIndexMethod: 'getToolName')]
        protected readonly ContainerInterface $approvableToolLocator,
        protected readonly NormalizerInterface $normalizer,
        protected readonly DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @template TArguments of object
     * @param \Shopsys\McpBundle\Component\WriteTool\McpApprovableToolInterface<TArguments> $tool
     * @param TArguments $arguments
     */
    public function create(McpApprovableToolInterface $tool, object $arguments): McpChangeRequest
    {
        $administrator = $this->getCurrentAdministrator();

        if ($this->countOpenByAdministrator($administrator) >= static::MAX_OPEN_COUNT_PER_ADMINISTRATOR) {
            throw new ToolCallException(sprintf(
                '%d changes already wait for approval in administration, ask the administrator to approve or discard them first.',
                static::MAX_OPEN_COUNT_PER_ADMINISTRATOR,
            ));
        }

        $mcpChangeRequestData = $this->mcpChangeRequestDataFactory->create();
        $mcpChangeRequestData->administrator = $administrator;
        $mcpChangeRequestData->toolName = $tool::getToolName();
        $mcpChangeRequestData->argumentsClass = $arguments::class;
        $mcpChangeRequestData->arguments = $this->normalizer->normalize($arguments);
        $mcpChangeRequestData->change = $tool->previewChange($arguments)->toArray();
        $mcpChangeRequestData->createdAt = $this->clock->now();

        $mcpChangeRequest = $this->mcpChangeRequestFactory->create($mcpChangeRequestData);
        $this->em->persist($mcpChangeRequest);
        $this->em->flush();

        $this->mcpToolCallLogger->logChangeRequestCreated($mcpChangeRequest);

        return $mcpChangeRequest;
    }

    public function countOpenByAdministrator(Administrator $administrator): int
    {
        return $this->mcpChangeRequestRepository->countOpenByAdministrator($administrator);
    }

    public function getOpenByAdministratorPaginationResult(
        Administrator $administrator,
        int $page,
        int $pageSize,
    ): PaginationResult {
        return $this->mcpChangeRequestRepository->getOpenByAdministratorPaginationResult($administrator, $page, $pageSize);
    }

    public function getOpenByIdAndAdministrator(int $id, Administrator $administrator): McpChangeRequest
    {
        $mcpChangeRequest = $this->mcpChangeRequestRepository->findOpenByIdAndAdministrator($id, $administrator);

        if ($mcpChangeRequest === null) {
            throw new McpChangeRequestNotFoundException(sprintf('Open MCP change request with ID %d not found.', $id));
        }

        return $mcpChangeRequest;
    }

    public function approve(McpChangeRequest $mcpChangeRequest): bool
    {
        $isApproved = $this->callOrFail(
            $mcpChangeRequest,
            fn () => $this->em->wrapInTransaction(fn () => $this->applyAndApprove($mcpChangeRequest)),
        );

        if ($isApproved) {
            $this->mcpToolCallLogger->logChangeRequestApproved($mcpChangeRequest);
        }

        return $isApproved;
    }

    public function discard(McpChangeRequest $mcpChangeRequest): void
    {
        $this->mcpChangeRequestRepository->delete($mcpChangeRequest);

        $this->mcpToolCallLogger->logChangeRequestDiscarded($mcpChangeRequest);
    }

    public function refresh(McpChangeRequest $mcpChangeRequest): void
    {
        Assert::true($mcpChangeRequest->isRefreshable());

        $change = $this->callOrFail(
            $mcpChangeRequest,
            fn () => $this->getApprovableTool($mcpChangeRequest)->previewChange($this->getArguments($mcpChangeRequest)),
        );

        $mcpChangeRequest->refresh($change->toArray());
        $this->em->flush();

        $this->mcpToolCallLogger->logChangeRequestRefreshed($mcpChangeRequest);
    }

    protected function applyAndApprove(McpChangeRequest $mcpChangeRequest): bool
    {
        if ($this->mcpChangeRequestRepository->findPendingByIdForUpdate($mcpChangeRequest->getId()) === null) {
            return false;
        }

        $tool = $this->getApprovableTool($mcpChangeRequest);
        $arguments = $this->getArguments($mcpChangeRequest);

        if ($tool->previewChange($arguments)->toArray() !== $mcpChangeRequest->getChange()) {
            throw new McpChangeRequestApprovalFailedException(t('The data has changed since the request was created.'));
        }

        $tool->apply($arguments);
        $this->mcpChangeRequestRepository->delete($mcpChangeRequest);

        return true;
    }

    /**
     * @template T
     * @param \Closure(): T $callback
     * @return T
     */
    protected function callOrFail(McpChangeRequest $mcpChangeRequest, Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (McpChangeRequestApprovalFailedException $exception) {
            $this->fail($mcpChangeRequest, $exception->getMessage(), $exception);
        } catch (ToolCallException $exception) {
            $this->markInapplicable($mcpChangeRequest, t('The change can no longer be applied, discard it.'), $exception);
        } catch (Throwable $exception) {
            $this->fail($mcpChangeRequest, t('The change could not be applied.'), $exception);
        }
    }

    protected function getApprovableTool(McpChangeRequest $mcpChangeRequest): McpApprovableToolInterface
    {
        if (!$this->approvableToolLocator->has($mcpChangeRequest->getToolName())) {
            throw new McpChangeRequestApprovalFailedException(t('The action no longer exists.'));
        }

        return $this->approvableToolLocator->get($mcpChangeRequest->getToolName());
    }

    protected function getArguments(McpChangeRequest $mcpChangeRequest): object
    {
        return $this->denormalizer->denormalize($mcpChangeRequest->getArguments(), $mcpChangeRequest->getArgumentsClass());
    }

    protected function getCurrentAdministrator(): Administrator
    {
        $administrator = $this->security->getUser();
        Assert::isInstanceOf($administrator, Administrator::class);

        return $administrator;
    }

    protected function fail(McpChangeRequest $mcpChangeRequest, string $errorMessage, Throwable $exception): never
    {
        $mcpChangeRequest->fail($errorMessage);
        $this->saveFailure($mcpChangeRequest, $errorMessage, $exception);
    }

    protected function markInapplicable(
        McpChangeRequest $mcpChangeRequest,
        string $errorMessage,
        Throwable $exception,
    ): never {
        $mcpChangeRequest->markInapplicable($errorMessage);
        $this->saveFailure($mcpChangeRequest, $errorMessage, $exception);
    }

    protected function saveFailure(
        McpChangeRequest $mcpChangeRequest,
        string $errorMessage,
        Throwable $exception,
    ): never {
        // A failed approval closes the entity manager, which can no longer flush but can still run a DQL update.
        $this->mcpChangeRequestRepository->saveFailure($mcpChangeRequest);

        $this->mcpToolCallLogger->logChangeRequestFailed($mcpChangeRequest, $exception);

        throw new McpChangeRequestApprovalFailedException($errorMessage, 0, $exception);
    }
}
