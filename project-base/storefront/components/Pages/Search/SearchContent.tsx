import { ProductsSearch } from './ProductsSearch';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SkeletonPageProductsList } from 'components/Blocks/Skeleton/SkeletonPageProductsList';
import { TIDs } from 'cypress/tids';
import { TypeSimpleCategoryFragment } from 'graphql/requests/categories/fragments/SimpleCategoryFragment.generated';
import { TypeSearchQuery } from 'graphql/requests/search/queries/SearchQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useMemo } from 'react';
import { mapConnectionEdges } from 'utils/mappers/connection';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

type SearchContentProps = {
    searchResults: TypeSearchQuery | undefined;
    fetching: boolean;
};

export const SearchContent: FC<SearchContentProps> = ({ searchResults, fetching }) => {
    const router = useRouter();
    const { t } = useTranslation();

    const title = useSeoTitleWithPagination(searchResults?.productsSearch.totalCount, t('Found products'));

    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<TypeSimpleCategoryFragment>(searchResults?.categoriesSearch.edges),
        [searchResults?.categoriesSearch.edges],
    );

    const isFetchingInitialData = !searchResults && fetching;

    return (
        <>
            <h1 className="mb-3" tid={TIDs.search_results_heading}>{`${t(
                'Search results for',
            )} "${getStringFromUrlQuery(router.query.q)}"`}</h1>
            {isFetchingInitialData ? (
                <SkeletonPageProductsList />
            ) : (
                !!searchResults && (
                    <>
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

                        <div className="mt-6">
                            <div className="h3 mb-3">{title}</div>
                            <ProductsSearch productsSearch={searchResults.productsSearch} />
                        </div>
                    </>
                )
            )}
        </>
    );
};
