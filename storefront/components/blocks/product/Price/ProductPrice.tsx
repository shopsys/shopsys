import { ProductPriceStyled, ProductPriceMainStyled} from './ProductPrice.style';

import { FC } from 'react';
import { formatPrice } from '../../../../utils/formatting';
import { ProductPriceType } from '../types';
import { useTranslation } from 'react-i18next';

const ProductPrice: FC<ProductPriceType> = (props) => {
    const { t } = useTranslation();

    return (
        <ProductPriceStyled>
            <ProductPriceMainStyled>
                {props.isPriceFrom && t('From') + '\u00A0'}
                {formatPrice(props.priceWithVat, props.currencyCode)}
            </ProductPriceMainStyled>
        </ProductPriceStyled>
    );
};

export default ProductPrice;
