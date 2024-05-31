import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
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

    return (
        <>
            {lastVisitedProducts && !areProductsFetching ? (
                <ProductsSlider
                    gtmProductListName={GtmProductListNameType.last_visited_products}
                    products={lastVisitedProducts}
                />
            ) : (
                <SkeletonModuleLastVisitedProducts />
            )}
        </>
    );
};
