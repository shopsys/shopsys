<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessage;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\LogoutException;

class LogoutExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface
     */
    protected $flashBag;

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
     * @param \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface $flashBag
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        FlashBagInterface $flashBag,
        CurrentCustomerUser $currentCustomerUser,
        RouterInterface $router,
        Domain $domain
    ) {
        $this->flashBag = $flashBag;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->router = $router;
        $this->domain = $domain;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
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

                $this->flashBag->add(
                    FlashMessage::KEY_ERROR,
                    t(
                        'There was an error during logout attempt. If you really want to sign out, please try it again.',
                        [],
                        Translator::DEFAULT_TRANSLATION_DOMAIN,
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
