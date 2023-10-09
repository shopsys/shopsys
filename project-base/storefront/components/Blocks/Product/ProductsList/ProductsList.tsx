import { ProductsListContent } from './ProductsListContent';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { SkeletonProductListItem } from 'components/Blocks/Skeleton/SkeletonProductListItem';
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
    fetching?: boolean;
    loadMoreFetching?: boolean;
    category?: CategoryDetailFragmentApi;
};

const TEST_IDENTIFIER = 'blocks-product-list';

const productListTwClass = 'relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6';

export const ProductsList: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    fetching,
    loadMoreFetching,
    category,
    gtmMessageOrigin = GtmMessageOriginType.other,
}) => {
    const { currentLoadMore } = useQueryParams();

    if (!products?.length && !fetching) {
        return <CategoryDetailContentMessage />;
    }

    if (!!products?.length && !fetching) {
        return (
            <>
                <ProductsListContent
                    className={productListTwClass}
                    dataTestId={TEST_IDENTIFIER}
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
                    createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => <SkeletonProductListItem key={index} />)}
            </>
        );
    }

    return (
        <div className={productListTwClass} data-testid={TEST_IDENTIFIER}>
            {createEmptyArray(calculatePageSize(currentLoadMore)).map((_, index) => (
                <SkeletonProductListItem key={index} />
            ))}
        </div>
    );
};
