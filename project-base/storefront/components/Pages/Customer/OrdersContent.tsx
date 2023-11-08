import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Image } from 'components/Basic/Image/Image';
import { Cell, CellHead, Row, Table } from 'components/Basic/Table/Table';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerOrders } from 'components/Blocks/Skeleton/SkeletonModuleCustomerOrders';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { ListedOrderFragmentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useAddOrderItemsToCart } from 'hooks/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'hooks/formatting/useFormatDate';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef } from 'react';

const NotAddedProductsPopup = dynamic(() =>
    import('./NotAddedProductsPopup').then((component) => component.NotAddedProductsPopup),
);
const MergeCartsPopup = dynamic(() => import('./MergeCartsPopup').then((component) => component.MergeCartsPopup));

type OrdersContentProps = {
    isLoading: boolean;
    orders: ListedOrderFragmentApi[] | undefined;
    totalCount: number | undefined;
};

const TEST_IDENTIFIER = 'pages-customer-orders-';

export const OrdersContent: FC<OrdersContentProps> = ({ isLoading, orders, totalCount }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { formatDateAndTime } = useFormatDate();
    const { url } = useDomainConfig();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);
    const {
        orderForPrefillingUuid,
        setOrderForPrefillingUuid,
        addOrderItemsToEmptyCart,
        mergeOrderItemsWithCurrentCart,
        notAddedProductNames,
        setNotAddedProductNames,
    } = useAddOrderItemsToCart();

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
                        if (isLoading) {
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
                                        <CellHead isWithoutWrap align="right">
                                            {t('Total price including VAT')}
                                        </CellHead>
                                        <CellHead isWithoutWrap>&nbsp;</CellHead>
                                    </Row>
                                }
                            >
                                {orders.map((order, index) => (
                                    <Row key={order.uuid} data-testid={TEST_IDENTIFIER + index}>
                                        <Cell data-testid={TEST_IDENTIFIER + 'number'}>
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
                                        <Cell align="right" data-testid={TEST_IDENTIFIER + 'creation-date'}>
                                            {formatDateAndTime(order.creationDate)}
                                        </Cell>
                                        <Cell align="right" data-testid={TEST_IDENTIFIER + 'quantity'}>
                                            {order.productItems.length}
                                        </Cell>
                                        <Cell data-testid={TEST_IDENTIFIER + 'transport'}>
                                            <div className="flex items-center gap-2">
                                                <Image
                                                    alt={order.transport.mainImage?.name || order.transport.name}
                                                    className="h-9 w-9"
                                                    height={20}
                                                    image={order.transport.mainImage}
                                                    width={36}
                                                />
                                                <span className="flex-1">{order.transport.name}</span>
                                            </div>
                                        </Cell>
                                        <Cell data-testid={TEST_IDENTIFIER + 'payment'}>{order.payment.name}</Cell>
                                        <Cell isWithoutWrap align="right" data-testid={TEST_IDENTIFIER + 'total-price'}>
                                            {formatPrice(order.totalPrice.priceWithVat)}
                                        </Cell>
                                        <Cell data-testid={TEST_IDENTIFIER + 'repeat-order'}>
                                            <Button
                                                className="bg-white text-greyDarker hover:bg-orangeLight hover:text-greyDark"
                                                size="small"
                                                onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                                            >
                                                {t('Repeat order')}
                                            </Button>
                                        </Cell>
                                        <Cell data-testid={TEST_IDENTIFIER + 'detail-link'}>
                                            <ExtendedNextLink
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

            {!!orderForPrefillingUuid && (
                <MergeCartsPopup
                    mergeOrderItemsWithCurrentCart={mergeOrderItemsWithCurrentCart}
                    orderForPrefillingUuid={orderForPrefillingUuid}
                    onCloseCallback={() => setOrderForPrefillingUuid(undefined)}
                />
            )}

            {!!notAddedProductNames?.length && (
                <NotAddedProductsPopup
                    notAddedProductNames={notAddedProductNames}
                    onCloseCallback={() => setNotAddedProductNames(undefined)}
                />
            )}
        </>
    );
};
