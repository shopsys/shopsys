import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import {
    CustomerRecordCard,
    CustomerRecordColumnInfo,
    CustomerRecordElementWithImage,
    CustomerRecordRowInfo,
} from 'components/Pages/Customer/CustomerRecordElements';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import { useId } from 'react';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getOrderPaymentItem, getOrderTransportItem } from 'utils/mappers/order';
import { isPriceVisible } from 'utils/mappers/price';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { OrderItemProducts } from './OrderItemProducts';
import { OrderPaymentStatusBar } from './OrderPaymentStatusBar';

type OrderItemProps = {
    order: TypeListedOrderFragment;
    addOrderItemsToEmptyCart: (orderUuid: string) => Promise<void>;
    listIndex: number;
};

export const OrderItem: FC<OrderItemProps> = ({ order, addOrderItemsToEmptyCart, listIndex }) => {
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();
    const { formatDate } = useFormatDate();
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);
    const orderTransport = getOrderTransportItem(order.items);
    const orderPayment = getOrderPaymentItem(order.items);
    const orderSummaryId = useId();
    const orderSummary = [
        `${t('Date of order')}: ${formatDate(order.creationDate)}.`,
        `${t('State')}: ${order.status}.`,
        isPriceVisible(order.totalPrice.priceWithVat)
            ? `${t('Price')}: ${formatPrice(order.totalPrice.priceWithVat, { currencyCode: order.totalPrice.currencyCode })}.`
            : undefined,
    ]
        .filter(Boolean)
        .join(' ');

    const orderLink = {
        pathname: customerOrderDetailUrl,
        query: { orderNumber: order.number },
    };

    const showRepeatOrderButton =
        canCreateOrder &&
        order.productItems.some(
            (item) =>
                item.product?.isVisible &&
                !item.product.isSellingDenied &&
                !item.product.isInquiryType &&
                !item.product.isCurrentlyOutOfStock,
        );

    const notPaid = order.hasExternalPayment && !order.isPaid && !order.hasPaymentInProcess;

    return (
        <CustomerRecordCard>
            <span className="sr-only" id={orderSummaryId}>
                {orderSummary}
            </span>

            <div className="flex flex-1 flex-col gap-2.5">
                <div className="flex vl:flex-row flex-col gap-x-8 gap-y-2">
                    <CustomerRecordColumnInfo tid={TIDs.order_list_item_number} title={t('Order number')}>
                        <ExtendedNextLink
                            aria-describedby={orderSummaryId}
                            className="font-bold"
                            type="orderDetail"
                            aria-label={t('Go to order detail number {{ orderNumber }}', {
                                ns: 'accessibility',
                                orderNumber: order.number,
                            })}
                            href={{
                                pathname: customerOrderDetailUrl,
                                query: { orderNumber: order.number },
                            }}
                        >
                            {order.number}
                        </ExtendedNextLink>
                    </CustomerRecordColumnInfo>

                    <CustomerRecordColumnInfo tid={TIDs.order_list_item_date} title={t('Date of order')}>
                        {formatDate(order.creationDate)}
                    </CustomerRecordColumnInfo>

                    {isPriceVisible(order.totalPrice.priceWithVat) && (
                        <CustomerRecordColumnInfo title={t('Price')}>
                            {formatPrice(order.totalPrice.priceWithVat, {
                                currencyCode: order.totalPrice.currencyCode,
                            })}

                            <OrderPaymentStatusBar
                                orderHasExternalPayment={order.hasExternalPayment}
                                orderHasPaymentInProcess={order.hasPaymentInProcess}
                                orderIsPaid={order.isPaid}
                            />
                        </CustomerRecordColumnInfo>
                    )}

                    <CustomerRecordColumnInfo title={t('State')}>{order.status}</CustomerRecordColumnInfo>
                </div>

                {orderPayment && (
                    <CustomerRecordRowInfo title={t('Payment')}>
                        <CustomerRecordElementWithImage
                            image={orderPayment.payment?.mainImage?.url}
                            name={orderPayment.payment?.name || ''}
                            tid={TIDs.order_list_transport_and_payment_image}
                        />
                    </CustomerRecordRowInfo>
                )}

                {orderTransport && (
                    <CustomerRecordRowInfo title={t('Transport')}>
                        <CustomerRecordElementWithImage
                            image={orderTransport.transport?.mainImage?.url}
                            name={orderTransport.transport?.name || ''}
                            tid={TIDs.order_list_transport_and_payment_image}
                        />
                    </CustomerRecordRowInfo>
                )}

                <OrderItemProducts items={order.productItems} orderLink={orderLink} />
            </div>

            <div className="flex shrink-0 gap-4">
                {showRepeatOrderButton && !notPaid && (
                    <Button
                        tid={TIDs.order_list_repeat_order_button}
                        variant="inverted"
                        aria-label={t('Repeat order number {{ orderNumber }}', {
                            ns: 'accessibility',
                            orderNumber: order.number,
                        })}
                        onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                    >
                        {t('Repeat order')}
                    </Button>
                )}

                {notPaid && (
                    <LinkButton
                        href={orderLink}
                        type="orderDetail"
                        variant="primary"
                        aria-label={t('Repeat payment for order number {{ orderNumber }}', {
                            ns: 'accessibility',
                            orderNumber: order.number,
                        })}
                    >
                        {t('Repeat payment')}
                    </LinkButton>
                )}

                <LinkButton
                    aria-describedby={orderSummaryId}
                    href={orderLink}
                    tid={TIDs.my_orders_link_ + listIndex}
                    type="orderDetail"
                    variant="secondary"
                    aria-label={t('Go to order detail number {{ orderNumber }}', {
                        ns: 'accessibility',
                        orderNumber: order.number,
                    })}
                >
                    {t('Detail')}
                </LinkButton>
            </div>
        </CustomerRecordCard>
    );
};
