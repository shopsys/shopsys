<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection;

use Symfony\Contracts\Service\ResetInterface;
use Traversable;

class ServicesResetter
{
    /**
     * @param \Traversable<string, \Symfony\Contracts\Service\ResetInterface> $resettableServices
     */
    public function __construct(
        protected readonly Traversable $resettableServices,
    ) {
    }

    public function reset(): void
    {
        foreach ($this->resettableServices as $service) {
            $className = $service::class;

            if (!($service instanceof ResetInterface) || !$this->isShopsysService($className)) {
                continue;
            }

            $service->reset();
        }
    }

    /**
     * @param class-string $className
     * @return bool
     */
    protected function isShopsysService(string $className): bool
    {
        return str_starts_with($className, 'Shopsys\\') || str_starts_with($className, 'App\\');
    }
}
