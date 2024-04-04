import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type ProductPriceProps = {
    productPrice: TypeProductPriceFragment;
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
