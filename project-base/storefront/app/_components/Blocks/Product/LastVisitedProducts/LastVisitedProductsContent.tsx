import { getLastVisitedProductsQuery } from 'app/_queries/getLastVisitedProductsQuery';
import { getProducsWithListStateQuery } from 'app/_queries/getProducsWithListStateQuery';
import { ProductsSlider, VISIBLE_SLIDER_ITEMS_LAST_VISITED } from 'components/Blocks/Product/ProductsSlider';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getServerT } from 'utils/getServerTranslation';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export async function LastVisitedProductsContent({ productsCatnums }: LastVisitedProductsProps) {
    const [t, lastVisitedProductsResult] = await Promise.all([
        getServerT(),
        getLastVisitedProductsQuery(productsCatnums),
    ]);

    const lastVisitedProducts = lastVisitedProductsResult.data?.productsByCatnums;

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
            productListButtons: true, // TODO: remove later
        },
        textSize: 'xs' as const,
    };

    const productsWithListState = await getProducsWithListStateQuery(lastVisitedProducts);

    return (
        <>
            <h5 className="mb-4">{t('Last visited products')}</h5>

            <ProductsSlider
                gtmProductListName={GtmProductListNameType.last_visited_products}
                products={productsWithListState}
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
