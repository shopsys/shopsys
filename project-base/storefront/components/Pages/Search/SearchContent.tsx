import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { TIDs } from 'cypress/tids';
import { SearchQueryApi, SimpleCategoryFragmentApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type SearchContentProps = {
    searchResults: SearchQueryApi;
};

export const SearchContent: FC<SearchContentProps> = ({ searchResults }) => {
    const router = useRouter();
    const { t } = useTranslation();

    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragmentApi>(searchResults.categoriesSearch.edges),
        [searchResults.categoriesSearch.edges],
    );

    return (
        <>
            <h1 className="mb-3" tid={TIDs.search_results_heading}>{`${t(
                'Search results for',
            )} "${getStringFromUrlQuery(router.query.q)}"`}</h1>
            {!!searchResults.articlesSearch.length && (
                <div className="mt-6">
                    <div className="h3 mb-3">{t('Found articles')}</div>
                    <SimpleNavigation listedItems={searchResults.articlesSearch} />
                </div>
            )}

            {!!searchResults.brandSearch.length && (
                <div className="mt-6">
                    <div className="h3 mb-3">{t('Found brands')}</div>
                    <SimpleNavigation listedItems={searchResults.brandSearch} />
                </div>
            )}

            {!!mappedCategoriesSearchResults?.length && (
                <div className="mt-6">
                    <div className="h3 mb-3">{t('Found categories')}</div>
                    <SimpleNavigation listedItems={mappedCategoriesSearchResults} />
                </div>
            )}
        </>
    );
};
