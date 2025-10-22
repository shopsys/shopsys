import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type CartItemPriceProps = {
    productPrice: TypeProductPriceFragment;
    quantity: number;
    freeQuantity: number;
};

export const CartItemPrice: FC<CartItemPriceProps> = ({ productPrice, quantity, freeQuantity }) => {
    const formatPrice = useFormatPrice();
    const isSpecialPrice =
        !!productPrice.percentageDiscount &&
        productPrice.percentageDiscount > 0 &&
        productPrice.percentageDiscount < 100;
    const isLowerPriceApplied = isSpecialPrice || freeQuantity > 0;

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return null;
    }

    return (
        <div className="vl:w-36 flex items-center justify-end">
            <div className="font-secondary flex flex-col gap-0.5 text-right font-bold whitespace-nowrap">
                <div
                    className={twMergeCustom(
                        'text-price-default',
                        isLowerPriceApplied && 'text-price-before text-sm font-semibold line-through',
                    )}
                >
                    {formatPrice(mapPriceForCalculations(productPrice.basicPrice.priceWithVat) * quantity)}
                </div>

                {isLowerPriceApplied && (
                    <div className="text-price-discounted">
                        {formatPrice(mapPriceForCalculations(productPrice.priceWithVat) * (quantity - freeQuantity))}
                    </div>
                )}
            </div>
        </div>
    );
};
