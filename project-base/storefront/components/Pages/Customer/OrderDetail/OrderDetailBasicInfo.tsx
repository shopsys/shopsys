import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Flag } from 'components/Basic/Flag/Flag';
import { WalletIcon } from 'components/Basic/Icon/WalletIcon';
import { Button } from 'components/Forms/Button/Button';
import { ElementWithImage, OrderItemColumnInfo } from 'components/Pages/Customer/Orders/OrderItemElements';
import { OrderPaymentStatusBar } from 'components/Pages/Customer/Orders/OrderPaymentStatusBar';
import { ShowPaymentInstructionButton } from 'components/Pages/Order/PaymentConfirmation/ShowPaymentInstructionButton';
import { PaymentsInOrderSelect } from 'components/PaymentsInOrderSelect/PaymentsInOrderSelect';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeOrderDetailFragment } from 'graphql/requests/orders/fragments/OrderDetailFragment.generated';
import { TypeOrderItemTypeEnum } from 'graphql/types';
import { ReactNode } from 'react';
import { twJoin } from 'tailwind-merge';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import {
    getOrderPaymentItem,
    getOrderRoundingItem,
    getOrderTransportItem,
    hasOrderExternalPaymentContext,
} from 'utils/mappers/order';
import { isPriceVisible } from 'utils/mappers/price';
import { OrderDetailOrderItem } from './OrderDetailOrderItem';

type OrderDetailBasicInfoProps = {
    order: TypeOrderDetailFragment;
};

export const OrderDetailBasicInfo: FC<OrderDetailBasicInfoProps> = ({ order }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDate } = useFormatDate();
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();
    const { canCreateOrder } = useAuthorization();
    const orderRounding = getOrderRoundingItem(order.items);
    const orderTransport = getOrderTransportItem(order.items);
    const orderPayment = getOrderPaymentItem(order.items);
    const hasExternalPaymentContext = hasOrderExternalPaymentContext(order);

    const filteredOrderItems = order.items.filter(
        (orderItem) =>
            ![TypeOrderItemTypeEnum.Payment, TypeOrderItemTypeEnum.Transport, TypeOrderItemTypeEnum.Rounding].includes(
                orderItem.type,
            ),
    );

    const showRepeatOrderButton =
        canCreateOrder &&
        filteredOrderItems.some(
            (item) =>
                item.product?.isVisible &&
                !item.product.isSellingDenied &&
                !item.product.isInquiryType &&
                !item.product.isCurrentlyOutOfStock,
        );

    const notPaid = hasExternalPaymentContext && !order.isPaid && !order.hasPaymentInProcess;
    const hasPaymentInProcess = hasExternalPaymentContext && !order.isPaid && order.hasPaymentInProcess;

    return (
        <>
            <div className="flex vl:flex-row flex-col flex-wrap justify-between gap-5 rounded-xl bg-background-more p-5">
                <OrderItemColumnInfo title={t('Order number')}>
                    <span data-tid={TIDs.order_detail_number}>{order.number}</span>
                </OrderItemColumnInfo>

                <OrderItemColumnInfo title={t('Date of order')}>
                    <span data-tid={TIDs.order_detail_creation_date}>{formatDate(order.creationDate)}</span>
                </OrderItemColumnInfo>

                {isPriceVisible(order.totalPrice.priceWithVat) && (
                    <OrderItemColumnInfo title={t('Price')}>
                        {formatPrice(order.totalPrice.priceWithVat)}

                        <OrderPaymentStatusBar
                            orderHasExternalPayment={hasExternalPaymentContext}
                            orderHasPaymentInProcess={order.hasPaymentInProcess}
                            orderIsPaid={order.isPaid}
                        />
                    </OrderItemColumnInfo>
                )}

                <OrderItemColumnInfo title={t('Status')}>{order.status}</OrderItemColumnInfo>

                {showRepeatOrderButton && !notPaid && (
                    <div className="flex shrink-0 flex-col items-end gap-2">
                        <Button
                            className="w-fit"
                            tid={TIDs.order_detail_repeat_order_button}
                            variant="secondary"
                            aria-label={t('Repeat order number {{ orderNumber }}', {
                                ns: 'accessibility',
                                orderNumber: order.number,
                            })}
                            onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                        >
                            {t('Repeat order')}
                        </Button>

                        {hasPaymentInProcess && order.lastExternalPaymentUrl && (
                            <ShowPaymentInstructionButton
                                href={order.lastExternalPaymentUrl}
                                orderUrlHash={order.urlHash}
                                orderUuid={order.uuid}
                            />
                        )}
                    </div>
                )}

                {notPaid && (
                    <div className="flex flex-col items-center justify-start gap-2.5 xl:flex-row xl:gap-5">
                        <div
                            className={twJoin(
                                'flex size-8 items-center justify-center rounded-full sm:size-11',
                                'bg-background-error text-text-inverted',
                            )}
                        >
                            <WalletIcon className="size-4 sm:size-6" />
                        </div>

                        <span className={twJoin('h5 text-center text-xs sm:text-sm lg:text-base', 'text-text-error')}>
                            {t('Payment failed')}
                        </span>
                    </div>
                )}
            </div>

            {canCreateOrder && notPaid && (
                <PaymentsInOrderSelect
                    orderNumber={order.number}
                    orderUrlHash={order.urlHash}
                    orderUuid={order.uuid}
                    paymentTransactionsCount={order.paymentTransactionsCount}
                />
            )}

            {orderTransport && (
                <OrderDetailRowInfo tid={TIDs.order_detail_transport} title={t('Transport')}>
                    <div className="flex w-full items-center justify-between">
                        <div className="flex flex-col gap-2">
                            <ElementWithImage
                                image={orderTransport.transport?.mainImage?.url}
                                name={orderTransport.name}
                            />

                            {order.trackingUrl && (
                                <div>
                                    {t('Tracking package')}
                                    {' - '}
                                    <ExtendedNextLink
                                        href={order.trackingUrl}
                                        target="_blank"
                                        aria-label={t('Go to tracking package {{ trackingNumber }}', {
                                            ns: 'accessibility',
                                            trackingNumber: order.trackingNumber,
                                        })}
                                    >
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
                        <ElementWithImage image={orderPayment.payment?.mainImage?.url} name={orderPayment.name} />

                        {isPriceVisible(orderPayment.totalPrice.priceWithVat) && (
                            <span className="font-bold">{formatPrice(orderPayment.totalPrice.priceWithVat)}</span>
                        )}
                    </div>
                </OrderDetailRowInfo>
            )}

            {orderRounding && isPriceVisible(orderRounding.totalPrice.priceWithVat) && (
                <OrderDetailRowInfo title={t('Rounding')}>
                    <span className="block w-full text-right font-bold">
                        {formatPrice(orderRounding.totalPrice.priceWithVat)}
                    </span>
                </OrderDetailRowInfo>
            )}

            <div className="rounded-xl bg-background-more p-5">
                <div data-tid={TIDs.order_detail_items}>
                    {filteredOrderItems.map((orderItem) => (
                        <OrderDetailOrderItem
                            key={orderItem.name}
                            isOrderFromRegisteredCustomer={order.customerUser !== null}
                            orderItem={orderItem}
                            orderUuid={order.uuid}
                            isDiscount={
                                orderItem.type === TypeOrderItemTypeEnum.Discount ||
                                orderItem.type === TypeOrderItemTypeEnum.Promotion
                            }
                        />
                    ))}
                </div>

                <div className="mt-8 flex flex-col gap-2 font-secondary font-semibold text-sm">
                    <span className="text-lg">{t('Order summary')}</span>

                    {order.promoCode && (
                        <div
                            className={twJoin(
                                'flex items-center justify-between gap-2',
                                isPriceVisible(order.totalPrice.priceWithVat) &&
                                    isPriceVisible(order.totalPrice.priceWithoutVat) &&
                                    'border-border-less border-b-[3px] pb-4',
                            )}
                        >
                            {t('Promo code')}
                            <Flag type="discount">{order.promoCode}</Flag>
                        </div>
                    )}

                    {isPriceVisible(order.totalPrice.priceWithVat) &&
                        isPriceVisible(order.totalPrice.priceWithoutVat) && (
                            <div className="flex items-baseline justify-between gap-2">
                                {t('Total price')}

                                <div className="flex flex-col items-end gap-2">
                                    <strong className="text-lg text-price">
                                        {formatPrice(order.totalPrice.priceWithVat)}
                                    </strong>

                                    <span className="text-price-before text-sm">
                                        {formatPrice(order.totalPrice.priceWithoutVat)} {t('without VAT')}
                                    </span>
                                </div>
                            </div>
                        )}
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

const OrderDetailRowInfo: FC<OrderDetailRowInfoProps> = ({ tid, title, children }) => {
    return (
        <div
            className="flex vl:flex-row flex-col vl:items-center gap-1 vl:gap-3 rounded-xl bg-background-more p-5 text-sm"
            data-tid={tid}
        >
            <span className="min-w-[100px] font-secondary font-semibold text-text-less">{title}</span>
            {children}
        </div>
    );
};
