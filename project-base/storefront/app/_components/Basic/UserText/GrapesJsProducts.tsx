import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getProductsByCatnumsQuery } from 'app/_queries/getProductsByCatnumsQuery';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { VISIBLE_SLIDER_ITEMS_ARTICLE, VISIBLE_SLIDER_ITEMS_BLOG } from 'utils/productSlider';
import { twMergeCustom } from 'utils/twMerge';

type GrapesJsProps = {
    catnums: string[];
    rawProductPart: string;
    visibleSliderItems: number;
};

export const GrapesJsProducts = async ({ catnums, rawProductPart, visibleSliderItems }: GrapesJsProps) => {
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

    const isBlog = visibleSliderItems === VISIBLE_SLIDER_ITEMS_BLOG;
    const isArticle = visibleSliderItems === VISIBLE_SLIDER_ITEMS_ARTICLE;

    return (
        <div
            className={twMergeCustom(
                'my-4',
                isBlog && products.length > VISIBLE_SLIDER_ITEMS_BLOG ? 'xl:my-9' : '',
                isArticle && products.length > VISIBLE_SLIDER_ITEMS_ARTICLE ? 'vl:my-9' : '',
            )}
        >
            <ProductSlider totalItems={products.length} variant={isBlog ? 'blog' : 'article'}>
                {products.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        isShownInSlider
                        gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                        gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                        listIndex={index}
                        product={product}
                    />
                ))}
            </ProductSlider>
        </div>
    );
};
