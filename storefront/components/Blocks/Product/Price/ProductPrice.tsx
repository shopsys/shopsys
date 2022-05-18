import { ProductPriceMainStyled, ProductPriceStyled } from './ProductPrice.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { ProductPriceType } from 'types/price';
import { formatPrice } from 'utils/formatting';

const ProductPrice: FC<ProductPriceType> = (props) => {
    const testIdentifier = 'blocks-product-price';

    const t = useTypedTranslationFunction();

    return (
        <ProductPriceStyled>
            <ProductPriceMainStyled data-testid={testIdentifier}>
                {props.isPriceFrom && t('From') + '\u00A0'}
                {formatPrice(props.priceWithVat, props.currencyCode, t)}
            </ProductPriceMainStyled>
        </ProductPriceStyled>
    );
};

export default ProductPrice;
