<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use App\Environment;
use Override;
use RedisException;
use Sentry\State\Scope;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\ShopsysFrameworkBundle;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\KernelEvents;
use function Sentry\configureScope;

class AddSentryContextSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeFacade $maintenanceModeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     */
    public function __construct(
        protected readonly MaintenanceModeFacade $maintenanceModeFacade,
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ErrorIdProvider $errorIdProvider,
    ) {
    }

    public function setSentryContext(): void
    {
        $context = [
            'environment' => class_exists('App\Environment') ? Environment::getEnvironment() : '',
            'version' => ShopsysFrameworkBundle::VERSION,
            'systemTimeZone' => date_default_timezone_get(),
            'userId' => $this->currentCustomerUser->findCurrentCustomerUser()?->getId(),
        ];

        try {
            $context['maintenance-page'] = $this->maintenanceModeFacade->isEnabled();
        } catch (RedisException) {
        }

        try {
            $context['currentDomainId'] = $this->domain->getId();
            $context['currentDomainName'] = $this->domain->getName();
            $context['currentDomainLocale'] = $this->domain->getLocale();
            $context['displayTimeZone'] = $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($this->domain->getId())->getName();
        } catch (NoDomainSelectedException | FileNotFoundException) {
        }

        configureScope(function (Scope $scope) use ($context): void {
            $scope->setContext('Shopsys', $context);
            $scope->setTag('errorId', $this->errorIdProvider->getErrorId());
        });
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'setSentryContext',
            ConsoleEvents::COMMAND => 'setSentryContext',
        ];
    }
}
