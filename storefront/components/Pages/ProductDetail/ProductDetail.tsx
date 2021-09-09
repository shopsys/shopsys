import { FC, useRef } from 'react';
import {
    StyledProductDetail,
    StyledProductDetailCode,
    StyledProductDetailHeading,
    StyledProductDetailImage,
    StyledProductDetailInfo,
    StyledProductDetailPrefix,
} from './ProductDetail.style';
import ProductDetailAvailability from './ProductDetailStoresAvailability/ProductDetailAvailability';
import ProductDetailAvailabilityList from './ProductDetailStoresAvailability/ProductDetailAvailabilityList';
import ProductDetailGallery from './ProductDetailGallery';
import ProductDetailTabs from './ProductDetailTabs';
import { ProductDetailType } from './types';
import { useTypedTranslationFunction } from 'hooks/UseTypedTranslationFunction';
import Webline from '../../Layout/Webline';

type ProductDetailProps = {
    product: ProductDetailType;
};

const ProductDetail: FC<ProductDetailProps> = (props) => {
    const t = useTypedTranslationFunction();
    const scrollTarget = useRef<HTMLUListElement>(null);

    return (
        <>
            <Webline>
                <StyledProductDetail>
                    <StyledProductDetailImage>
                        <ProductDetailGallery />
                    </StyledProductDetailImage>
                    <StyledProductDetailInfo>
                        <StyledProductDetailPrefix>{props.product.namePrefix}</StyledProductDetailPrefix>
                        <StyledProductDetailHeading>
                            {props.product.name} {props.product.nameSuffix}
                        </StyledProductDetailHeading>
                        <StyledProductDetailCode>
                            {t('Code')}: {props.product.catalogNumber}
                        </StyledProductDetailCode>
                        <ProductDetailAvailability scrollTarget={scrollTarget} {...props} />
                    </StyledProductDetailInfo>
                </StyledProductDetail>
            </Webline>
            <Webline>
                <ProductDetailTabs description={props.product.description} />
            </Webline>
            <Webline>
                <ProductDetailAvailabilityList ref={scrollTarget} {...props} />
            </Webline>
        </>
    );
};

export default ProductDetail;
