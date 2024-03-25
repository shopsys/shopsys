import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { SkeletonModuleLastVisitedProducts } from 'components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts';
import { useProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export const LastVisitedProductsContent: FC<LastVisitedProductsProps> = ({ productsCatnums }) => {
    const [{ data: result, fetching }] = useProductsByCatnums({
        variables: { catnums: productsCatnums },
    });

    const lastVisitedProducts = result?.productsByCatnums;

    if (!lastVisitedProducts && !fetching) {
        return null;
    }

    return (
        <>
            {lastVisitedProducts && !fetching ? (
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
