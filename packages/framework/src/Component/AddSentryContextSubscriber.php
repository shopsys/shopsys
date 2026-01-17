<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use App\Environment;
use Override;
use RedisException;
use Sentry\State\Scope;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Maintenance\MaintenanceModeFacade;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\ShopsysFrameworkBundle;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\KernelEvents;
use function Sentry\configureScope;

class AddSentryContextSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly MaintenanceModeFacade $maintenanceModeFacade,
        protected readonly Domain $domain,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ErrorIdProvider $errorIdProvider,
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly AdministratorFacade $administratorFacade,
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

        if ($this->contextResolver->isCurrentContext(AdminContext::class)) {
            $context['inAdmin'] = true;
            $context['displayTimeZone'] = $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin()->getName();

            try {
                $administrator = $this->administratorFacade->getCurrentlyLoggedAdministrator();
                $context['adminSelectedLocale'] = $administrator->getSelectedLocale();
                $context['adminId'] = $administrator->getId();
            } catch (AdministratorIsNotLoggedException) {
                $context['adminId'] = null;
            }
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
