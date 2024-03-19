import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { SearchProductsQueryApi } from 'graphql/generated';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { createEmptyArray } from 'helpers/arrayUtils';
import { getMappedProducts } from 'helpers/mappers/products';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { RefObject } from 'react';

type SearchProductsContentProps = {
    isFetching: boolean;
    isLoadMoreFetching: boolean;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
    searchProductsData: SearchProductsQueryApi['productsSearch'];
};

export const SearchProductsContent: FC<SearchProductsContentProps> = ({
    isFetching,
    isLoadMoreFetching,
    paginationScrollTargetRef,
    searchProductsData,
}) => {
    const { t } = useTranslation();
    const searchResultProducts = getMappedProducts(searchProductsData.edges);

    useGtmPaginatedProductListViewEvent(searchResultProducts, GtmProductListNameType.search_results);

    const isWithProductsShown = !!searchProductsData.totalCount;
    const noProductsFound = parseInt(searchProductsData.productFilterOptions.maximalPrice) === 0;

    if (isFetching) {
        return (
            <div className="relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6">
                {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                    <SkeletonModuleProductListItem key={index} />
                ))}
            </div>
        );
    }

    return (
        <>
            {searchResultProducts && (
                <>
                    {isWithProductsShown && (
                        <ProductsList
                            gtmMessageOrigin={GtmMessageOriginType.other}
                            gtmProductListName={GtmProductListNameType.search_results}
                            isFetching={isFetching}
                            isLoadMoreFetching={isLoadMoreFetching}
                            products={searchResultProducts}
                        />
                    )}

                    {!isWithProductsShown && !noProductsFound && (
                        <div className="p-12 text-center">
                            <div className="mb-5">
                                <strong>{t('No results match the filter')}</strong>
                            </div>
                            <div>
                                <Trans components={{ 0: <br /> }} i18nKey="ProductsNoResults" />
                            </div>
                        </div>
                    )}

                    {noProductsFound && (
                        <div className="p-12 text-center">
                            <div className="mb-5">
                                <strong>{t('No products matched your search')}</strong>
                            </div>
                        </div>
                    )}
                </>
            )}

            <Pagination
                isWithLoadMore
                hasNextPage={searchProductsData.pageInfo.hasNextPage}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={searchProductsData.totalCount}
            />
        </>
    );
};
