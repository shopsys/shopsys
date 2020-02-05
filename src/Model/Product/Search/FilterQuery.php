<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;

class FilterQuery extends BaseFilterQuery
{
    /**
     * @param string $text
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function search(string $text): BaseFilterQuery
    {
        $clonedQuery = parent::search($text);

        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.full_with_diacritic^60';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.full_without_diacritic^50';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix^45';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.edge_ngram_with_diacritic^40';
        $clonedQuery->match['multi_match']['fields'][] = 'name_prefix.edge_ngram_without_diacritic^35';

        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.full_with_diacritic^60';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.full_without_diacritic^50';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix^45';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.edge_ngram_with_diacritic^40';
        $clonedQuery->match['multi_match']['fields'][] = 'name_sufix.edge_ngram_without_diacritic^35';

        return $clonedQuery;
    }
}
