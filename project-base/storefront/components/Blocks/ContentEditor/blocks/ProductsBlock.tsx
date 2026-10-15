import { BlockProps } from 'components/Blocks/ContentEditor/ContentRenderer';
import { ArticleProductHero } from 'components/Blocks/Product/ArticleProductHero';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { VISIBLE_SLIDER_ITEMS_ARTICLE } from 'components/Blocks/Product/productsSliderConstants';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { ProductsBlockData } from 'types/contentEditor';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { twMergeCustom } from 'utils/twMerge';

export const ProductsBlock: FC<BlockProps<ProductsBlockData>> = ({
    block,
    products: fetchedProducts,
    areProductsFetching,
}) => {
    if (areProductsFetching) {
        return (
            <div className="flex" data-tid={TIDs.content_block_products}>
                {createEmptyArray(4).map((_, index) => (
                    <SkeletonModuleProductListItem key={index} />
                ))}
            </div>
        );
    }

    // the order of the catnums is the order chosen by the administrator
    const products = block.data.catnums.flatMap((catnum) =>
        fetchedProducts.filter((product) => product.catalogNumber === catnum),
    );

    if (products.length === 0) {
        return null;
    }

    if (products.length === 1) {
        return (
            <section className="my-8" data-tid={TIDs.content_block_products}>
                <ArticleProductHero product={products[0]} />
            </section>
        );
    }

    return (
        <section
            className={twMergeCustom('my-8', products.length > VISIBLE_SLIDER_ITEMS_ARTICLE ? 'vl:my-10' : '')}
            data-tid={TIDs.content_block_products}
        >
            <ProductsSlider
                ariaAnchorName="product-slider-content-editor"
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.other}
                products={products}
                variant="article"
                visibleSliderItems={VISIBLE_SLIDER_ITEMS_ARTICLE}
            />
        </section>
    );
};
