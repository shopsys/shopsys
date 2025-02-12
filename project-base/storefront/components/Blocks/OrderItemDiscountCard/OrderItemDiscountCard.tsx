import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { mapPriceForCalculations } from 'utils/mappers/price';

type OrderItemDiscountCardProps = {
    name: string;
    price: string;
};

export const OrderItemDiscountCard: FC<OrderItemDiscountCardProps> = ({ name, price }) => {
    const formatPrice = useFormatPrice();

    return (
        <li className="-mt-5 flex flex-col gap-1 rounded-b-xl bg-backgroundMore px-4 pb-4 font-secondary">
            <div className="flex items-center justify-between gap-2">
                <span className="text-sm font-semibold">{name}</span>
                <div className="whitespace-nowrap font-secondary font-bold text-priceDiscounted">
                    {formatPrice(mapPriceForCalculations(price))}
                </div>
            </div>
        </li>
    );
};
