<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SpamProtection;

use Psr\Log\LoggerInterface;
use Shopsys\FrontendApiBundle\Component\HttpFoundation\ClientIpProvider;
use Shopsys\FrontendApiBundle\Model\SpamProtection\Exception\TooManyFormSubmissionsUserError;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class FormSpamProtectionFacade
{
    public const string HONEY_POT_FIELD_NAME = 'subject';

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
     * @throws \Shopsys\FrontendApiBundle\Model\SpamProtection\Exception\TooManyFormSubmissionsUserError
     */
    public function shouldDiscardSubmission(array $input, string $formName): bool
    {
        $this->spamProtectedFormEnum->validateCase($formName);

        $this->checkRateLimit($formName);

        if (!$this->isHoneyPotFilled($input)) {
            return false;
        }

        $this->logger->info(
            'Form submission was discarded because the honey pot field was filled in.',
            [
                'formName' => $formName,
            ],
        );

        return true;
    }

    /**
     * @throws \Shopsys\FrontendApiBundle\Model\SpamProtection\Exception\TooManyFormSubmissionsUserError
     */
    protected function checkRateLimit(string $formName): void
    {
        $rateLimit = $this->getRateLimiterFactory($formName)
            ->create($formName . ':' . $this->clientIpProvider->getClientIp())
            ->consume();

        if (!$rateLimit->isAccepted()) {
            throw new TooManyFormSubmissionsUserError('Too many submissions of this form. Try again later.');
        }
    }

    /**
     * $formName is unused here, because all forms share one limiter — a project can override this method and branch on it.
     */
    protected function getRateLimiterFactory(string $formName): RateLimiterFactoryInterface
    {
        return $this->formSpamProtectionRateLimiter;
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function isHoneyPotFilled(array $input): bool
    {
        $honeyPotValue = $input[static::HONEY_POT_FIELD_NAME] ?? null;

        return is_string($honeyPotValue) && trim($honeyPotValue) !== '';
    }
}
