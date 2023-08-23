import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import { Loader } from 'components/Basic/Loader/Loader';
import { Cell, CellHead, Row, Table } from 'components/Basic/Table/Table';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { Button } from 'components/Forms/Button/Button';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi, ListedOrderFragmentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useAddOrderItemsToCart } from 'hooks/cart/useAddOrderItemsToCart';
import { useFormatDate } from 'hooks/formatting/useFormatDate';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
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
    breadcrumbs: BreadcrumbFragmentApi[];
};

const TEST_IDENTIFIER = 'pages-customer-orders-';

export const OrdersContent: FC<OrdersContentProps> = ({ isLoading, breadcrumbs, orders, totalCount }) => {
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
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
                <div className="text-center">
                    <Heading type="h1">{t('My orders')}</Heading>
                </div>
            </Webline>
            <div ref={paginationScrollTargetRef} className="scroll-mt-5">
                <Webline>
                    {(() => {
                        if (isLoading) {
                            return (
                                <div className="flex justify-center">
                                    <Loader className="w-10" />
                                </div>
                            );
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
                                        <CellHead isWithoutWrap>{t('Shipping')}</CellHead>
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
                                                href={{
                                                    pathname: customerOrderDetailUrl,
                                                    query: { orderNumber: order.number },
                                                }}
                                                type="static"
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
                                            <div className="relative top-1 mr-1 inline-flex w-10 justify-center">
                                                <Image
                                                    image={order.transport.mainImage}
                                                    type="default"
                                                    alt={order.transport.mainImage?.name || order.transport.name}
                                                    width={36}
                                                    height={20}
                                                />
                                            </div>
                                            {order.transport.name}
                                        </Cell>
                                        <Cell data-testid={TEST_IDENTIFIER + 'payment'}>{order.payment.name}</Cell>
                                        <Cell isWithoutWrap align="right" data-testid={TEST_IDENTIFIER + 'total-price'}>
                                            {formatPrice(order.totalPrice.priceWithVat)}
                                        </Cell>
                                        <Cell data-testid={TEST_IDENTIFIER + 'repeat-order'}>
                                            <Button
                                                onClick={() => addOrderItemsToEmptyCart(order.uuid)}
                                                size="small"
                                                className="bg-white text-greyDarker hover:bg-orangeLight hover:text-greyDark"
                                            >
                                                {t('Repeat order')}
                                            </Button>
                                        </Cell>
                                        <Cell data-testid={TEST_IDENTIFIER + 'detail-link'}>
                                            <ExtendedNextLink
                                                href={{
                                                    pathname: customerOrderDetailUrl,
                                                    query: { orderNumber: order.number },
                                                }}
                                                type="static"
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
                    <Pagination totalCount={totalCount || 0} paginationScrollTargetRef={paginationScrollTargetRef} />
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
