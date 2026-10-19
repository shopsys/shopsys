import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';
import { OrderItemPrice } from './OrderItemPrice';

type OrderItemProductPriceProps = {
    productPrice: TypeProductPriceFragment | Pick<TypeOrderDetailItemFragment, 'unitPrice' | 'totalPrice'>;
    quantity: number;
    freeQuantity: number | null;
    unit: string | null;
};

export const OrderItemProductPrice: FC<OrderItemProductPriceProps> = ({
    productPrice,
    quantity,
    unit,
    freeQuantity,
}) => {
    const formatPrice = useFormatPrice();
    if ('unitPrice' in productPrice) {
        return (
            <OrderItemPrice
                totalPrice={productPrice.totalPrice.priceWithVat}
                unit={unit}
                unitPrice={productPrice.unitPrice.priceWithVat}
            />
        );
    }

    const isSpecialPrice =
        !!productPrice.percentageDiscount &&
        productPrice.percentageDiscount > 0 &&
        productPrice.percentageDiscount < 100;

    const isLowerPriceApplied = isSpecialPrice || (freeQuantity !== null && freeQuantity > 0);

    if (!isPriceVisible(productPrice.priceWithVat)) {
        return null;
    }

    return (
        <div className="flex items-center justify-between">
            <div className="whitespace-nowrap">
                <span className="font-semibold">{formatPrice(productPrice.priceWithVat)}</span>
                <span className="text-sm text-text-less"> / {unit}</span>
            </div>

            <div className="flex flex-col gap-0.5 text-right">
                <div
                    className={twMergeCustom(
                        'whitespace-nowrap font-bold font-secondary text-price-default',
                        isLowerPriceApplied && 'font-semibold text-price-before text-xs line-through',
                    )}
                >
                    {formatPrice(mapPriceForCalculations(productPrice.basicPrice.priceWithVat) * quantity)}
                </div>

                {isLowerPriceApplied && (
                    <div className="whitespace-nowrap font-bold font-secondary text-price-discounted">
                        {formatPrice(
                            mapPriceForCalculations(productPrice.priceWithVat) * (quantity - (freeQuantity || 0)),
                        )}
                    </div>
                )}
            </div>
        </div>
    );
};
