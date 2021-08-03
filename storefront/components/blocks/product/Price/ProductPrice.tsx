import { FC } from 'react';
import { formatPrice } from '../../../../utils/formatting';
import { ProductPriceType } from '../types';
import { useTranslation } from 'react-i18next';

const ProductPrice: FC<ProductPriceType> = (props) => {
    const { t } = useTranslation();

    return (
        <div>
            {/** TODO KOD - we need a space between "From" and the price, I am not sure if this is a correct approach, so feel free to re-work it or remove this comment please if it is ok solution for you */}
            {props.isPriceFrom && t('From') + '\u00A0'}
            {formatPrice(props.priceWithVat, props.currencyCode)}
        </div>
    );
};

export default ProductPrice;
