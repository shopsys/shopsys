import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { twMergeCustom } from 'utils/twMerge';

type ProductPriceProps = {
    productPrice: TypeProductPriceFragment;
    isPriceFromVisible?: boolean;
};

export const ProductPrice: FC<ProductPriceProps> = ({ productPrice, isPriceFromVisible, className }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();

    return (
        <div className={twMergeCustom('text-base font-bold text-primaryDark', className)}>
            {productPrice.isPriceFrom && isPriceFromVisible && t('From') + '\u00A0'}
            {formatPrice(productPrice.priceWithVat)}
        </div>
    );
};
