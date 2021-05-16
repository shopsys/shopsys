<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use RuntimeException;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\SaveServiceAliasContainerInfoCompilerPass;
use Symfony\Contracts\Service\ServiceProviderInterface;

class ServiceAliasContainerInfoRegistry
{
    /**
     * @var array<string,string>|null
     */
    protected ?array $serviceIdsToClassNames = null;

    /**
     * @var array<string>|null
     */
    protected ?array $publicServiceIds = null;

    /**
     * @var array<string,string>|null
     */
    protected ?array $aliasIdsToAliases = null;

    /**
     * @var \Symfony\Contracts\Service\ServiceProviderInterface[]|null
     */
    protected ?array $serviceLocators = null;

    /**
     * @param array<string,string> $serviceIdToClassNames
     */
    public function setServiceIdsToClassNames(array $serviceIdToClassNames): void
    {
        $this->checkDataWasNotSetYet($this->serviceIdsToClassNames);

        $this->serviceIdsToClassNames = $serviceIdToClassNames;
    }

    /**
     * @param array<string> $publicServiceIds
     */
    public function setPublicServiceIds(array $publicServiceIds): void
    {
        $this->checkDataWasNotSetYet($this->publicServiceIds);

        $this->publicServiceIds = $publicServiceIds;
    }

    /**
     * @param array<string,string> $aliasIdsToAliases
     */
    public function setAliasIdsToAliases(array $aliasIdsToAliases): void
    {
        $this->checkDataWasNotSetYet($this->aliasIdsToAliases);

        $this->aliasIdsToAliases = $aliasIdsToAliases;
    }

    /**
     * @param \Symfony\Contracts\Service\ServiceProviderInterface ...$serviceLocators
     */
    public function setServiceLocators(ServiceProviderInterface ...$serviceLocators): void
    {
        $this->serviceLocators = $serviceLocators;
    }

    /**
     * @return array<string,string>
     */
    public function getServiceIdsToClassNames(): array
    {
        return $this->checkDataWasSetByCompilerPass($this->serviceIdsToClassNames);
    }

    /**
     * Returns true if service is accessible from Container or ServiceLocator
     * (eg. ValidatorFactory uses service locator for accessing Validator services)
     *
     * @see \Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass
     * @see \Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass
     * @param string $serviceId
     * @return bool
     */
    public function isServiceAccessible(string $serviceId): bool
    {
        if (in_array($serviceId, $this->checkDataWasSetByCompilerPass($this->publicServiceIds), true)) {
            return true;
        }

        foreach ($this->checkDataWasSetByCompilerPass($this->serviceLocators) as $serviceLocator) {
            if ($serviceLocator->has($serviceId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string,string>
     */
    public function getAliasIdsToAliases(): array
    {
        return $this->checkDataWasSetByCompilerPass($this->aliasIdsToAliases);
    }

    /**
     * @param array|null $internalData
     */
    protected function checkDataWasNotSetYet(?array $internalData): void
    {
        if ($internalData !== null) {
            $message = 'Data was already set during kernel compilation. You cannot overwrite previously set values.';

            throw new RuntimeException($message);
        }
    }

    /**
     * @template T
     * @param array<T>|null $internalData
     * @return array<T>
     */
    protected function checkDataWasSetByCompilerPass(?array $internalData): array
    {
        if ($internalData === null) {
            $message = sprintf(
                'Data was not set during kernel compilation. Is %s added as a compiler pass in your Kernel?',
                SaveServiceAliasContainerInfoCompilerPass::class
            );

            throw new RuntimeException($message);
        }

        return $internalData;
    }
}
