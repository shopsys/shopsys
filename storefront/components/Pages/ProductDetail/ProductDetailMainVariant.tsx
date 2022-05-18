import {
    ProductDetailCodeStyled,
    ProductDetailHeadingStyled,
    ProductDetailImageStyled,
    ProductDetailInfoStyled,
    ProductDetailPrefixStyled,
    ProductDetailStyled,
} from './ProductDetail.style';
import ProductDetailAccessories from './ProductDetailAccessories';
import ProductDetailGallery from './ProductDetailGallery';
import ProductDetailTabs from './ProductDetailTabs';
import ProductVariantsTable from './ProductVariantsTable';
import Webline from 'components/Layout/Webline';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { MainVariantDetailType } from 'types/product';

type ProductDetailMainVariantProps = {
    product: MainVariantDetailType;
};

/**
 * Main Product Variant Detail page component
 */
const ProductDetailMainVariant: FC<ProductDetailMainVariantProps> = (props) => {
    const testIdentifier = 'pages-productdetail-';

    const t = useTypedTranslationFunction();

    return (
        <>
            <Webline>
                <ProductDetailStyled>
                    <ProductDetailImageStyled data-testid={testIdentifier + 'gallery'}>
                        <ProductDetailGallery
                            images={props.product.images}
                            productName={props.product.name}
                            flags={props.product.flags}
                        />
                    </ProductDetailImageStyled>
                    <ProductDetailInfoStyled>
                        <ProductDetailPrefixStyled data-testid={testIdentifier + 'prefix'}>
                            {props.product.namePrefix}
                        </ProductDetailPrefixStyled>
                        <ProductDetailHeadingStyled data-testid={testIdentifier + 'name'}>
                            {props.product.name} {props.product.nameSuffix}
                        </ProductDetailHeadingStyled>
                        <ProductDetailCodeStyled data-testid={testIdentifier + 'code'}>
                            {t('Code')}: {props.product.catalogNumber}
                        </ProductDetailCodeStyled>
                    </ProductDetailInfoStyled>
                </ProductDetailStyled>
            </Webline>
            <Webline data-testid={testIdentifier + 'variants'}>
                <ProductVariantsTable variants={props.product.variants} />
            </Webline>
            <Webline data-testid={testIdentifier + 'description'}>
                <ProductDetailTabs description={props.product.description} parameters={props.product.parameters} />
            </Webline>
            <Webline data-testid={testIdentifier + 'accessories'}>
                <ProductDetailAccessories accessories={props.product.accessories} />
            </Webline>
        </>
    );
};

/* @component */
export default ProductDetailMainVariant;
