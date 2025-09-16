import { TypePriceFragment } from 'graphql/requests/prices/fragments/PriceFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type OrderItemProductPriceProps = {
    productPrice: TypeProductPriceFragment;
    quantity: number;
    unit: string | null;
    paidQuantity?: number;
    freeQuantity?: number;
    unitPriceBeforePromotion?: TypePriceFragment | null;
};

export const OrderItemProductPrice: FC<OrderItemProductPriceProps> = ({
    productPrice,
    quantity,
    unit,
    paidQuantity,
    freeQuantity,
    unitPriceBeforePromotion,
}) => {
    const formatPrice = useFormatPrice();
    const hasPromotion = freeQuantity !== undefined && freeQuantity > 0 && unitPriceBeforePromotion;
    const isSpecialPrice =
        hasPromotion ||
        (!!productPrice.percentageDiscount &&
            productPrice.percentageDiscount > 0 &&
            productPrice.percentageDiscount < 100);

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return null;
    }

    const originalTotalPrice = hasPromotion
        ? mapPriceForCalculations(unitPriceBeforePromotion!.priceWithVat) * quantity
        : mapPriceForCalculations(productPrice.basicPrice.priceWithVat) * quantity;

    const finalTotalPrice = hasPromotion
        ? mapPriceForCalculations(productPrice.priceWithVat) * (paidQuantity || quantity)
        : mapPriceForCalculations(productPrice.priceWithVat) * quantity;

    return (
        <div className="flex items-center justify-between">
            <div className="whitespace-nowrap">
                <span className="font-semibold">{formatPrice(productPrice.priceWithVat)}</span>
                <span className="text-text-less text-sm"> / {unit}</span>
            </div>

            <div className="flex flex-col gap-0.5 text-right">
                {isSpecialPrice && (
                    <div className="font-secondary text-price-before text-xs font-semibold whitespace-nowrap line-through">
                        {formatPrice(originalTotalPrice)}
                    </div>
                )}
                <div
                    className={twMergeCustom(
                        'font-secondary font-bold whitespace-nowrap',
                        isSpecialPrice ? 'text-price-discounted' : 'text-price-default',
                    )}
                >
                    {formatPrice(finalTotalPrice)}
                </div>
            </div>
        </div>
    );
};
