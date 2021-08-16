import {
    StyledProductDetail,
    StyledProductDetailCode,
    StyledProductDetailHeading,
    StyledProductDetailImage,
    StyledProductDetailInfo,
    StyledProductDetailPrefix,
} from './ProductDetail.style';
import { FC } from 'react';
import { ProductDetailType } from './types';
import ShopsysInUserText from 'components/in/ShopsysInUserText';
import { useTranslation } from 'next-i18next';
import Webline from '../../layout/Webline';

type ProductDetailProps = {
    product: ProductDetailType;
};

const ProductDetail: FC<ProductDetailProps> = (props) => {
    const { t } = useTranslation();

    return (
        <Webline>
            <StyledProductDetail>
                <StyledProductDetailImage>
                    <img src="http://placeimg.com/946/406/any" />
                </StyledProductDetailImage>
                <StyledProductDetailInfo>
                    <StyledProductDetailPrefix>{props.product.namePrefix}</StyledProductDetailPrefix>
                    <StyledProductDetailHeading>
                        {props.product.name} {props.product.nameSuffix}
                    </StyledProductDetailHeading>
                    <StyledProductDetailCode>
                        {t('Code')}: {props.product.catalogNumber}
                    </StyledProductDetailCode>
                </StyledProductDetailInfo>
                <ShopsysInUserText htmlContent={props.product.description} />
            </StyledProductDetail>
        </Webline>
    );
};

export default ProductDetail;
