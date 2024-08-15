import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { SkeletonModuleProductListItem } from 'components/Blocks/Skeleton/SkeletonModuleProductListItem';
import { TypeProductsByCatnums } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { parseCatnums } from 'utils/parsing/grapesJsParser';
import { twMergeCustom } from 'utils/twMerge';

type GrapesJsProps = {
    rawProductPart: string;
    allFetchedProducts?: TypeProductsByCatnums | undefined;
    areProductsFetching: boolean;
    isBlogPage?: boolean;
};

export const GrapesJsProducts: FC<GrapesJsProps> = ({
    rawProductPart,
    allFetchedProducts,
    areProductsFetching,
    isBlogPage = false,
}) => {
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

    if (areProductsFetching) {
        return (
            <div className="flex">
                {createEmptyArray(4).map((_, index) => (
                    <SkeletonModuleProductListItem key={index} />
                ))}
            </div>
        );
    }

    if (!products.length) {
        return null;
    }

    return (
        <div
            className={twMergeCustom(
                'my-4',
                isBlogPage && products.length > 3 ? 'xl:my-9' : '',
                !isBlogPage && products.length > 4 ? 'vl:my-9' : '',
            )}
        >
            <ProductsSlider
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.other}
                isBlogPage={isBlogPage}
                products={products}
            />
        </div>
    );
};
