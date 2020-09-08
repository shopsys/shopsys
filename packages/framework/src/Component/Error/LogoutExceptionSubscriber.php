<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\LogoutException;

class LogoutExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     */
    protected $currentUser;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender
     */
    protected $flashMessageSender;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender $flashMessageSender
     * @param \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer $currentUser
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        FlashMessageSender $flashMessageSender,
        CurrentCustomer $currentUser,
        RouterInterface $router,
        Domain $domain
    ) {
        $this->flashMessageSender = $flashMessageSender;
        $this->currentUser = $currentUser;
        $this->router = $router;
        $this->domain = $domain;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if ($event->getException() instanceof LogoutException || $event->getException()->getPrevious() instanceof LogoutException) {
            if ($this->currentUser->findCurrentUser() !== null) {
                $domainId = $this->currentUser->findCurrentUser()->getDomainId();
                $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

                $this->flashMessageSender->addErrorFlash(t('There was an error during logout attempt. If you really want to sign out, please try it again.', [], 'messages', $locale));
            }

            $redirectUrl = $this->getSafeUrlToRedirect($event->getRequest()->headers->get('referer'));

            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

    /**
     * @param string|null $url
     * @return string
     */
    protected function getSafeUrlToRedirect(?string $url): string
    {
        if ($url !== null) {
            $urlParse = parse_url($url);
            $domainUrl = $this->domain->getUrl();
            $domainUrlParse = parse_url($domainUrl);
            $parsedUrl = $urlParse['scheme'] . $urlParse['host'];
            $parsedDomainUrl = $domainUrlParse['scheme'] . $domainUrlParse['host'];

            if ($parsedUrl === $parsedDomainUrl) {
                return $url;
            }
        }

        return $this->router->generate('front_homepage');
    }
}
