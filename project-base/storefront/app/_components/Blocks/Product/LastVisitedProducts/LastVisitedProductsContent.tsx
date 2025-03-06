import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getLastVisitedProductsQuery } from 'app/_queries/getLastVisitedProductsQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

type LastVisitedProductsProps = {
    productsCatnums: string[];
};

export const LastVisitedProductsContent = async ({ productsCatnums }: LastVisitedProductsProps) => {
    const [t, lastVisitedProductsResult] = await Promise.all([
        getTranslation(),
        getLastVisitedProductsQuery(productsCatnums),
    ]);

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
        <section>
            <h3 className="mb-3">{t('Last visited products')}</h3>

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
        </section>
    );
};
