import { ProductPriceFragmentApi } from 'graphql/generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type ProductPriceProps = {
    productPrice: ProductPriceFragmentApi;
};

export const ProductPrice: FC<ProductPriceProps> = ({ productPrice }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <div className="text-lg font-bold text-primary">
            {productPrice.isPriceFrom && t('From') + '\u00A0'}
            {formatPrice(productPrice.priceWithVat)}
        </div>
    );
};
