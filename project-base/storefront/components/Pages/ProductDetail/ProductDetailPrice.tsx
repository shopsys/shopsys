import { DeferredCountdown } from './Countdown/DeferredCountdown';
import { Flag } from 'components/Basic/Flag/Flag';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type ProductDetailPriceProps = {
    productPrice: TypeProductPriceFragment;
};

export const ProductDetailPrice: FC<ProductDetailPriceProps> = ({ productPrice }) => {
    const formatPrice = useFormatPrice();
    const isSpecialPrice =
        !!productPrice.percentageDiscount &&
        productPrice.percentageDiscount > 0 &&
        productPrice.percentageDiscount < 100;

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2">
            <div className="flex flex-wrap items-center gap-x-2">
                <div
                    className={twMergeCustom(
                        'font-secondary text-price-default text-2xl font-bold whitespace-nowrap',
                        isSpecialPrice && 'text-price-before text-base font-semibold line-through',
                    )}
                >
                    {formatPrice(productPrice.basicPrice.priceWithVat)}
                </div>

                {isSpecialPrice && (
                    <>
                        <Flag type="discount">-{productPrice.percentageDiscount}%</Flag>

                        <div className="font-secondary text-price-discounted text-2xl font-bold whitespace-nowrap">
                            {formatPrice(productPrice.priceWithVat)}
                        </div>
                    </>
                )}
            </div>

            {isSpecialPrice && <DeferredCountdown endTime={productPrice.nextPriceChange} />}
        </div>
    );
};
