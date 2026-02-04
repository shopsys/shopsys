<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Nette\Utils\Json;
use Redis;

class ProductRecalculationDeduplicationFacade
{
    protected const int TTL = 3600;

    public function __construct(
        protected readonly Redis $redisClient,
        protected readonly bool $isDeduplicationActive,
    ) {
    }

    /**
     * @param int[] $productIds
     */
    public function delete(
        array $productIds,
        string $priority,
    ): void {
        if (!$this->isDeduplicationActive) {
            return;
        }

        $this->redisClient->hDel($this->getCacheKey($priority), ...array_map('strval', $productIds));
    }

    /**
     * @param int[] $productIds
     * @return array<int,string[]>
     */
    public function getScopesIndexedByProductId(
        array $productIds,
        string $priority,
    ): array {
        if (!$this->isDeduplicationActive) {
            return array_fill_keys($productIds, []);
        }

        $exportScopesIndexedByProductIds = $this->doGetStoredExportScopes($productIds, $priority);

        foreach ($exportScopesIndexedByProductIds as $productId => $exportScopesJson) {
            if ($exportScopesJson === false) {
                $exportScopesJson = '[]';
            }

            $exportScopes = Json::decode($exportScopesJson, true);
            $exportScopesIndexedByProductIds[$productId] = $exportScopes;
        }

        return $exportScopesIndexedByProductIds;
    }

    /**
     * @param int[] $productIdsWithMainVariants
     * @param string[] $requestedExportScopes
     * @return int[]
     */
    public function updateScopesAndReturnProductIdsToDispatch(
        array $productIdsWithMainVariants,
        array $requestedExportScopes,
        string $priority,
    ): array {
        if (!$this->isDeduplicationActive) {
            return $productIdsWithMainVariants;
        }

        $exportScopesIndexedByProductIds = $this->doGetStoredExportScopes($productIdsWithMainVariants, $priority);
        $productIdsToDispatch = [];
        $exportScopesToWrite = [];

        foreach ($exportScopesIndexedByProductIds as $productId => $storedScopesJson) {
            if ($storedScopesJson === false) {
                $productIdsToDispatch[] = $productId;
            }

            $updatedScopesJson = $this->updateScopesByRequestedScopes($requestedExportScopes, $storedScopesJson);

            if ($storedScopesJson === false || $storedScopesJson !== $updatedScopesJson) {
                $exportScopesToWrite[$productId] = $updatedScopesJson;
            }
        }

        if ($exportScopesToWrite !== []) {
            $this->redisClient->hMset($this->getCacheKey($priority), $exportScopesToWrite);
            $this->redisClient->rawCommand(
                'HEXPIRE',
                $this->redisClient->getOption(Redis::OPT_PREFIX) . $this->getCacheKey($priority),
                self::TTL,
                'FIELDS',
                count($exportScopesToWrite),
                ...array_keys($exportScopesToWrite),
            );
        }

        return $productIdsToDispatch;
    }

    /**
     * @param string[] $requestedExportScopes
     */
    protected function updateScopesByRequestedScopes(
        array $requestedExportScopes,
        false|string $storedScopesJson,
    ): string {
        // no scopes stored; store requested scopes
        if ($storedScopesJson === false) {
            return Json::encode($requestedExportScopes);
        }

        $storedScopes = Json::decode($storedScopesJson, true);

        // all scopes are already stored; do not modify stored scopes
        if ($storedScopes === []) {
            return $storedScopesJson;
        }

        // requested all scopes; store all scopes
        if ($requestedExportScopes === []) {
            return '[]';
        }

        // all requested scopes already stored; do not modify stored scopes
        if (array_diff($requestedExportScopes, $storedScopes) === []) {
            return $storedScopesJson;
        }

        // update stored scopes with newly requested scopes
        $newScopes = array_unique(array_merge($storedScopes, $requestedExportScopes));

        return Json::encode($newScopes);
    }

    /**
     * @param int[] $productIds
     * @return array<int, string|false>
     */
    protected function doGetStoredExportScopes(array $productIds, string $priority): array
    {
        $exportScopesIndexedByProductIds = $this->redisClient->hMget(
            $this->getCacheKey($priority),
            array_map('strval', $productIds),
        );

        if ($exportScopesIndexedByProductIds === false) {
            $exportScopesIndexedByProductIds = array_fill_keys($productIds, false);
        }

        return $exportScopesIndexedByProductIds;
    }

    protected function getCacheKey(string $priority): string
    {
        return 'product_recalculation_ids_' . $priority;
    }
}
