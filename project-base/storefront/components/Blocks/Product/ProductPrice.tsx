import { ProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';

type ProductPriceProps = {
    productPrice: ProductPriceFragment;
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
