import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useCookiesStore } from 'store/useCookiesStore';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { ProductItemProps, ProductListViewModeType } from './ProductListItem';
import { ProductsListContent } from './ProductsListContent';
import { productListTwClass, productListViewModeListTwClass } from './productsListConstants';

type ProductsListProps = {
    products: TypeListedProductFragment[] | undefined;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    areProductsFetching?: boolean;
    isLoadingMoreProducts?: boolean;
    category?: TypeCategoryDetailFragment;
    productItemProps?: Partial<ProductItemProps>;
};

const getProductListTwClass = (productListViewMode: ProductListViewModeType) =>
    productListViewMode === 'list' ? productListViewModeListTwClass : productListTwClass;

export const ProductsList: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    areProductsFetching,
    isLoadingMoreProducts,
    category,
    gtmMessageOrigin = GtmMessageOriginType.other,
    productItemProps,
}) => {
    const { t } = useTranslation();
    const currentLoadMore = useCurrentLoadMoreQuery();
    const productListViewMode = useCookiesStore((store) => store.productListViewMode);
    const currentProductListTwClass = getProductListTwClass(productListViewMode);

    if (!products?.length && !areProductsFetching) {
        return <CategoryDetailContentMessage />;
    }

    if (products?.length && !areProductsFetching) {
        return (
            <>
                <h2 className="sr-only">{t('Product list')}</h2>

                <ProductsListContent
                    className={currentProductListTwClass}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    isWithImageGallery
                    productListViewMode={productListViewMode}
                    productItemProps={productItemProps}
                    products={products}
                >
                    {category && (
                        <li className="col-span-full row-start-2 mx-auto w-full min-w-0 justify-center">
                            <Adverts isSingle currentCategory={category} positionName="productListSecondRow" />
                        </li>
                    )}
                </ProductsListContent>

                {isLoadingMoreProducts && (
                    <div className={currentProductListTwClass}>
                        {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                            <SkeletonModuleProductListItem key={index} productListViewMode={productListViewMode} />
                        ))}
                    </div>
                )}
            </>
        );
    }

    return (
        <div className={currentProductListTwClass}>
            {createEmptyArray(calculatePageSize(currentLoadMore)).map((_, index) => (
                <SkeletonModuleProductListItem key={index} productListViewMode={productListViewMode} />
            ))}
        </div>
    );
};
