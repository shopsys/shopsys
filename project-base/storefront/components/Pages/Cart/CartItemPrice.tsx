import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type CartItemPriceProps = {
    productPrice: TypeProductPriceFragment;
    quantity: number;
    paidQuantity?: number;
    freeQuantity?: number;
    totalPriceBeforePromotion?: TypePriceFragment | null;
};

export const CartItemPrice: FC<CartItemPriceProps> = ({
    productPrice,
    quantity,
    paidQuantity,
    freeQuantity,
    totalPriceBeforePromotion,
}) => {
    const formatPrice = useFormatPrice();
    const hasPromotion = freeQuantity !== undefined && freeQuantity > 0 && totalPriceBeforePromotion;
    const isSpecialPrice =
        hasPromotion ||
        (!!productPrice.percentageDiscount &&
            productPrice.percentageDiscount > 0 &&
            productPrice.percentageDiscount < 100);

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return null;
    }

    const originalPrice = hasPromotion
        ? mapPriceForCalculations(totalPriceBeforePromotion!.priceWithVat)
        : mapPriceForCalculations(productPrice.basicPrice.priceWithVat) * quantity;

    const finalPrice = hasPromotion
        ? mapPriceForCalculations(productPrice.priceWithVat) * (paidQuantity || quantity)
        : mapPriceForCalculations(productPrice.priceWithVat) * quantity;

    return (
        <div className="vl:w-36 flex items-center justify-end">
            <div className="font-secondary flex flex-col gap-0.5 text-right font-bold whitespace-nowrap">
                {isSpecialPrice && (
                    <div className="text-price-before text-sm font-semibold line-through">
                        {formatPrice(originalPrice)}
                    </div>
                )}
                <div className={twMergeCustom('text-price-default', isSpecialPrice && 'text-price-discounted')}>
                    {formatPrice(finalPrice)}
                </div>
            </div>
        </div>
    );
};
