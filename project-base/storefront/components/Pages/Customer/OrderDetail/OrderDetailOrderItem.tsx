import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';

type OrderDetailOrderItemProps = {
    orderItem: TypeOrderDetailItemFragment;
    isDiscount?: boolean;
};

export const OrderDetailOrderItem: FC<OrderDetailOrderItemProps> = ({ orderItem, isDiscount }) => {
    const formatPrice = useFormatPrice();

    return (
        <div
            className={twJoin(
                'flex gap-3 vl:gap-5 first:border-none items-center first:pt-0 last:pb-0',
                isDiscount ? 'pb-5' : 'py-5 border-t border-t-borderAccentLess',
            )}
        >
            {isDiscount ? (
                <div className="min-w-[60px]" />
            ) : (
                <Image alt={orderItem.name} height={60} src={orderItem.product?.mainImage?.url} width={60} />
            )}
            <div className="w-full flex flex-wrap justify-between vl:grid vl:grid-cols-[4fr_1fr_2fr] gap-3 vl:gap-5 last:border-none border-b border-b-borderLess items-center">
                {isDiscount ? (
                    <span>{orderItem.name}</span>
                ) : (
                    <ExtendedNextLink className="w-full vl:w-fit" href={orderItem.product?.slug ?? ''}>
                        {orderItem.name}
                    </ExtendedNextLink>
                )}
                {isDiscount ? (
                    <div />
                ) : (
                    <span>
                        {orderItem.quantity}
                        {orderItem.unit}
                    </span>
                )}
                <span className="font-bold">{formatPrice(orderItem.totalPrice.priceWithVat)}</span>
            </div>
        </div>
    );
};
