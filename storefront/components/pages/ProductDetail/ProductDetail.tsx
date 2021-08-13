import {
    StyledProductDetail,
    StyledProductDetailCode,
    StyledProductDetailHeading,
    StyledProductDetailImage,
    StyledProductDetailInfo,
    StyledProductDetailPrefix,
} from './ProductDetail.style';
import { FC } from 'react';
import { ProductDetailType } from '../../../connectors/products/ProductDetailType';
import ShopsysInUserText from 'components/in/ShopsysInUserText';
import { useTranslation } from 'next-i18next';
import Webline from '../../layout/Webline';

type ProductDetailProps = {
    data: ProductDetailType;
};

const ProductDetail: FC<ProductDetailProps> = (props) => {
    const { t } = useTranslation();
    const data = props.data;

    return (
        <Webline>
            <StyledProductDetail>
                <StyledProductDetailImage>
                    <img src="http://placeimg.com/946/406/any" />
                </StyledProductDetailImage>
                <StyledProductDetailInfo>
                    <StyledProductDetailPrefix>{data.namePrefix}</StyledProductDetailPrefix>
                    <StyledProductDetailHeading>
                        {data.name} {data.nameSuffix}
                    </StyledProductDetailHeading>
                    <StyledProductDetailCode>
                        {t('Code')}: {data.catalogNumber}
                    </StyledProductDetailCode>
                </StyledProductDetailInfo>
                <ShopsysInUserText htmlContent={data.description} />
            </StyledProductDetail>
        </Webline>
    );
};

export default ProductDetail;
