import { TransportImageWrapperStyled } from './OrdersContent.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import { TableGrid } from 'components/Basic/TableGrid/TableGrid';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import NextLink from 'next/link';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedOrderType } from 'types/orders';

type OrdersContentProps = {
    orders: ListedOrderType[] | undefined;
    totalCount: number | undefined;
    breadcrumbs: BreadcrumbItemType[];
};

const TEST_IDENTIFIER = 'pages-customer-orders-';

export const OrdersContent: FC<OrdersContentProps> = ({ breadcrumbs, orders, totalCount }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const { url } = useShopsysSelector((state) => state.domain);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);
    const { query } = useRouter();
    const currentPage = parsePageNumberFromQuery(query.page);

    return (
        <PaginationProvider {...getNewPagination(currentPage)}>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">{t('My orders')}</Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
            </Webline>
            <div ref={containerWrapRef}>
                <Webline>
                    <TableGrid>
                        {orders !== undefined && orders.length !== 0 && (
                            <tr>
                                <th>{t('Order number')}</th>
                                <th className="text-right">{t('Creation date')}</th>
                                <th className="text-right">{t('Number of items')}</th>
                                <th>{t('Shipping')}</th>
                                <th>{t('Payment')}</th>
                                <th className="text-right">{t('Total price including VAT')}</th>
                                <th>&nbsp;</th>
                            </tr>
                        )}

                        {orders !== undefined &&
                            orders.length !== 0 &&
                            orders.map((order, index) => (
                                <tr key={index} data-testid={TEST_IDENTIFIER + index}>
                                    <td data-testid={TEST_IDENTIFIER + 'number'}>
                                        <NextLink
                                            href={{
                                                pathname: customerOrderDetailUrl,
                                                query: { orderNumber: order.number },
                                            }}
                                        >
                                            {order.number}
                                        </NextLink>
                                    </td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'creation-date'}>
                                        {order.creationDate}
                                    </td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'quantity'}>
                                        {order.items.quantity}
                                    </td>
                                    <td data-testid={TEST_IDENTIFIER + 'transport'}>
                                        <TransportImageWrapperStyled>
                                            <Image
                                                image={order.transport.image}
                                                type="default"
                                                alt={order.transport.name}
                                            />
                                        </TransportImageWrapperStyled>
                                        {order.transport.name}
                                    </td>
                                    <td data-testid={TEST_IDENTIFIER + 'payment'}>{order.payment}</td>
                                    <td className="text-right" data-testid={TEST_IDENTIFIER + 'total-price'}>
                                        {formatPrice(order.totalPrice.priceWithVat)}
                                    </td>
                                    <td data-testid={TEST_IDENTIFIER + 'detail-link'}>
                                        <NextLink
                                            href={{
                                                pathname: customerOrderDetailUrl,
                                                query: { orderNumber: order.number },
                                            }}
                                        >
                                            {t('Detail')}
                                        </NextLink>
                                    </td>
                                </tr>
                            ))}

                        {orders?.length === 0 && (
                            <tr>
                                <th>{t('You have no orders')}</th>
                            </tr>
                        )}
                    </TableGrid>
                </Webline>
                <Webline>
                    <Pagination
                        totalCount={totalCount !== undefined ? totalCount : 0}
                        containerWrapRef={containerWrapRef}
                    />
                </Webline>
            </div>
        </PaginationProvider>
    );
};
