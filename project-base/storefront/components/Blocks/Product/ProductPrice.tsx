import { Flag } from 'components/Basic/Flag/Flag';
import { TIDs } from 'cypress/tids';
import { TypeListedProductPriceFragment } from 'graphql/requests/products/fragments/ListedProductPriceFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isPriceVisible } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type ProductPriceProps = {
    productPrice: TypeProductPriceFragment | TypeListedProductPriceFragment;
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
        <div
            className={twMergeCustom('flex flex-wrap items-center gap-x-2 gap-y-0.5', className)}
            data-tid={TIDs.product_price}
        >
            <div
                className={twMergeCustom(
                    'whitespace-nowrap font-bold font-secondary text-lg text-price-default',
                    textPriceSize === 'base' ? 'text-md' : 'text-lg',
                    isSpecialPrice && 'font-semibold text-price-before text-sm line-through',
                )}
            >
                {productPrice.isPriceFrom && isPriceFromVisible && `${t('From')}\u00A0`}
                {formatPrice(productPrice.basicPrice.priceWithVat)}
            </div>

            {isSpecialPrice && (
                <>
                    <Flag type="discount">-{productPrice.percentageDiscount}%</Flag>

                    <div
                        className={twMergeCustom(
                            'whitespace-nowrap font-bold font-secondary text-price-discounted',
                            textPriceSize === 'base' ? 'text-md' : 'text-lg',
                        )}
                    >
                        {productPrice.isPriceFrom && isPriceFromVisible && `${t('From')}\u00A0`}
                        {formatPrice(productPrice.priceWithVat)}
                    </div>
                </>
            )}
        </div>
    );
};
