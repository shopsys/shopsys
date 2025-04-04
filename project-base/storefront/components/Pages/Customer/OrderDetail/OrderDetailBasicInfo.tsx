import { OrderDetailOrderItem } from './OrderDetailOrderItem';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { WalletIcon } from 'components/Basic/Icon/WalletIcon';
import { Button } from 'components/Forms/Button/Button';
import { ElementWithImage, OrderItemColumnInfo } from 'components/Pages/Customer/Orders/OrderItemElements';
import { OrderPaymentStatusBar } from 'components/Pages/Customer/Orders/OrderPaymentStatusBar';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { ReactNode } from 'react';
import { twJoin } from 'tailwind-merge';
import { PaymentTypeEnum } from 'types/payment';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';

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

    const notPaid = order.payment.type === PaymentTypeEnum.GoPay && !order.isPaid && !order.hasPaymentInProcess;

    return (
        <>
            <div className="bg-backgroundMore vl:flex-row flex flex-col flex-wrap justify-between gap-5 rounded-xl p-5">
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

                {showRepeatOrderButton && !notPaid && (
                    <div className="flex shrink-0 gap-4">
                        <Button
                            tid={TIDs.order_detail_repeat_order_button}
                            variant="inverted"
                            onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                        >
                            {t('Repeat order')}
                        </Button>
                    </div>
                )}

                {notPaid && (
                    <div className="flex flex-col items-center justify-start gap-2.5 xl:flex-row xl:gap-5">
                        <div
                            className={twJoin(
                                'flex size-8 items-center justify-center rounded-full sm:size-11',
                                'bg-backgroundError text-textInverted',
                            )}
                        >
                            <WalletIcon className="size-4 sm:size-6" />
                        </div>

                        <h5 className={twJoin('text-center text-xs sm:text-sm lg:text-base', 'text-textError')}>
                            {t('Payment failed')}
                        </h5>
                    </div>
                )}
            </div>

            {canCreateOrder && notPaid && (
                <PaymentsInOrderSelect
                    orderUuid={order.uuid}
                    paymentTransactionCount={order.paymentTransactionsCount}
                />
            )}

            {orderTransport && (
                <OrderDetailRowInfo tid={TIDs.order_detail_transport} title={t('Transport')}>
                    <div className="flex w-full items-center justify-between">
                        <div className="flex flex-col gap-2">
                            <ElementWithImage image={order.transport.mainImage?.url} name={orderTransport.name} />

                            {order.trackingUrl && (
                                <div>
                                    {t('Tracking package')}
                                    {' - '}
                                    <ExtendedNextLink href={order.trackingUrl} target="_blank">
                                        {order.trackingNumber}
                                    </ExtendedNextLink>
                                </div>
                            )}
                        </div>

                        {isPriceVisible(order.totalPrice.priceWithVat) && (
                            <span className="font-bold">{formatPrice(orderTransport.totalPrice.priceWithVat)}</span>
                        )}
                    </div>
                </OrderDetailRowInfo>
            )}

            {orderPayment && (
                <OrderDetailRowInfo tid={TIDs.order_detail_payment} title={t('Payment')}>
                    <div className="flex w-full justify-between">
                        <ElementWithImage image={order.payment.mainImage?.url} name={orderPayment.name} />

                        {isPriceVisible(order.totalPrice.priceWithVat) && (
                            <span className="font-bold">{formatPrice(orderPayment.totalPrice.priceWithVat)}</span>
                        )}
                    </div>
                </OrderDetailRowInfo>
            )}

            {orderRounding && (
                <OrderDetailRowInfo title={t('Rounding')}>
                    {isPriceVisible(order.totalPrice.priceWithVat) && (
                        <span className="block w-full text-right font-bold">
                            {formatPrice(orderRounding.totalPrice.priceWithVat)}
                        </span>
                    )}
                </OrderDetailRowInfo>
            )}

            <div className="bg-backgroundMore rounded-xl p-5">
                <div tid={TIDs.order_detail_items}>
                    {filteredOrderItems.map((orderItem) => (
                        <OrderDetailOrderItem
                            key={orderItem.name}
                            isDiscount={orderItem.type === TypeOrderItemTypeEnum.Discount}
                            orderItem={orderItem}
                            orderUuid={order.uuid}
                        />
                    ))}
                </div>

                <p className="font-secondary mt-8 mb-2 text-lg font-semibold">{t('Order summary')}</p>

                <div className="font-secondary flex items-start justify-between text-sm font-semibold">
                    <span className="mr-4 inline-flex items-end">{t('Total price')}</span>

                    <div className="flex flex-col items-end gap-2">
                        <strong className="text-price text-lg">{formatPrice(order.totalPrice.priceWithVat)}</strong>

                        <span className="text-priceBefore text-sm">
                            {formatPrice(order.totalPrice.priceWithoutVat)} {t('without VAT')}
                        </span>
                    </div>
                </div>
            </div>

            {!!order.note && (
                <OrderDetailRowInfo tid={TIDs.order_detail_note} title={t('Note')}>
                    {order.note}
                </OrderDetailRowInfo>
            )}
        </>
    );
};

type OrderDetailRowInfoProps = {
    tid?: string;
    title: string;
    children: ReactNode;
};

export const OrderDetailRowInfo: FC<OrderDetailRowInfoProps> = ({ tid, title, children }) => {
    return (
        <div
            className="vl:flex-row vl:gap-3 vl:items-center bg-backgroundMore flex flex-col gap-1 rounded-xl p-5 text-sm"
            tid={tid}
        >
            <span className="text-textSubtle font-secondary min-w-[100px] font-semibold">{title}</span>
            {children}
        </div>
    );
};
