import { getLastVisitedProductsQuery } from 'app/_queries/getLastVisitedProductsQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { VISIBLE_SLIDER_ITEMS_LAST_VISITED } from 'utils/productSlider';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export async function LastVisitedProductsContent({ productsCatnums }: LastVisitedProductsProps) {
    const [t, lastVisitedProductsResult] = await Promise.all([
        getTranslation(),
        getLastVisitedProductsQuery(productsCatnums),
    ]);

    if (!lastVisitedProductsResult) {
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
                products={lastVisitedProductsResult}
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
