import { ProductPriceMainStyled, ProductPriceStyled } from './ProductPrice.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { ProductPriceType } from 'types/price';

const ProductPrice: FC<ProductPriceType> = (props) => {
    const testIdentifier = 'blocks-product-price';

    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <ProductPriceStyled>
            <ProductPriceMainStyled data-testid={testIdentifier}>
                {props.isPriceFrom && t('From') + '\u00A0'}
                {formatPrice(props.priceWithVat)}
            </ProductPriceMainStyled>
        </ProductPriceStyled>
    );
};

export default ProductPrice;
