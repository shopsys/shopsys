import { FC, useRef } from 'react';
import {
    ProductDetailCodeStyled,
    ProductDetailHeadingStyled,
    ProductDetailImageStyled,
    ProductDetailInfoStyled,
    ProductDetailPrefixStyled,
    ProductDetailStyled,
} from './ProductDetail.style';
import ProductDetailAccessories from './ProductDetailAccessories';
import ProductDetailAddToCart from './ProductDetailAddToCart';
import ProductDetailAvailability from './ProductDetailStoresAvailability/ProductDetailAvailability';
import ProductDetailAvailabilityList from './ProductDetailStoresAvailability/ProductDetailAvailabilityList';
import ProductDetailGallery from './ProductDetailGallery';
import ProductDetailTabs from './ProductDetailTabs';
import { ProductDetailType } from './types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from '../../Layout/Webline';

type ProductDetailProps = {
    product: ProductDetailType;
};

/**
 * Product Detail page component
 */
const ProductDetail: FC<ProductDetailProps> = (props) => {
    const t = useTypedTranslationFunction();
    const scrollTarget = useRef<HTMLUListElement>(null);

    return (
        <>
            <Webline>
                <ProductDetailStyled>
                    <ProductDetailImageStyled>
                        <ProductDetailGallery />
                    </ProductDetailImageStyled>
                    <ProductDetailInfoStyled>
                        <ProductDetailPrefixStyled>{props.product.namePrefix}</ProductDetailPrefixStyled>
                        <ProductDetailHeadingStyled>
                            {props.product.name} {props.product.nameSuffix}
                        </ProductDetailHeadingStyled>
                        <ProductDetailCodeStyled>
                            {t('Code')}: {props.product.catalogNumber}
                        </ProductDetailCodeStyled>
                        <ProductDetailAddToCart />
                        <ProductDetailAvailability scrollTarget={scrollTarget} {...props} />
                    </ProductDetailInfoStyled>
                </ProductDetailStyled>
            </Webline>
            <Webline>
                <ProductDetailTabs description={props.product.description} />
            </Webline>
            <Webline>
                <ProductDetailAvailabilityList ref={scrollTarget} {...props} />
            </Webline>
            <Webline>
                <ProductDetailAccessories accessories={props.product.accessories} />
            </Webline>
        </>
    );
};

/* @component */
export default ProductDetail;
