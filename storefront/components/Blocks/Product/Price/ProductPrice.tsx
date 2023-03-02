import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { ProductPriceType } from 'types/price';

type ProductPriceProps = {
    productPrice: ProductPriceType;
};

const TEST_IDENTIFIER = 'blocks-product-price';

export const ProductPrice: FC<ProductPriceProps> = ({ productPrice }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();

    return (
        <div className="flex flex-wrap items-center">
            <div className="order-2 mr-3 text-lg font-bold text-primary" data-testid={TEST_IDENTIFIER}>
                {productPrice.isPriceFrom && t('From') + '\u00A0'}
                {formatPrice(productPrice.priceWithVat)}
            </div>
        </div>
    );
};
