<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashBagProvider;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\LogoutException;

class LogoutExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FlashMessage\FlashBagProvider
     */
    protected FlashBagProvider $flashBagProvider;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\FlashBagProvider $flashBagProvider
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        FlashBagProvider $flashBagProvider,
        CurrentCustomerUser $currentCustomerUser,
        RouterInterface $router,
        Domain $domain
    ) {
        $this->flashBagProvider = $flashBagProvider;
        $this->currentCustomerUser = $currentCustomerUser;
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
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if (
            $event->getThrowable() instanceof LogoutException
            || $event->getThrowable()->getPrevious() instanceof LogoutException
        ) {
            if ($this->currentCustomerUser->findCurrentCustomerUser() !== null) {
                $domainId = $this->currentCustomerUser->findCurrentCustomerUser()->getDomainId();
                $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

                $this->flashBagProvider->getFlashBag()?->add(
                    FlashMessage::KEY_ERROR,
                    t(
                        'There was an error during logout attempt. If you really want to sign out, please try it again.',
                        [],
                        'messages',
                        $locale
                    )
                );
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
