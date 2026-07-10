<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrontendApiBundle\Model\Mutation\Cart\Exception\TooManyCodeApplicationAttemptsUserError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class ApplyCodeToCartRateLimiter
{
    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly ?RateLimiterFactoryInterface $applyCodeToCartLimiter = null,
    ) {
    }

    public function consume(): void
    {
        if ($this->applyCodeToCartLimiter === null) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return;
        }

        $limiter = $this->applyCodeToCartLimiter->create($request->getClientIp());

        if (!$limiter->consume()->isAccepted()) {
            throw new TooManyCodeApplicationAttemptsUserError('Too many attempts to apply a code. Try again later.');
        }
    }
}
