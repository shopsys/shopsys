import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { productListTwClass, ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeSearchProductsQuery } from 'graphql/requests/search/queries/SearchProductsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { getMappedProducts } from 'utils/mappers/products';

type SearchProductsContentProps = {
    areSearchProductsFetching: boolean;
    isLoadingMoreSearchProducts: boolean;
    searchProductsData: TypeSearchProductsQuery['productsSearch'];
};

export const SearchProductsContent: FC<SearchProductsContentProps> = ({
    areSearchProductsFetching,
    isLoadingMoreSearchProducts,
    searchProductsData,
}) => {
    const { t } = useTranslation();
    const searchResultProducts = getMappedProducts(searchProductsData.edges);

    useGtmPaginatedProductListViewEvent(searchResultProducts, GtmProductListNameType.search_results);

    const isWithProductsShown = !!searchProductsData.totalCount;
    const noProductsFound = parseFloat(searchProductsData.productFilterOptions.maximalPrice) === 0;

    if (areSearchProductsFetching) {
        return (
            <div className={productListTwClass}>
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
                            areProductsFetching={areSearchProductsFetching}
                            gtmMessageOrigin={GtmMessageOriginType.other}
                            gtmProductListName={GtmProductListNameType.search_results}
                            isLoadingMoreProducts={isLoadingMoreSearchProducts}
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
                totalCount={searchProductsData.totalCount}
            />
        </>
    );
};
