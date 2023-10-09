import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { SkeletonProductListItem } from 'components/Blocks/Skeleton/SkeletonProductListItem';
import { ProductsByCatnumsApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { createEmptyArray } from 'helpers/arrayUtils';
import { parseCatnums } from 'helpers/parsing/grapesJsParser';

type GrapesJsProps = {
    rawProductPart: string;
    allFetchedProducts?: ProductsByCatnumsApi | undefined;
    fetching: boolean;
};

export const GrapesJsProducts: FC<GrapesJsProps> = ({ rawProductPart, allFetchedProducts, fetching }) => {
    const products = [];

    const productCatnums = parseCatnums(rawProductPart);

    for (const productCatnum of productCatnums) {
        const matchingProduct = allFetchedProducts?.productsByCatnums.find(
            (blogArticleProduct) => blogArticleProduct.catalogNumber === productCatnum,
        );

        if (matchingProduct) {
            products.push(matchingProduct);
        }
    }

    if (fetching) {
        return (
            <div className="flex">
                {createEmptyArray(4).map((_, index) => (
                    <SkeletonProductListItem key={index} />
                ))}
            </div>
        );
    }

    if (!products.length) {
        return null;
    }

    return (
        <ProductsSlider
            gtmMessageOrigin={GtmMessageOriginType.other}
            gtmProductListName={GtmProductListNameType.other}
            products={products}
        />
    );
};
