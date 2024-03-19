import { ProductsListContent } from './ProductsListContent';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { CategoryDetailFragmentApi, ListedProductFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { createEmptyArray } from 'helpers/arrayUtils';
import { calculatePageSize } from 'helpers/loadMore';
import { useQueryParams } from 'hooks/useQueryParams';

type ProductsListProps = {
    products: ListedProductFragmentApi[] | undefined;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    isFetching?: boolean;
    isLoadMoreFetching?: boolean;
    category?: CategoryDetailFragmentApi;
};

const productListTwClass = 'relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6';

export const ProductsList: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    isFetching,
    isLoadMoreFetching,
    category,
    gtmMessageOrigin = GtmMessageOriginType.other,
}) => {
    const { currentLoadMore } = useQueryParams();

    if (!products?.length && !isFetching) {
        return <CategoryDetailContentMessage />;
    }

    if (!!products?.length && !isFetching) {
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

                {isLoadMoreFetching &&
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
