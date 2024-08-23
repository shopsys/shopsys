import { OrderDetailOrderItem } from './OrderDetailOrderItem';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { OrderItemColumnInfo } from 'components/Pages/Customer/Orders/OrderItem';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { twMergeCustom } from 'utils/twMerge';

type OrderDetailBasicInfoProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailBasicInfo: FC<OrderDetailBasicInfoProps> = ({ order }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDate } = useFormatDate();
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const orderRounding = order.items.find((orderItem) => orderItem.type === TypeOrderItemTypeEnum.Rounding);
    const orderTransport = order.items.find((orderItem) => orderItem.type === TypeOrderItemTypeEnum.Transport);
    const orderPayment = order.items.find((orderItem) => orderItem.type === TypeOrderItemTypeEnum.Payment);

    const filteredOrderItems = order.items.filter(
        (orderItem) =>
            ![TypeOrderItemTypeEnum.Payment, TypeOrderItemTypeEnum.Transport, TypeOrderItemTypeEnum.Rounding].includes(
                orderItem.type,
            ),
    );

    return (
        <div className="my-6 vl:mb-8 flex flex-col gap-4 bg-background">
            <OrderRowWrapper className="flex justify-between gap-4 items-center">
                <div className="gap-6  gap-y-2 vl:gap-8 flex flex-wrap">
                    <OrderItemColumnInfo
                        tid={TIDs.order_detail_number}
                        title={t('Order number')}
                        value={order.number}
                    />
                    <OrderItemColumnInfo
                        tid={TIDs.order_detail_creation_date}
                        title={t('Date of order')}
                        value={formatDate(order.creationDate, 'DD. MM. YYYY')}
                    />
                    <OrderItemColumnInfo
                        title={t('Price')}
                        value={formatPrice(order.totalPrice.priceWithVat)}
                        valueClassName="text-textAccent"
                    />
                    <OrderItemColumnInfo title={t('Status')} value={order.status} />
                </div>
                <Button
                    size="small"
                    tid={TIDs.order_detail_repeat_order_button}
                    variant="inverted"
                    onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                >
                    {t('Repeat order')}
                </Button>
            </OrderRowWrapper>
            {orderTransport && (
                <OrderRowWrapper className="flex gap-4 flex-col">
                    <div className="flex gap-4">
                        {t('Transport')} - {orderTransport.name}
                        <span className="font-bold">{formatPrice(orderTransport.totalPrice.priceWithVat)}</span>
                    </div>
                    {order.trackingUrl && (
                        <div>
                            {t('Tracking package')}
                            {' - '}
                            <ExtendedNextLink href={order.trackingUrl} target="_blank">
                                {order.trackingNumber}
                            </ExtendedNextLink>
                        </div>
                    )}
                </OrderRowWrapper>
            )}
            {orderPayment && (
                <OrderRowWrapper className="flex gap-4">
                    {t('Payment')} - {orderPayment.name}
                    <span className="font-bold">{formatPrice(orderPayment.totalPrice.priceWithVat)}</span>
                </OrderRowWrapper>
            )}
            {orderRounding && (
                <OrderRowWrapper className="flex gap-4">
                    {t('Rounding')}
                    <span className="font-bold">{formatPrice(orderRounding.totalPrice.priceWithVat)}</span>
                </OrderRowWrapper>
            )}
            <div className="bg-background border-[5px] border-borderLess rounded-md p-7">
                {filteredOrderItems.map((orderItem) => (
                    <OrderDetailOrderItem
                        key={orderItem.name}
                        isDiscount={orderItem.type === TypeOrderItemTypeEnum.Discount}
                        orderItem={orderItem}
                        orderUuid={order.uuid}
                    />
                ))}
            </div>
            {!!order.note && (
                <OrderRowWrapper className="flex gap-2">
                    <div>{t('Note')}</div>
                    {' - '}
                    <div className="font-bold">{order.note}</div>
                </OrderRowWrapper>
            )}
        </div>
    );
};

export const OrderRowWrapper: FC = ({ children, className }) => {
    return (
        <div className={twMergeCustom('rounded-md bg-backgroundMore px-4 vl:px-6 py-3 vl:py-4', className)}>
            {children}
        </div>
    );
};
