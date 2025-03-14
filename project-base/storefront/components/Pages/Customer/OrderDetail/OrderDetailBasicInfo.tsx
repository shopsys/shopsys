import { OrderDetailOrderItem } from './OrderDetailOrderItem';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { OrderItemColumnInfo } from 'components/Pages/Customer/Orders/OrderItemElements';
import { OrderPaymentStatusBar } from 'components/Pages/Customer/Orders/OrderPaymentStatusBar';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { twMergeCustom } from 'utils/twMerge';

type OrderDetailBasicInfoProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailBasicInfo: FC<OrderDetailBasicInfoProps> = ({ order }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDate } = useFormatDate();
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const { canCreateOrder } = useAuthorization();
    const orderRounding = order.items.find((orderItem) => orderItem.type === TypeOrderItemTypeEnum.Rounding);
    const orderTransport = order.items.find((orderItem) => orderItem.type === TypeOrderItemTypeEnum.Transport);
    const orderPayment = order.items.find((orderItem) => orderItem.type === TypeOrderItemTypeEnum.Payment);

    const filteredOrderItems = order.items.filter(
        (orderItem) =>
            ![TypeOrderItemTypeEnum.Payment, TypeOrderItemTypeEnum.Transport, TypeOrderItemTypeEnum.Rounding].includes(
                orderItem.type,
            ),
    );

    const showRepeatOrderButton =
        canCreateOrder &&
        filteredOrderItems.some(
            (item) => item.product?.isVisible && !item.product.isSellingDenied && !item.product.isInquiryType,
        );

    return (
        <>
            <OrderRowWrapper className="flex items-center justify-between gap-4">
                <div className="vl:gap-8 flex flex-wrap gap-6 gap-y-2">
                    <OrderItemColumnInfo title={t('Order number')}>
                        <span tid={TIDs.order_detail_number}>{order.number}</span>
                    </OrderItemColumnInfo>

                    <OrderItemColumnInfo title={t('Date of order')}>
                        <span tid={TIDs.order_detail_creation_date}>{formatDate(order.creationDate)}</span>
                    </OrderItemColumnInfo>

                    {isPriceVisible(order.totalPrice.priceWithVat) && (
                        <OrderItemColumnInfo title={t('Price')}>
                            {formatPrice(order.totalPrice.priceWithVat)}

                            <OrderPaymentStatusBar
                                orderHasPaymentInProcess={order.hasPaymentInProcess}
                                orderIsPaid={order.isPaid}
                                orderPaymentType={order.payment.type}
                            />
                        </OrderItemColumnInfo>
                    )}

                    <OrderItemColumnInfo title={t('Status')}>{order.status}</OrderItemColumnInfo>
                </div>

                {showRepeatOrderButton && (
                    <Button
                        tid={TIDs.order_detail_repeat_order_button}
                        variant="inverted"
                        onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                    >
                        {t('Repeat order')}
                    </Button>
                )}
            </OrderRowWrapper>

            {orderTransport && (
                <OrderRowWrapper className="flex flex-col gap-4" tid={TIDs.order_detail_transport}>
                    <div className="flex gap-4">
                        {t('Transport')} - {orderTransport.name}
                        {isPriceVisible(order.totalPrice.priceWithVat) && (
                            <span className="font-bold">{formatPrice(orderTransport.totalPrice.priceWithVat)}</span>
                        )}
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
                <OrderRowWrapper className="flex gap-4" tid={TIDs.order_detail_payment}>
                    {t('Payment')} - {orderPayment.name}
                    {isPriceVisible(order.totalPrice.priceWithVat) && (
                        <span className="font-bold">{formatPrice(orderPayment.totalPrice.priceWithVat)}</span>
                    )}
                </OrderRowWrapper>
            )}

            {orderRounding && (
                <OrderRowWrapper className="flex gap-4">
                    {t('Rounding')}
                    {isPriceVisible(order.totalPrice.priceWithVat) && (
                        <span className="font-bold">{formatPrice(orderRounding.totalPrice.priceWithVat)}</span>
                    )}
                </OrderRowWrapper>
            )}

            <div className="border-borderLess bg-background rounded-md border-[5px] p-7" tid={TIDs.order_detail_items}>
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
                <OrderRowWrapper className="flex gap-2" tid={TIDs.order_detail_note}>
                    <div>{t('Note')}</div>
                    {' - '}
                    <div className="font-bold">{order.note}</div>
                </OrderRowWrapper>
            )}
        </>
    );
};

export const OrderRowWrapper: FC = ({ children, className, tid }) => {
    return (
        <div className={twMergeCustom('bg-backgroundMore vl:px-6 vl:py-4 rounded-md px-4 py-3', className)} tid={tid}>
            {children}
        </div>
    );
};
