import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderItemPriceProps = {
    unitPrice: string;
    totalPrice: string;
    unit: string | null;
    hideFreeUnitPrice?: boolean;
};

export const OrderItemPrice: FC<OrderItemPriceProps> = ({
    unitPrice,
    totalPrice,
    unit,
    hideFreeUnitPrice = false,
    children,
}) => {
    const formatPrice = useFormatPrice();
    const showUnitPrice = isPriceVisible(unitPrice) && (!hideFreeUnitPrice || mapPriceForCalculations(unitPrice) > 0);

    return (
        <div className="flex flex-wrap items-center justify-between gap-2">
            {children ??
                (showUnitPrice && (
                    <div className="whitespace-nowrap">
                        <span className="font-semibold">{formatPrice(unitPrice)}</span>
                        {unit && <span className="text-sm text-text-less"> / {unit}</span>}
                    </div>
                ))}
            {isPriceVisible(totalPrice) && (
                <span className="ml-auto whitespace-nowrap font-bold font-secondary text-price-default">
                    {formatPrice(totalPrice)}
                </span>
            )}
        </div>
    );
};
