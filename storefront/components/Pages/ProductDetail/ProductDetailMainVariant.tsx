import {
    ProductDetailCodeStyled,
    ProductDetailHeadingStyled,
    ProductDetailImageStyled,
    ProductDetailInfoStyled,
    ProductDetailPrefixStyled,
    ProductDetailStyled,
} from './ProductDetail.style';
import { FC } from 'react';
import { MainVariantDetailType } from 'connectors/products/types';
import ProductDetailAccessories from './ProductDetailAccessories';
import ProductDetailGallery from './ProductDetailGallery';
import ProductDetailTabs from './ProductDetailTabs';
import ProductVariantsTable from './ProductVariantsTable';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type ProductDetailMainVariantProps = {
    product: MainVariantDetailType;
};

/**
 * Main Product Variant Detail page component
 */
const ProductDetailMainVariant: FC<ProductDetailMainVariantProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            <Webline>
                <ProductDetailStyled>
                    <ProductDetailImageStyled>
                        <ProductDetailGallery images={props.product.images} productName={props.product.name} />
                    </ProductDetailImageStyled>
                    <ProductDetailInfoStyled>
                        <ProductDetailPrefixStyled>{props.product.namePrefix}</ProductDetailPrefixStyled>
                        <ProductDetailHeadingStyled>
                            {props.product.name} {props.product.nameSuffix}
                        </ProductDetailHeadingStyled>
                        <ProductDetailCodeStyled>
                            {t('Code')}: {props.product.catalogNumber}
                        </ProductDetailCodeStyled>
                    </ProductDetailInfoStyled>
                </ProductDetailStyled>
            </Webline>
            <Webline>
                <ProductVariantsTable variants={props.product.variants} />
            </Webline>
            <Webline>
                <ProductDetailTabs description={props.product.description} parameters={props.product.parameters} />
            </Webline>
            <Webline>
                <ProductDetailAccessories accessories={props.product.accessories} />
            </Webline>
        </>
    );
};

/* @component */
export default ProductDetailMainVariant;
