import { ProductPriceMainStyled, ProductPriceStyled } from './ProductPrice.style';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import { ProductPriceType } from 'components/Blocks/Product/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ProductPrice: FC<ProductPriceType> = (props) => {
    const t = useTypedTranslationFunction();

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
