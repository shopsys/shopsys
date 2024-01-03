import { ProductsSearch } from './ProductsSearch';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SkeletonPageProductsList } from 'components/Blocks/Skeleton/SkeletonPageProductsList';
import { SearchQueryApi, SimpleCategoryFragmentApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type SearchContentProps = {
    searchResults: SearchQueryApi | undefined;
    fetching: boolean;
};

const TEST_IDENTIFIER = 'search-results';

export const SearchContent: FC<SearchContentProps> = ({ searchResults, fetching }) => {
    const router = useRouter();
    const { t } = useTranslation();

    const title = useSeoTitleWithPagination(searchResults?.productsSearch.totalCount, t('Found products'));

    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragmentApi>(searchResults?.categoriesSearch.edges),
        [searchResults?.categoriesSearch.edges],
    );

    const isFetchingInitialData = !searchResults && fetching;

    return (
        <>
            <h1 className="mb-3" data-testid={`${TEST_IDENTIFIER}-heading`}>{`${t(
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
                                <SimpleNavigation linkType="blogArticle" listedItems={searchResults.articlesSearch} />
                            </div>
                        )}

                        {!!searchResults.brandSearch.length && (
                            <div className="mt-6">
                                <div className="h3 mb-3">{t('Found brands')}</div>
                                <SimpleNavigation linkType="brand" listedItems={searchResults.brandSearch} />
                            </div>
                        )}

                        {!!mappedCategoriesSearchResults?.length && (
                            <div className="mt-6">
                                <div className="h3 mb-3">{t('Found categories')}</div>
                                <SimpleNavigation linkType="category" listedItems={mappedCategoriesSearchResults} />
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
