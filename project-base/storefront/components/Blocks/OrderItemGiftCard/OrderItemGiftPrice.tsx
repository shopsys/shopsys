import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderItemGiftPriceProps = {
    baseGiftPrice: string;
    quantity: number;
    unit: string | null;
};

export const OrderItemGiftPrice: FC<OrderItemGiftPriceProps> = ({ baseGiftPrice, quantity, unit }) => {
    const formatPrice = useFormatPrice();

    if (!isPriceVisible(baseGiftPrice)) {
        return null;
    }

    return (
        <div className="flex items-center justify-between">
            <div className="whitespace-nowrap">
                <span className="font-semibold">{formatPrice(baseGiftPrice)}</span>
                <span className="text-text-less text-sm"> / {unit}</span>
            </div>

            <div className="flex flex-col gap-0.5 text-right">
                <div className="font-secondary text-price-default font-bold whitespace-nowrap">
                    {formatPrice(mapPriceForCalculations(baseGiftPrice) * quantity)}
                </div>
            </div>
        </div>
    );
};
