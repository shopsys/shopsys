import { ProductsSlider, VISIBLE_SLIDER_ITEMS_LAST_VISITED } from 'components/Blocks/Product/ProductsSlider';
import { SkeletonModuleLastVisitedProducts } from 'components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts';
import { useProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export const LastVisitedProductsContent: FC<LastVisitedProductsProps> = ({ productsCatnums }) => {
    const [{ data: productsData, fetching: areProductsFetching }] = useProductsByCatnums({
        variables: { catnums: productsCatnums },
    });

    const lastVisitedProducts = productsData?.productsByCatnums;

    if (!lastVisitedProducts && !areProductsFetching) {
        return null;
    }

    const productItemStyleProps = {
        size: 'small' as const,
        visibleItemsConfig: { price: false, addToCart: false, flags: false, discount: false, storeAvailability: false },
        textSize: 'xs' as const,
    };

    return (
        <>
            {lastVisitedProducts && !areProductsFetching ? (
                <ProductsSlider
                    ariaAnchorName="product-slider-last-visited"
                    gtmProductListName={GtmProductListNameType.last_visited_products}
                    productItemProps={productItemStyleProps}
                    products={lastVisitedProducts}
                    variant="lastVisited"
                    visibleSliderItems={VISIBLE_SLIDER_ITEMS_LAST_VISITED}
                />
            ) : (
                <SkeletonModuleLastVisitedProducts />
            )}
        </>
    );
};
