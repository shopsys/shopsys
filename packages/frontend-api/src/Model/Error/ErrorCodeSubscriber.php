<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

use Exception;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorCodeSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Overblog\GraphQLBundle\Event\ErrorFormattingEvent $event
     */
    public function onErrorFormatting(ErrorFormattingEvent $event): void
    {
        $error = $event->getError();
        $code = null;
        $userCode = null;

        $previousError = $error->getPrevious();

        if ($previousError instanceof Exception && $previousError instanceof UserErrorWithCodeInterface) {
            $userCode = $previousError->getUserErrorCode();
            $code = $previousError->getCode();
        }

        if ($error instanceof Exception && $error instanceof UserErrorWithCodeInterface) {
            $userCode = $error->getUserErrorCode();
            $code = $error->getCode();
        }

        if ($userCode === null && $code === null) {
            $code = 500;
        }

        $formattedError = $event->getFormattedError();

        $extensions = [];

        if ($formattedError->offsetExists('extensions')) {
            $extensions = $formattedError->offsetGet('extensions');
        }

        $extensions['userCode'] = $userCode;
        $extensions['code'] = $code;
        $formattedError->offsetSet('extensions', $extensions);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::ERROR_FORMATTING => ['onErrorFormatting'],
        ];
    }
}
