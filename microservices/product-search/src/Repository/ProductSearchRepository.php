<?php

namespace Shopsys\MicroserviceProductSearch\Repository;

use Shopsys\MicroserviceProductSearch\Component\String\DatabaseSearching;
use Shopsys\MicroserviceProductSearch\Component\String\TsqueryFactory;

class ProductSearchRepository
{
    /**
     * @var \Shopsys\MicroserviceProductSearch\Component\String\TsqueryFactory
     */
    private $tsqueryFactory;

    public function __construct(TsqueryFactory $tsqueryFactory)
    {
        $this->tsqueryFactory = $tsqueryFactory;
    }

    /**
     * @param int $domainId
     * @param string $searchText
     * @return int[]
     */
    public function getFoundProductIds(int $domainId, string $searchText): array
    {
        if (!$this->tsqueryFactory->isValidSearchText($searchText)) {
            return [];
        }

        $connection = pg_connect("host=postgres dbname=shopsys user=root password=root");
        $sql = '
          SELECT p.id, CASE
                WHEN (
                    NORMALIZE(pt.name) LIKE NORMALIZE($1)
                    OR
                    NORMALIZE(pt.name) LIKE NORMALIZE($2)
                ) THEN 1
                WHEN pt.name_tsvector @@ to_tsquery($3) = TRUE THEN 2
                WHEN p.catnum_tsvector @@ to_tsquery($4) = TRUE THEN 3
                WHEN p.partno_tsvector @@ to_tsquery($4) = TRUE THEN 4
                WHEN NORMALIZE(pt.name) LIKE NORMALIZE($5) THEN 5
                WHEN pt.name_tsvector @@ to_tsquery($6) = TRUE THEN 6
                WHEN pd.description_tsvector @@ to_tsquery($3) = TRUE THEN 7
                WHEN pt.name_tsvector @@ to_tsquery($4) = TRUE THEN 8
                WHEN pt.name_tsvector @@ to_tsquery($7) = TRUE THEN 9
                ELSE 10
            END AS relevance 
          FROM products p
          JOIN product_translations pt ON pt.translatable_id = p.id AND pt.locale = get_domain_locale($8)
          JOIN product_domains pd ON pd.product_id = p.id AND pd.domain_id = $8
          WHERE pd.fulltext_tsvector @@ to_tsquery($6) = TRUE
          ORDER BY relevance';
        $parameters = [
            '%' . DatabaseSearching::getLikeSearchString($searchText) . ' %',
            '%' . DatabaseSearching::getLikeSearchString($searchText),
            $this->tsqueryFactory->getTsqueryWithAndConditions($searchText),
            $this->tsqueryFactory->getTsqueryWithOrConditions($searchText),
            DatabaseSearching::getFullTextLikeSearchString($searchText),
            $this->tsqueryFactory->getTsqueryWithAndConditionsAndPrefixMatchForLastWord($searchText),
            $this->tsqueryFactory->getTsqueryWithOrConditionsAndPrefixMatchForLastWord($searchText),
            $domainId,
        ];
        $result = pg_query_params($connection, $sql, $parameters);
        $productsIds = pg_fetch_all_columns($result, 0);

        return array_map('intval', $productsIds);
    }
}
