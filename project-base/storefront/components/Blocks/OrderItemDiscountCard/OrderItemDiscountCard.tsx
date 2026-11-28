import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderItemDiscountCardProps = {
    name: string;
    price: string;
};

export const OrderItemDiscountCard: FC<OrderItemDiscountCardProps> = ({ name, price }) => {
    const formatPrice = useFormatPrice();

    return (
        <li className="-mt-5 flex flex-col gap-1 rounded-b-card bg-background-more px-4 pb-4">
            <div className="flex items-center justify-between gap-2">
                <span className="font-semibold text-sm">{name}</span>

                {isPriceVisible(price) && (
                    <div className="whitespace-nowrap font-price text-price-discounted tabular-nums">
                        {formatPrice(mapPriceForCalculations(price))}
                    </div>
                )}
            </div>
        </li>
    );
};
