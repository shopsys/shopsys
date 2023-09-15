import { ProductsSearch } from './ProductsSearch';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Webline } from 'components/Layout/Webline/Webline';
import { SearchQueryApi, SimpleCategoryFragmentApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useMemo } from 'react';
import { CategoryDetailPageSkeleton } from '../CategoryDetail/CategoryDetailPageSkeleton';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';

type SearchContentProps = {
    searchResults: SearchQueryApi | undefined;
    fetching: boolean;
};

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
        <Webline>
            <Heading type="h1">{`${t('Search results for')} "${getStringFromUrlQuery(router.query.q)}"`}</Heading>
            {isFetchingInitialData ? (
                <CategoryDetailPageSkeleton />
            ) : (
                !!searchResults && (
                    <>
                        {!!searchResults.articlesSearch.length && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found articles')}</Heading>
                                <SimpleNavigation
                                    listedItems={searchResults.articlesSearch}
                                    imageType="searchThumbnail"
                                />
                            </div>
                        )}

                        {!!searchResults.brandSearch.length && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found brands')}</Heading>
                                <SimpleNavigation listedItems={searchResults.brandSearch} />
                            </div>
                        )}

                        {!!searchResults.categoriesSearch.totalCount && !!mappedCategoriesSearchResults && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found categories')}</Heading>
                                <SimpleNavigation listedItems={mappedCategoriesSearchResults} />
                            </div>
                        )}

                        <div className="mt-6">
                            <Heading type="h3">{title}</Heading>
                            <ProductsSearch productsSearch={searchResults.productsSearch} />
                        </div>
                    </>
                )
            )}
        </Webline>
    );
};
