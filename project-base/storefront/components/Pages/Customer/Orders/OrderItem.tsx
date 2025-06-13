import { ElementWithImage, OrderItemColumnInfo, OrderItemRowInfo } from './OrderItemElements';
import { OrderPaymentStatusBar } from './OrderPaymentStatusBar';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

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

    const showRepeatOrderButton =
        canCreateOrder &&
        order.productItems.some(
            (item) => item.product?.isVisible && !item.product.isSellingDenied && !item.product.isInquiryType,
        );

    const notPaid = order.hasExternalPayment && !order.isPaid && !order.hasPaymentInProcess;

    return (
        <div className="bg-background-more vl:flex-row flex flex-col flex-wrap justify-between gap-4 rounded-xl p-5">
            <div className="flex flex-1 flex-col gap-2.5">
                <div className="vl:flex-row flex flex-col gap-x-8 gap-y-2">
                    <OrderItemColumnInfo title={t('Order number')}>
                        <ExtendedNextLink
                            className="font-bold"
                            type="orderDetail"
                            aria-label={t('Go to order detail number {{ orderNumber }}', {
                                orderNumber: order.number,
                            })}
                            href={{
                                pathname: customerOrderDetailUrl,
                                query: { orderNumber: order.number },
                            }}
                        >
                            {order.number}
                        </ExtendedNextLink>
                    </OrderItemColumnInfo>

                    <OrderItemColumnInfo title={t('Date of order')}>
                        {formatDate(order.creationDate)}
                    </OrderItemColumnInfo>

                    {isPriceVisible(order.totalPrice.priceWithVat) && (
                        <OrderItemColumnInfo title={t('Price')}>
                            {formatPrice(order.totalPrice.priceWithVat)}

                            <OrderPaymentStatusBar
                                orderHasExternalPayment={order.hasExternalPayment}
                                orderHasPaymentInProcess={order.hasPaymentInProcess}
                                orderIsPaid={order.isPaid}
                            />
                        </OrderItemColumnInfo>
                    )}

                    <OrderItemColumnInfo title={t('State')}>{order.status}</OrderItemColumnInfo>
                </div>

                <OrderItemRowInfo title={t('Payment')}>
                    <ElementWithImage image={order.payment.mainImage?.url} name={order.payment.name} />
                </OrderItemRowInfo>

                <OrderItemRowInfo title={t('Transport')}>
                    <ElementWithImage image={order.transport.mainImage?.url} name={order.transport.name} />
                </OrderItemRowInfo>
            </div>

            <div className="flex shrink-0 gap-4">
                {showRepeatOrderButton && !notPaid && (
                    <Button
                        tid={TIDs.order_list_repeat_order_button}
                        variant="inverted"
                        aria-label={t('Repeat order number {{ orderNumber }}', {
                            orderNumber: order.number,
                        })}
                        onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                    >
                        {t('Repeat order')}
                    </Button>
                )}

                {notPaid && (
                    <LinkButton
                        type="orderDetail"
                        variant="primary"
                        aria-label={t('Repeat payment for order number {{ orderNumber }}', {
                            orderNumber: order.number,
                        })}
                        href={{
                            pathname: customerOrderDetailUrl,
                            query: { orderNumber: order.number },
                        }}
                    >
                        {t('Repeat payment')}
                    </LinkButton>
                )}

                <LinkButton
                    tid={TIDs.my_orders_link_ + listIndex}
                    type="orderDetail"
                    variant="secondary"
                    aria-label={t('Go to order detail number {{ orderNumber }}', {
                        orderNumber: order.number,
                    })}
                    href={{
                        pathname: customerOrderDetailUrl,
                        query: { orderNumber: order.number },
                    }}
                >
                    {t('Detail')}
                </LinkButton>
            </div>
        </div>
    );
};
