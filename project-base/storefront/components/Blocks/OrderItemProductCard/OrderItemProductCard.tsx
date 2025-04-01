import { OrderItemProductPrice } from './OrderItemProductPrice';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';

type OrderItemProductCardProps = {
    availability: TypeAvailability;
    mainImage?: TypeImageFragment | null;
    fullName: string;
    quantity: number;
    unit: string | null;
    price: TypeProductPriceFragment;
};

export const OrderItemProductCard: FC<OrderItemProductCardProps> = ({
    mainImage,
    fullName,
    quantity,
    unit,
    price,
    availability,
}) => {
    return (
        <li className="bg-background-more font-secondary flex flex-col gap-1 rounded-xl p-4">
            <div className="flex items-center gap-2.5">
                <Image
                    alt={mainImage?.name || fullName}
                    className="size-auto max-h-20 max-w-20 mix-blend-multiply"
                    height={80}
                    src={mainImage?.url}
                    tid={TIDs.order_summary_cart_item_image}
                    width={80}
                />
                <div className="flex flex-1 items-center justify-between gap-2.5">
                    <div className="flex flex-col gap-0.5">
                        <span className="max-w-44 text-sm font-semibold">{fullName}</span>
                        <span
                            className={twJoin(
                                'text-xs font-semibold',
                                availability.status === TypeAvailabilityStatusEnum.InStock &&
                                    'text-availabilityInStock',
                                availability.status === TypeAvailabilityStatusEnum.OutOfStock &&
                                    'text-availabilityOutOfStock',
                            )}
                        >
                            {availability.name}
                        </span>
                    </div>
                    <span className="text-sm font-semibold whitespace-nowrap">
                        {quantity} {unit}
                    </span>
                </div>
            </div>

            <OrderItemProductPrice productPrice={price} quantity={quantity} unit={unit} />
        </li>
    );
};
