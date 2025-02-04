import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type CartItemPriceProps = {
    productPrice: TypeProductPriceFragment;
    quantity: number;
};

export const CartItemPrice: FC<CartItemPriceProps> = ({ productPrice, quantity }) => {
    const formatPrice = useFormatPrice();
    const isSpecialPrice =
        !!productPrice.percentageDiscount &&
        productPrice.percentageDiscount > 0 &&
        productPrice.percentageDiscount < 100;

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return null;
    }

    return (
        <div className="flex items-center justify-end vl:w-36">
            <div className="flex flex-col gap-0.5 whitespace-nowrap text-right font-secondary font-bold">
                <div
                    className={twMergeCustom(
                        'text-price',
                        isSpecialPrice && 'text-sm font-semibold text-priceBefore line-through',
                    )}
                >
                    {formatPrice(mapPriceForCalculations(productPrice.basicPrice.priceWithVat) * quantity)}
                </div>

                {isSpecialPrice && (
                    <div className="text-priceDiscounted">
                        {formatPrice(mapPriceForCalculations(productPrice.priceWithVat) * quantity)}
                    </div>
                )}
            </div>
        </div>
    );
};
