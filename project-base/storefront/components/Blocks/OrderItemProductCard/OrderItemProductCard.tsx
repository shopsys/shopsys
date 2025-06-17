import { OrderItemProductPrice } from './OrderItemProductPrice';
import { Image } from 'components/Basic/Image/Image';
import { TIDs } from 'cypress/tids';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeProductPriceFragment } from 'graphql/requests/products/fragments/ProductPriceFragment.generated';
import { TypeAvailability, TypeAvailabilityStatusEnum } from 'graphql/types';
import { memo } from 'react';
import { twJoin } from 'tailwind-merge';
import { generateProductImageAlt } from 'utils/productAltText';

type OrderItemProductCardProps = {
    availability: TypeAvailability;
    mainImage?: TypeImageFragment | null;
    fullName: string;
    categoryName: string;
    quantity: number;
    unit: string | null;
    price: TypeProductPriceFragment;
};

const OrderItemProductCardComp: FC<OrderItemProductCardProps> = ({
    mainImage,
    fullName,
    categoryName,
    quantity,
    unit,
    price,
    availability,
}) => {
    return (
        <li className="bg-background-more font-secondary flex flex-col gap-1 rounded-xl p-4">
            <div className="isolate flex items-center gap-2.5">
                <Image
                    alt={generateProductImageAlt(fullName, categoryName)}
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
                                    'text-availability-in-stock',
                                availability.status === TypeAvailabilityStatusEnum.OutOfStock &&
                                    'text-availability-out-of-stock',
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

export const OrderItemProductCard = memo(OrderItemProductCardComp);
