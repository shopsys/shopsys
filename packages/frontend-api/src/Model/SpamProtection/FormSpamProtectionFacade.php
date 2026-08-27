<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SpamProtection;

use Psr\Log\LoggerInterface;
use Shopsys\FrontendApiBundle\Component\HttpFoundation\ClientIpProvider;
use Shopsys\FrontendApiBundle\Model\SpamProtection\Exception\HoneyPotFieldNameNotConfiguredException;
use Shopsys\FrontendApiBundle\Model\SpamProtection\Exception\TooManyFormSubmissionsUserError;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class FormSpamProtectionFacade
{
    public function __construct(
        protected readonly ClientIpProvider $clientIpProvider,
        protected readonly LoggerInterface $logger,
        protected readonly RateLimiterFactoryInterface $formSpamProtectionRateLimiter,
        protected readonly SpamProtectedFormEnum $spamProtectedFormEnum,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     * @param string $formName one of \Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum cases
     */
    public function shouldDiscardSubmission(array $input, string $formName): bool
    {
        $honeyPotFieldName = $this->getHoneyPotFieldName($formName);

        $this->checkRateLimit($formName);

        if (!$this->isHoneyPotFilled($input, $honeyPotFieldName)) {
            return false;
        }

        $this->logger->info(
            'Form submission was discarded because the honey pot field was filled in.',
            [
                'formName' => $formName,
                'clientIp' => $this->clientIpProvider->getClientIp(),
            ],
        );

        return true;
    }

    protected function getHoneyPotFieldName(string $formName): string
    {
        $honeyPotFieldNameIndexedByFormName = $this->spamProtectedFormEnum->getHoneyPotFieldNameIndexedByFormName();

        if (!array_key_exists($formName, $honeyPotFieldNameIndexedByFormName)) {
            throw new HoneyPotFieldNameNotConfiguredException($formName);
        }

        return $honeyPotFieldNameIndexedByFormName[$formName];
    }

    protected function checkRateLimit(string $formName): void
    {
        $rateLimit = $this->getRateLimiterFactory($formName)
            ->create($formName . ':' . $this->clientIpProvider->getClientIp())
            ->consume();

        if (!$rateLimit->isAccepted()) {
            throw new TooManyFormSubmissionsUserError();
        }
    }

    /**
     * $formName is unused here, because all forms share one configuration —
     * a project can override this method and branch on it.
     */
    protected function getRateLimiterFactory(string $formName): RateLimiterFactoryInterface
    {
        return $this->formSpamProtectionRateLimiter;
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function isHoneyPotFilled(array $input, string $honeyPotFieldName): bool
    {
        $honeyPotValue = $input[$honeyPotFieldName] ?? null;

        return is_string($honeyPotValue) && trim($honeyPotValue) !== '';
    }
}
