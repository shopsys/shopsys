import { ProductItemProps } from './ProductListItem';
import { ProductsListContent } from './ProductsListContent';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';

type ProductsListProps = {
    products: TypeListedProductFragment[] | undefined;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    areProductsFetching?: boolean;
    isLoadingMoreProducts?: boolean;
    category?: TypeCategoryDetailFragment;
    productItemProps?: Partial<ProductItemProps>;
};

export const productListTwClass = twJoin(
    'relative grid gap-2.5 sm:gap-x-5 sm:gap-y-6',
    'grid-cols-1',
    'xs:grid-cols-2',
    'lg:grid-cols-3',
    'xl:grid-cols-4',
);

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

    if (!products?.length && !areProductsFetching) {
        return <CategoryDetailContentMessage />;
    }

    if (!!products?.length && !areProductsFetching) {
        return (
            <>
                <h2 className="sr-only">{t('Product list')}</h2>
                <ProductsListContent
                    className={productListTwClass}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    productItemProps={productItemProps}
                    products={products}
                >
                    {category && (
                        <li className="col-span-full row-start-2 mx-auto justify-center">
                            <Adverts isSingle currentCategory={category} positionName="productListSecondRow" />
                        </li>
                    )}
                </ProductsListContent>

                {isLoadingMoreProducts && (
                    <div className={productListTwClass}>
                        {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                            <SkeletonModuleProductListItem key={index} />
                        ))}
                    </div>
                )}
            </>
        );
    }

    return (
        <div className={productListTwClass}>
            {createEmptyArray(calculatePageSize(currentLoadMore)).map((_, index) => (
                <SkeletonModuleProductListItem key={index} />
            ))}
        </div>
    );
};
