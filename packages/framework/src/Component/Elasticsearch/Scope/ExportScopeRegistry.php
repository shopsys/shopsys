<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Scope;

use Webmozart\Assert\Assert;

class ExportScopeRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[]
     */
    protected array $allScopes;

    /**
     * @param iterable $allScopes
     */
    public function __construct(
        iterable $allScopes,
    ) {
        $this->registerScopes($allScopes);
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface>
     */
    public function getAllScopes(): array
    {
        $scopes = [];
        foreach ($this->allScopes as $scope) {
            $scopes[get_class($scope)] = $scope;
        }

        return $scopes;
    }

    /**
     * @param string[] $propertyNames
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[]
     */
    public function getScopesByFcqn(array $scopesFcqn): array
    {
        if ($scopesFcqn === []) {
            return $this->getAllScopes();
        }

        $scopes = [];
        foreach ($this->allScopes as $scope) {
            $scopeClass = get_class($scope);
            if (in_array($scopeClass, $scopesFcqn, true)) {
                $scopes[$scopeClass] = $scope;

                if ($scope->getDependencies() !== []) {
                    $scopes = array_merge($scopes, $this->getScopesByFcqn($scope->getDependencies()));
                }
            }
        }

        return $scopes;
    }

    /**
     * @param iterable $allScopes
     */
    protected function registerScopes(iterable $allScopes): void
    {
        Assert::allIsInstanceOf($allScopes, ExportScopeInterface::class);

        foreach ($allScopes as $scope) {
            $this->allScopes[] = $scope;
        }
    }
}
