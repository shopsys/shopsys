import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getLastVisitedProductsQuery } from 'app/_queries/getLastVisitedProductsQuery';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export const LastVisitedProductsContent = async ({ productsCatnums }: LastVisitedProductsProps) => {
    const lastVisitedProductsResult = await getLastVisitedProductsQuery(productsCatnums);

    const lastVisitedProducts = lastVisitedProductsResult?.productsByCatnums;

    if (!lastVisitedProducts) {
        return null;
    }

    const productItemStyleProps = {
        size: 'small' as const,
        visibleItemsConfig: {
            price: false,
            addToCart: false,
            flags: false,
            discount: false,
            storeAvailability: false,
        },
        textSize: 'xs' as const,
    };

    return (
        <ProductSlider totalItems={lastVisitedProducts.length} variant="lastVisited">
            {lastVisitedProducts.map((product, index) => (
                <ProductListItem
                    key={product.uuid}
                    isShownInSlider
                    gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                    gtmProductListName={GtmProductListNameType.last_visited_products}
                    listIndex={index}
                    product={product}
                    {...productItemStyleProps}
                />
            ))}
        </ProductSlider>
    );
};
