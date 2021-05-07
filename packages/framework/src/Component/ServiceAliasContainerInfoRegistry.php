<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use RuntimeException;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\SaveServiceAliasContainerInfoCompilerPass;

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
     * @return array<string,string>
     */
    public function getServiceIdsToClassNames(): array
    {
        return $this->checkDataWasSetByCompilerPass($this->serviceIdsToClassNames);
    }

    /**
     * @return array<string>
     */
    public function getPublicServiceIds(): array
    {
        return $this->checkDataWasSetByCompilerPass($this->publicServiceIds);
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
     * @param array|null $internalData
     * @return array
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
