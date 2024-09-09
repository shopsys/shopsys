import { OrderPaymentStatusBar } from './OrderPaymentStatusBar';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Button } from 'components/Forms/Button/Button';
import { LinkButton } from 'components/Forms/Button/LinkButton';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { ReactNode } from 'react';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';

type OrderItemProps = {
    order: TypeListedOrderFragment;
    addOrderItemsToEmptyCart: (orderUuid: string) => Promise<void>;
    listIndex: number;
};

export const OrderItem: FC<OrderItemProps> = ({ order, addOrderItemsToEmptyCart, listIndex }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);

    return (
        <div className="bg-backgroundMore flex flex-col gap-5 rounded-md p-4 vl:p-6">
            <OrderPaymentStatusBar orderIsPaid={order.isPaid} orderPaymentType={order.payment.type} />
            <div className="flex flex-col vl:flex-row vl:justify-between vl:items-start gap-6">
                <div className="flex flex-col gap-5">
                    <div className="flex gap-x-8 gap-y-2 flex-wrap">
                        <OrderItemColumnInfo
                            title={t('Order number')}
                            value={
                                <ExtendedNextLink
                                    type="order"
                                    href={{
                                        pathname: customerOrderDetailUrl,
                                        query: { orderNumber: order.number },
                                    }}
                                >
                                    {order.number}
                                </ExtendedNextLink>
                            }
                        />
                        <OrderItemColumnInfo
                            title={t('Date of order')}
                            value={formatDate(order.creationDate, 'DD. MM. YYYY')}
                        />
                        <OrderItemColumnInfo
                            title={t('Price')}
                            value={formatPrice(order.totalPrice.priceWithVat)}
                            valueClassName="text-price"
                            wrapperClassName="min-w-[80px] max-w-[80px]"
                        />
                        <OrderItemColumnInfo
                            title={t('Status')}
                            value={order.status}
                            wrapperClassName="min-w-[100px] max-w-[100px]"
                        />
                    </div>
                    <div className="flex flex-col gap-3">
                        <OrderItemRowInfo
                            rowValueClassName="flex gap-2 items-center"
                            title={t('Payment')}
                            value={
                                <>
                                    <Image
                                        alt={order.payment.name}
                                        className="max-h-[20px] w-8 object-contain object-left"
                                        height={20}
                                        src={order.payment.mainImage?.url}
                                        width={32}
                                    />
                                    {order.payment.name}
                                </>
                            }
                        />
                        <OrderItemRowInfo
                            rowValueClassName="flex gap-2 items-center"
                            title={t('Transport')}
                            value={
                                <>
                                    <Image
                                        alt={order.transport.name}
                                        className="max-h-[20px] w-8 object-contain object-left"
                                        height={20}
                                        src={order.transport.mainImage?.url}
                                        width={32}
                                    />
                                    {order.transport.name}
                                </>
                            }
                        />
                        {order.note && <OrderItemRowInfo title={t('Note')} value={order.note} />}
                    </div>
                </div>
                <div className="flex gap-2 items-center">
                    <Button
                        size="small"
                        tid={TIDs.order_list_repeat_order_button}
                        variant="inverted"
                        onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                    >
                        {t('Repeat order')}
                    </Button>
                    <LinkButton
                        size="small"
                        tid={TIDs.my_orders_link_ + listIndex}
                        type="order"
                        href={{
                            pathname: customerOrderDetailUrl,
                            query: { orderNumber: order.number },
                        }}
                    >
                        {t('Detail')}
                    </LinkButton>
                </div>
            </div>
        </div>
    );
};

type OrderItemColumnInfoProps = {
    title: string;
    value: ReactNode;
    valueClassName?: string;
    wrapperClassName?: string;
    tid?: string;
};

export const OrderItemColumnInfo: FC<OrderItemColumnInfoProps> = ({
    title,
    value,
    valueClassName,
    wrapperClassName,
    tid,
}) => {
    return (
        <div className={twMergeCustom('flex gap-4 items-end', wrapperClassName)}>
            <div className="flex flex-col gap-1">
                <span className="text-sm">{title}</span>
                <span className={twMergeCustom('font-bold leading-none', valueClassName)} tid={tid}>
                    {value}
                </span>
            </div>
        </div>
    );
};

type OrderItemRowInfoProps = {
    title: string;
    value: ReactNode;
    rowValueClassName?: string;
};

export const OrderItemRowInfo: FC<OrderItemRowInfoProps> = ({ title, value, rowValueClassName }) => {
    return (
        <div className="grid grid-cols-[85px_1fr]">
            <span className="text-sm">{title}</span>
            <span className={twMergeCustom('font-bold leading-5 [overflow-wrap:anywhere]', rowValueClassName)}>
                {value}
            </span>
        </div>
    );
};
