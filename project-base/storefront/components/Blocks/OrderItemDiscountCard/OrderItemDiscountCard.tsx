import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderItemDiscountCardProps = {
    name: string;
    price: string;
};

export const OrderItemDiscountCard: FC<OrderItemDiscountCardProps> = ({ name, price }) => {
    const formatPrice = useFormatPrice();

    return (
        <li className="bg-background-more font-secondary -mt-5 flex flex-col gap-1 rounded-b-xl px-4 pb-4">
            <div className="flex items-center justify-between gap-2">
                <span className="text-sm font-semibold">{name}</span>

                {isPriceVisible(price) && (
                    <div className="font-secondary text-price-discounted font-bold whitespace-nowrap">
                        {formatPrice(mapPriceForCalculations(price))}
                    </div>
                )}
            </div>
        </li>
    );
};
