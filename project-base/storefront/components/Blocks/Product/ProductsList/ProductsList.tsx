import { ProductsListContent } from './ProductsListContent';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
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
};

const productListTwClass = 'relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6';

export const ProductsList: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    areProductsFetching,
    isLoadingMoreProducts,
    category,
    gtmMessageOrigin = GtmMessageOriginType.other,
}) => {
    const currentLoadMore = useCurrentLoadMoreQuery();

    if (!products?.length && !areProductsFetching) {
        return <CategoryDetailContentMessage />;
    }

    if (!!products?.length && !areProductsFetching) {
        return (
            <>
                <ProductsListContent
                    className={productListTwClass}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    products={products}
                >
                    {category && (
                        <Adverts
                            isSingle
                            className="col-span-full row-start-2 mx-auto justify-center pl-2"
                            currentCategory={category}
                            positionName="productListSecondRow"
                        />
                    )}
                </ProductsListContent>

                {isLoadingMoreProducts &&
                    createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                        <SkeletonModuleProductListItem key={index} />
                    ))}
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
