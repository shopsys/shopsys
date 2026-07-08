<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\FlashMessage;

use Override;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class LiveComponentFlashMessageSubscriber implements EventSubscriberInterface
{
    public const string FLASH_MESSAGES_HEADER = 'X-Admin-Flash-Messages';

    protected const string LIVE_COMPONENT_CONTENT_TYPE = 'application/vnd.live-component+html';

    public function __construct(
        protected readonly FlashMessageService $flashMessageService,
        protected readonly Environment $twigEnvironment,
    ) {
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->shouldHandleResponse($event->getResponse())) {
            return;
        }

        $toastHtml = trim($this->twigEnvironment->render(
            '@ShopsysAdministration/partial/flash_message/_toasts.html.twig',
            $this->getMessages(),
        ));

        if ($toastHtml === '') {
            return;
        }

        $event->getResponse()->headers->set(self::FLASH_MESSAGES_HEADER, base64_encode($toastHtml));
    }

    protected function shouldHandleResponse(Response $response): bool
    {
        if ($response->headers->has('Location') || $response->headers->has('X-Live-Redirect')) {
            return false;
        }

        if (!str_contains((string)$response->headers->get('Content-Type'), self::LIVE_COMPONENT_CONTENT_TYPE)) {
            return false;
        }

        return $response->isSuccessful()
            || $response->getStatusCode() === Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    /**
     * @return array{
     *     errorMessages: string[],
     *     infoMessages: string[],
     *     successMessages: string[],
     *     warningMessages: string[]
     * }
     */
    protected function getMessages(): array
    {
        return [
            'errorMessages' => $this->flashMessageService->getErrorMessages(),
            'infoMessages' => $this->flashMessageService->getInfoMessages(),
            'successMessages' => $this->flashMessageService->getSuccessMessages(),
            'warningMessages' => $this->flashMessageService->getWarningMessages(),
        ];
    }
}
