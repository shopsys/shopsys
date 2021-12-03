<?php

declare(strict_types=1);

namespace App\FrontendApi\Error;

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

        $previousError = $error->getPrevious();

        if ($previousError instanceof UserErrorWithCodeInterface) {
            $code = $previousError->getUserErrorCode();
        }

        if ($error instanceof UserErrorWithCodeInterface) {
            $code = $error->getUserErrorCode();
        }

        if ($code === null) {
            return;
        }

        $formattedError = $event->getFormattedError();
        $extensions = $formattedError->offsetGet('extensions');
        $extensions['code'] = $code;
        $formattedError->offsetSet('extensions', $extensions);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            Events::ERROR_FORMATTING => ['onErrorFormatting'],
        ];
    }
}
