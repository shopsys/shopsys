import { Flag } from 'components/Basic/Flag/Flag';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type ProductPriceProps = {
    productPrice: TypeProductPriceFragment;
    isPriceFromVisible?: boolean;
    placeholder?: string;
    textPriceSize?: 'base' | 'lg';
};

export const ProductPrice: FC<ProductPriceProps> = ({
    productPrice,
    isPriceFromVisible,
    placeholder,
    textPriceSize = 'lg',
    className,
}) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const isSpecialPrice =
        !!productPrice.percentageDiscount &&
        productPrice.percentageDiscount > 0 &&
        productPrice.percentageDiscount < 100;

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return placeholder ?? null;
    }

    return (
        <div className={twMergeCustom('flex flex-wrap items-center gap-x-2 gap-y-0.5', className)}>
            <div
                className={twMergeCustom(
                    'font-secondary text-price-default text-lg font-bold whitespace-nowrap',
                    textPriceSize === 'base' ? 'text-base' : 'text-lg',
                    isSpecialPrice && 'text-price-before text-sm font-semibold line-through',
                )}
            >
                {productPrice.isPriceFrom && isPriceFromVisible && t('From') + '\u00A0'}
                {formatPrice(productPrice.basicPrice.priceWithVat)}
            </div>

            {isSpecialPrice && (
                <>
                    <Flag type="discount">-{productPrice.percentageDiscount}%</Flag>

                    <div
                        className={twMergeCustom(
                            'font-secondary text-price-discounted font-bold whitespace-nowrap',
                            textPriceSize === 'base' ? 'text-base' : 'text-lg',
                        )}
                    >
                        {productPrice.isPriceFrom && isPriceFromVisible && t('From') + '\u00A0'}
                        {formatPrice(productPrice.priceWithVat)}
                    </div>
                </>
            )}
        </div>
    );
};
