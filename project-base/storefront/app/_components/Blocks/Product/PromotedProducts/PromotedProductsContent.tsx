import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getPromotedProductsQuery } from 'app/_queries/getPromotedProductsQuery';
import { getTranslation } from 'app/_utils/translation/getTranslation';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export const PromotedProductsContent = async () => {
    const [t, promotedProductsResult] = await Promise.all([getTranslation(), getPromotedProductsQuery()]);

    if (!promotedProductsResult) {
        return null;
    }

    return (
        <>
            <h3 className="mb-3">{t('News on offer')}</h3>

            <ProductSlider
                ariaAnchorName="product-slider-promoted"
                totalItems={promotedProductsResult.promotedProducts.length}
                variant="default"
            >
                {promotedProductsResult.promotedProducts.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        isShownInSlider
                        gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                        gtmProductListName={GtmProductListNameType.last_visited_products}
                        listIndex={index}
                        product={product}
                    />
                ))}
            </ProductSlider>
        </>
    );
};
