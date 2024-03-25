import { ProductsListContent } from './ProductsListContent';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { CategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { createEmptyArray } from 'helpers/arrays/createEmptyArray';
import { calculatePageSize } from 'helpers/loadMore/calculatePageSize';
import { useCurrentLoadMoreQuery } from 'hooks/queryParams/useCurrentLoadMoreQuery';

type ProductsListProps = {
    products: ListedProductFragment[] | undefined;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    fetching?: boolean;
    loadMoreFetching?: boolean;
    category?: CategoryDetailFragment;
};

const productListTwClass = 'relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6';

export const ProductsList: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    fetching,
    loadMoreFetching,
    category,
    gtmMessageOrigin = GtmMessageOriginType.other,
}) => {
    const currentLoadMore = useCurrentLoadMoreQuery();

    if (!products?.length && !fetching) {
        return <CategoryDetailContentMessage />;
    }

    if (!!products?.length && !fetching) {
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

                {loadMoreFetching &&
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
