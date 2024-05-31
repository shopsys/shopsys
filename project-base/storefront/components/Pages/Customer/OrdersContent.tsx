import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Cell, CellHead, Row, Table } from 'components/Basic/Table/Table';
import { OrderStatus } from 'components/Blocks/OrderStatus/OrderStatus';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerOrders } from 'components/Blocks/Skeleton/SkeletonModuleCustomerOrders';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

type OrdersContentProps = {
    areOrdersFetching: boolean;
    orders: TypeListedOrderFragment[] | undefined;
    totalCount: number | undefined;
};

export const OrdersContent: FC<OrdersContentProps> = ({ areOrdersFetching, orders, totalCount }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDateAndTime } = useFormatDate();
    const { url } = useDomainConfig();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);
    const addOrderItemsToEmptyCart = useAddOrderItemsToCart();

    return (
        <>
            <Webline>
                <div className="text-center">
                    <h1 className="mb-3">{t('My orders')}</h1>
                </div>
            </Webline>

            <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
                <Webline>
                    {(() => {
                        if (areOrdersFetching) {
                            return <SkeletonModuleCustomerOrders />;
                        }

                        if (!orders?.length) {
                            return <div>{t('You have no orders')}</div>;
                        }

                        return (
                            <Table
                                head={
                                    <Row>
                                        <CellHead isWithoutWrap>{t('Order number')}</CellHead>
                                        <CellHead isWithoutWrap align="right">
                                            {t('Creation date')}
                                        </CellHead>
                                        <CellHead isWithoutWrap align="right">
                                            {t('Number of items')}
                                        </CellHead>
                                        <CellHead isWithoutWrap className=" min-w-[150px]">
                                            {t('Shipping')}
                                        </CellHead>
                                        <CellHead isWithoutWrap>{t('Payment')}</CellHead>
                                        <CellHead isWithoutWrap>{t('Status')}</CellHead>
                                        <CellHead isWithoutWrap align="right">
                                            {t('Total price including VAT')}
                                        </CellHead>
                                        <CellHead isWithoutWrap>&nbsp;</CellHead>
                                    </Row>
                                }
                            >
                                {orders.map((order, index) => (
                                    <Row key={order.uuid}>
                                        <Cell>
                                            <ExtendedNextLink
                                                type="order"
                                                href={{
                                                    pathname: customerOrderDetailUrl,
                                                    query: { orderNumber: order.number },
                                                }}
                                            >
                                                {order.number}
                                            </ExtendedNextLink>
                                        </Cell>
                                        <Cell align="right">{formatDateAndTime(order.creationDate)}</Cell>
                                        <Cell align="right">{order.productItems.length}</Cell>
                                        <Cell>
                                            <div className="flex items-center gap-2">
                                                <Image
                                                    alt={order.transport.mainImage?.name || order.transport.name}
                                                    height={20}
                                                    src={order.transport.mainImage?.url}
                                                    width={36}
                                                />
                                                <span className="flex-1">{order.transport.name}</span>
                                            </div>
                                        </Cell>
                                        <Cell>{order.payment.name}</Cell>
                                        <Cell isWithoutWrap>
                                            <OrderStatus order={order} />
                                        </Cell>
                                        <Cell isWithoutWrap align="right">
                                            {formatPrice(order.totalPrice.priceWithVat)}
                                        </Cell>
                                        <Cell>
                                            <Button
                                                size="small"
                                                tid={TIDs.order_list_repeat_order_button}
                                                onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                                            >
                                                {t('Repeat order')}
                                            </Button>
                                        </Cell>
                                        <Cell>
                                            <ExtendedNextLink
                                                tid={TIDs.my_orders_link_ + index}
                                                type="order"
                                                href={{
                                                    pathname: customerOrderDetailUrl,
                                                    query: { orderNumber: order.number },
                                                }}
                                            >
                                                {t('Detail')}
                                            </ExtendedNextLink>
                                        </Cell>
                                    </Row>
                                ))}
                            </Table>
                        );
                    })()}
                </Webline>

                <Webline>
                    <Pagination paginationScrollTargetRef={paginationScrollTargetRef} totalCount={totalCount || 0} />
                </Webline>
            </div>
        </>
    );
};
