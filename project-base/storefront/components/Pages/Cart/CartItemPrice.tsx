import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type CartItemPriceProps = {
    productPrice: TypeProductPriceFragment;
    quantity: number;
    freeQuantity: number;
    className?: string;
};

export const CartItemPrice: FC<CartItemPriceProps> = ({ productPrice, quantity, freeQuantity, className }) => {
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
        <div className={twMergeCustom('flex vl:w-32 items-center justify-end', className)}>
            <div className="flex flex-col gap-0.5 whitespace-nowrap text-right font-bold font-secondary">
                <div
                    className={twMergeCustom(
                        'text-price-default',
                        isLowerPriceApplied && 'font-semibold text-price-before text-sm line-through',
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
