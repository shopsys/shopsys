import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { useProductsByCatnumsApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { parseCatnums } from 'helpers/parsing/grapesJsParser';
import { replaceAll } from 'helpers/replaceAll';
import { memo } from 'react';
import { UserText } from './UserText';

type GrapesJsParserProps = {
    text: string;
    uuid: string;
};

export const GrapesJsParser: FC<GrapesJsParserProps> = memo(({ text, uuid }) => {
    const catnums = parseCatnums(text);
    const [allProductsResponse] = useProductsByCatnumsApi({ variables: { catnums } });

    const renderArticleProductList = (part: string) => {
        if (!allProductsResponse.data?.productsByCatnums.length) {
            return (
                <ProductsList
                    products={[]}
                    gtmProductListName={GtmProductListNameType.other}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    key={uuid}
                />
            );
        }

        const products = [];

        const productCatnums = replaceAll(part, /\[gjc-comp-ProductList&#61;|\]/g, '').split(',');
        for (const productCatnum of productCatnums) {
            const matchingProduct = allProductsResponse.data.productsByCatnums.find(
                (blogArticleProduct) => blogArticleProduct.catalogNumber === productCatnum,
            );

            if (matchingProduct) {
                products.push(matchingProduct);
            }
        }

        return (
            <ProductsList
                products={products}
                gtmProductListName={GtmProductListNameType.other}
                gtmMessageOrigin={GtmMessageOriginType.other}
                key={uuid}
            />
        );
    };

    const renderGrapesJsParts = (part: string) => {
        if (part.match(/\[gjc-comp-(.*?)\]/g)) {
            return renderArticleProductList(part);
        }

        return <UserText key={uuid} htmlContent={part} isGrapesJs />;
    };

    return renderGrapesJsParts(text);
});

GrapesJsParser.displayName = 'GrapesJsParser';
