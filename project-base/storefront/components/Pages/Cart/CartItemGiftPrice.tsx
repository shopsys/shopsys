import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type CartItemGiftPriceProps = {
    baseGiftPrice: string;
    quantity: number;
};

export const CartItemGiftPrice: FC<CartItemGiftPriceProps> = ({ baseGiftPrice, quantity }) => {
    const formatPrice = useFormatPrice();

    if (!isPriceVisible(baseGiftPrice)) {
        return null;
    }

    return (
        <div className="vl:w-36 flex items-center justify-end">
            <div className="font-secondary flex flex-col gap-0.5 text-right font-bold whitespace-nowrap">
                <div className="text-price-default">
                    {formatPrice(mapPriceForCalculations(baseGiftPrice) * quantity)}
                </div>
            </div>
        </div>
    );
};
