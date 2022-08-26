import { ProductPriceMainStyled, ProductPriceStyled } from './ProductPrice.style';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { ProductPriceType } from 'types/price';

type ProductPriceProps = {
    productPrice: ProductPriceType;
};

const TEST_IDENTIFIER = 'blocks-product-price';

export const ProductPrice: FC<ProductPriceProps> = ({ productPrice }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <ProductPriceStyled>
            <ProductPriceMainStyled data-testid={TEST_IDENTIFIER}>
                {productPrice.isPriceFrom && t('From') + '\u00A0'}
                {formatPrice(productPrice.priceWithVat)}
            </ProductPriceMainStyled>
        </ProductPriceStyled>
    );
};
