<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Controller;

use Exception;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Shopsys\FrameworkBundle\Component\Utils\UserFacingExceptionInterface;
use stdClass;
use Throwable;

final class CrudUserFacingExceptionTest extends TestCase
{
    public function testUserFacingExceptionMessageReplacesTheGenericErrorFlash(): void
    {
        $flashMessageServiceMock = $this->createFlashMessageServiceMock(hasErrorMessages: false);
        $flashMessageServiceMock->expects($this->once())->method('addErrorFlash')->with('Default store cannot be removed');
        $flashMessageServiceMock->expects($this->never())->method('addErrorFlashTwig');

        $this->createCrudController($flashMessageServiceMock)->addErrorFlashForFailedActionForTest(
            new TestUserFacingException('Default store cannot be removed'),
            'An error occurred while deleting <strong>{{ objectName }}</strong>.',
            ['objectName' => 'Ostrava'],
        );
    }

    public function testOtherExceptionsShowTheGenericErrorFlash(): void
    {
        $flashMessageServiceMock = $this->createFlashMessageServiceMock(hasErrorMessages: false);
        $flashMessageServiceMock->expects($this->never())->method('addErrorFlash');
        $flashMessageServiceMock
            ->expects($this->once())
            ->method('addErrorFlashTwig')
            ->with('An error occurred while deleting <strong>{{ objectName }}</strong>.', ['objectName' => 'Ostrava']);

        $this->createCrudController($flashMessageServiceMock)->addErrorFlashForFailedActionForTest(
            new RuntimeException('database is down'),
            'An error occurred while deleting <strong>{{ objectName }}</strong>.',
            ['objectName' => 'Ostrava'],
        );
    }

    public function testErrorFlashOfAnExtensionHookIsKept(): void
    {
        $flashMessageServiceMock = $this->createFlashMessageServiceMock(hasErrorMessages: true);
        $flashMessageServiceMock->expects($this->never())->method('addErrorFlash');
        $flashMessageServiceMock->expects($this->never())->method('addErrorFlashTwig');

        $this->createCrudController($flashMessageServiceMock)->addErrorFlashForFailedActionForTest(
            new TestUserFacingException('Default store cannot be removed'),
            'An error occurred while deleting.',
        );
    }

    public function testUserFacingExceptionIsNotLoggedAsError(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())->method('error');

        $this->createLoggingCrudController($loggerMock)->logActionErrorForTest(ActionType::DELETE, new TestUserFacingException('Default store cannot be removed'), 1);
    }

    public function testOtherExceptionIsLoggedAsErrorWithTheEntityId(): void
    {
        $exception = new RuntimeException('database is down');
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('error')
            ->with('Error from CrudController while running delete action', $this->callback(
                static fn (array $context): bool => $context['exception'] === $exception
                    && $context['entityId'] === 1
                    && $context['entityClass'] === stdClass::class,
            ));

        $this->createLoggingCrudController($loggerMock)->logActionErrorForTest(ActionType::DELETE, $exception, 1);
    }

    private function createLoggingCrudController(LoggerInterface $logger): UserFacingExceptionTestCrudController
    {
        $crudController = new UserFacingExceptionTestCrudController();
        $crudController->setDefinition(new Definition(
            UserFacingExceptionTestCrudController::class,
            'UserFacingExceptionTestCrudController',
            stdClass::class,
            'Store',
            (new CrudConfig('Store'))->getConfig(),
            [],
            [],
        ));
        $crudController->logger = $logger;

        return $crudController;
    }

    private function createFlashMessageServiceMock(bool $hasErrorMessages): FlashMessageService&MockObject
    {
        $flashMessageServiceMock = $this->createMock(FlashMessageService::class);
        $flashMessageServiceMock->method('hasErrorMessages')->willReturn($hasErrorMessages);

        return $flashMessageServiceMock;
    }

    private function createCrudController(
        FlashMessageService $flashMessageService,
    ): UserFacingExceptionTestCrudController {
        $crudController = new UserFacingExceptionTestCrudController();
        $crudController->flashMessageService = $flashMessageService;

        return $crudController;
    }
}

final class UserFacingExceptionTestCrudController extends AbstractCrudController
{
    /**
     * @param array<string, mixed> $genericErrorMessageParameters
     */
    public function addErrorFlashForFailedActionForTest(
        Throwable $exception,
        string $genericErrorMessageTemplate,
        array $genericErrorMessageParameters = [],
    ): void {
        $this->addErrorFlashForFailedAction($exception, $genericErrorMessageTemplate, $genericErrorMessageParameters);
    }

    public function logActionErrorForTest(ActionType $actionType, Throwable $exception, ?int $entityId = null): void
    {
        $this->logActionError($actionType, $exception, $entityId);
    }
}

final class TestUserFacingException extends Exception implements UserFacingExceptionInterface
{
    #[Override]
    public function getUserFacingMessage(): string
    {
        return $this->getMessage();
    }
}
