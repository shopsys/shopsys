import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { UserText } from 'components/Basic/UserText/UserText';
import { replaceAll } from 'helpers/replaceAll';
import { memo } from 'react';
import { GtmProductListNameType } from 'gtm/types/enums';
import { GJS_PRODUCTS_SEPARATOR, parseCatnums } from 'helpers/parsing/grapesJsParser';
import { ListedProductFragmentApi } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { useProductsByCatnumsApi } from 'graphql/requests/products/queries/ProductsByCatnumsQuery.generated';

type GrapesJsParserProps = {
    text: string;
};

export const GrapesJsParser: FC<GrapesJsParserProps> = memo(({ text }) => {
    const productsCatnum = parseCatnums(text);

    const dividedText = text.split(GJS_PRODUCTS_SEPARATOR).filter(Boolean);

    const [result] = useProductsByCatnumsApi({ variables: { catnums: productsCatnum } });

    const allProducts = result.data?.productsByCatnums;

    const renderArticleProductList = (part: string) => {
        const products = allProducts
            ? (replaceAll(part, /\[gjc-comp-ProductList&#61;|\]/g, '')
                  .split(',')
                  .map((productCatnum) => allProducts.find((product) => product.catalogNumber === productCatnum))
                  .filter(Boolean) as ListedProductFragmentApi[])
            : [];

        return <ProductsSlider products={products} gtmProductListName={GtmProductListNameType.blog_article_detail} />;
    };

    return (
        <>
            {dividedText.map((part, index) => {
                const isWithProductsList = part.match(/\[gjc-comp-(.*?)\]/g);

                return isWithProductsList ? (
                    renderArticleProductList(part)
                ) : (
                    <UserText key={index} htmlContent={part} isGrapesJs />
                );
            })}
        </>
    );
});

GrapesJsParser.displayName = 'GrapesJsParser';
