import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible, mapPriceForCalculations } from 'utils/mappers/price';

type OrderItemDiscountCardProps = {
    name: string;
    price: string;
};

export const OrderItemDiscountCard: FC<OrderItemDiscountCardProps> = ({ name, price }) => {
    const formatPrice = useFormatPrice();

    return (
        <li className="-mt-5 flex flex-col gap-1 rounded-b-xl bg-background-more px-4 pb-4 font-secondary">
            <div className="flex items-center justify-between gap-2">
                <span className="font-semibold text-sm">{name}</span>

                {isPriceVisible(price) && (
                    <div className="whitespace-nowrap font-bold font-secondary text-price-discounted">
                        {formatPrice(mapPriceForCalculations(price))}
                    </div>
                )}
            </div>
        </li>
    );
};
