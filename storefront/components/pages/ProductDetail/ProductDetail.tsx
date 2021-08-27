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
import { ProductDetailType } from './types';
import ShopsysInUserText from 'components/in/ShopsysInUserText';
import { useTranslation } from 'next-i18next';
import Webline from '../../layout/Webline';

type ProductDetailProps = {
    product: ProductDetailType;
};

const ProductDetail: FC<ProductDetailProps> = (props) => {
    const { t } = useTranslation();
    const scrollTarget = useRef<HTMLUListElement>(null);

    return (
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
                <ShopsysInUserText htmlContent={props.product.description} />
                <ProductDetailAvailabilityList ref={scrollTarget} {...props} />
            </StyledProductDetail>
        </Webline>
    );
};

export default ProductDetail;
