import { ProductsList } from 'app/_components/Blocks/Product/ProductsList/ProductsList';
import { getLastVisitedProductsQuery } from 'app/_queries/getLastVisitedProductsQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

// import { ProductsSlider, VISIBLE_SLIDER_ITEMS_LAST_VISITED } from 'components/Blocks/Product/ProductsSlider';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export async function LastVisitedProductsContent({ productsCatnums }: LastVisitedProductsProps) {
    const [t, lastVisitedProductsResult] = await Promise.all([
        getTranslation(),
        getLastVisitedProductsQuery(productsCatnums),
    ]);

    const lastVisitedProducts = lastVisitedProductsResult.data?.productsByCatnums;

    if (!lastVisitedProducts) {
        return null;
    }

    // TODO: SLIDER
    const productItemStyleProps = {
        size: 'small' as const,
        visibleItemsConfig: {
            price: false,
            addToCart: false,
            flags: false,
            discount: false,
            storeAvailability: false,
            productListButtons: true, // TODO: remove later
        },
        textSize: 'xs' as const,
    };

    return (
        <>
            <h5 className="mb-4">{t('Last visited products')}</h5>

            {/* <ProductsSlider
                gtmProductListName={GtmProductListNameType.last_visited_products}
                products={productsWithListState}
                variant="lastVisited"
                visibleSliderItems={VISIBLE_SLIDER_ITEMS_LAST_VISITED}
                productItemProps={{
                    visibleItemsConfig: productItemStyleProps.visibleItemsConfig,
                    size: productItemStyleProps.size,
                }}
            /> */}

            <ProductsList
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                gtmProductListName={GtmProductListNameType.last_visited_products}
                products={lastVisitedProducts}
            />
        </>
    );
}
