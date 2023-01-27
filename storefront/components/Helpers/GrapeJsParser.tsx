import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { UserText } from 'components/Helpers/UserText/UserText';
import { FC, memo } from 'react';
import { ListedProductType } from 'types/product';

type GrapeJsParserProps = {
    text: string;
    allProducts: ListedProductType[];
};

const PRODUCT_STRING_PATTERN =
    /<section class=".+?">|<\/section>| id=".*?"|<div class="gjs-product" data-product=".+?"><\/div>|<div data-product=".+?" class="gjs-product"><\/div>/g;
const PRODUCTS_STRING_PATTERN =
    /<div class="gjs-products" data-products=".+?"><\/div>|<div data-products=".+?" class="gjs-products"><\/div>/g;

const GrapeJsParser: FC<GrapeJsParserProps> = ({ text, allProducts }) => {
    const preparedText = text.replaceAll(PRODUCT_STRING_PATTERN, '').replaceAll(PRODUCTS_STRING_PATTERN, (data) => {
        const products = /data-products="(?<products>.+?)"/g.exec(data)?.groups?.products;
        return `|||[gjc-comp-ProductList=${products}]|||`;
    });

    const ComponentPreparation = (part: string, index: number) => {
        if (part.match(/\[gjc-comp-ProductList=(.*?)\]/g) !== null) {
            const products = part
                .replaceAll(/\[gjc-comp-ProductList=|\]/g, '')
                .split(',')
                .map((product) =>
                    allProducts.find((blogArticleProduct) => blogArticleProduct.catalogNumber === product),
                )
                .filter(Boolean) as ListedProductType[];
            return <ProductsSlider products={products} gtmListName={'blog article'} key={index} />;
        }

        return null;
    };

    const dividedText = preparedText.split('|||').filter(Boolean);

    return (
        <>
            {dividedText.map((part, index) =>
                part.match(/\[gjc-comp-(.*?)\]/g) ? (
                    ComponentPreparation(part, index)
                ) : (
                    <UserText key={index} htmlContent={part} isGrapesJs={true} />
                ),
            )}
        </>
    );
};

export default memo(GrapeJsParser);
