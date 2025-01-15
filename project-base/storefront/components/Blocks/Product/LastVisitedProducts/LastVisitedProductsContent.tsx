import { createQuery } from 'app/_urql/urql-dto';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ProductsSlider, VISIBLE_SLIDER_ITEMS_LAST_VISITED } from 'components/Blocks/Product/ProductsSlider';
import { ProductsByCatnumsDocument } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.ssr';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

async function getLastVisitedProductsQuery(productsCatnums: string[]) {
    return createQuery(ProductsByCatnumsDocument, {
        catnums: productsCatnums,
    });
}

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

    const productItemStyleProps = {
        size: 'small' as const,
        visibleItemsConfig: { price: false, addToCart: false, flags: false, discount: false, storeAvailability: false },
        textSize: 'xs' as const,
    };

    return (
        <>
            <h5 className="mb-3">{t('Last visited products')}</h5>

            <ProductsSlider
                ariaAnchorName="product-slider-last-visited"
                gtmProductListName={GtmProductListNameType.last_visited_products}
                products={lastVisitedProducts}
                variant="lastVisited"
                visibleSliderItems={VISIBLE_SLIDER_ITEMS_LAST_VISITED}
                productItemProps={{
                    visibleItemsConfig: productItemStyleProps.visibleItemsConfig,
                    size: productItemStyleProps.size,
                }}
            />
        </>
    );
}
