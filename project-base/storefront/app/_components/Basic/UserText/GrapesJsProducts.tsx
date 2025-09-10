import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import {
    PREDEFINED_VISIBLE_ITEMS_CONFIGS,
    ProductListItem,
} from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getProductsByCatnumsQuery } from 'app/_queries/getProductsByCatnumsQuery';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'utils/productSlider';
import { twMergeCustom } from 'utils/twMerge';

type GrapesJsProps = {
    catnums: string[];
    rawProductPart: string;
    visibleSliderItems: number;
};

// TODO: Remove visibleSliderItems parameter
export const GrapesJsProducts = async ({ catnums, rawProductPart }: GrapesJsProps) => {
    const allFetchedProducts = await getProductsByCatnumsQuery(catnums);
    const products = [];

    const productCatnums = parseCatnums(rawProductPart);

    for (const productCatnum of productCatnums) {
        const matchingProduct = allFetchedProducts.find(
            (blogArticleProduct) => blogArticleProduct.catalogNumber === productCatnum,
        );

        if (matchingProduct) {
            products.push(matchingProduct);
        }
    }

    if (!products.length) {
        return null;
    }

    return (
        <section className={twMergeCustom('my-8', products.length > VISIBLE_SLIDER_ITEMS_ARTICLE ? 'vl:my-10' : '')}>
            <ProductSlider ariaAnchorName="product-slider-grapesjs" totalItems={products.length} variant="article">
                {products.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        isShownInSlider
                        gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                        gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                        listIndex={index}
                        product={product}
                        visibleItemsConfig={PREDEFINED_VISIBLE_ITEMS_CONFIGS.largeItem}
                    />
                ))}
            </ProductSlider>
        </section>
    );
};
