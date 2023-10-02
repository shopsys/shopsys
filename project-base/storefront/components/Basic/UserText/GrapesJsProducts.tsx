import { ProductListItemSkeleton } from 'components/Blocks/Product/ProductsList/ProductListItemSkeleton';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { ProductsByCatnumsApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { createEmptyArray } from 'helpers/arrayUtils';
import { replaceAll } from 'helpers/replaceAll';

type GrapesJsProps = {
    rawProductPart: string;
    allFetchedProducts?: ProductsByCatnumsApi | undefined;
    fetching: boolean;
};

export const GrapesJsProducts: FC<GrapesJsProps> = ({ rawProductPart, allFetchedProducts, fetching }) => {
    const products = [];

    const productCatnums = replaceAll(rawProductPart, /\[gjc-comp-ProductList&#61;|\]/g, '').split(',');

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
                    <ProductListItemSkeleton key={index} />
                ))}
            </div>
        );
    }

    if (!products.length) {
        return null;
    }

    return (
        <ProductsList
            products={products}
            gtmProductListName={GtmProductListNameType.other}
            gtmMessageOrigin={GtmMessageOriginType.other}
        />
    );
};
