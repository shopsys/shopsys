import { FC, useRef } from 'react';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { formatPrice } from 'utils/formatting';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import Heading from 'components/Basic/Heading';
import { HeadingWrapperStyled } from 'components/Layout/SimpleLayout/SimpleLayout.style';
import Image from 'components/Basic/Image';
import { ListedOrderType } from 'types/orders';
import NextLink from 'next/link';
import Pagination from 'components/Blocks/Pagination';
import TableGrid from 'components/Basic/TableGrid';
import { TransportImageWrapperStyled } from './Orders.style';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

type ListedOrdersProps = { orders: ListedOrderType[] | undefined; totalCount: number | undefined };

const Orders: FC<ListedOrdersProps> = (props) => {
    const testIdentifier = 'pages-customer-orders-';

    const t = useTypedTranslationFunction();
    const { currencyCode, url } = useShopsysSelector((state) => state.domain);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);

    const [customerOrdersUrl, customerOrderDetailUrl] = getInternationalizedStaticUrls(
        ['/customer/orders', '/customer/order-detail'],
        url,
    );
    const orders = props.orders;

    return (
        <>
            <Webline>
                <HeadingWrapperStyled>
                    <Heading type="h1">{t('My orders')}</Heading>
                </HeadingWrapperStyled>
                <Breadcrumbs key="breadcrumb" breadcrumb={[{ name: t('My orders'), slug: customerOrdersUrl }]} />
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
                                <tr key={index} data-testid={testIdentifier + index}>
                                    <td data-testid={testIdentifier + 'number'}>
                                        <NextLink
                                            href={{
                                                pathname: customerOrderDetailUrl,
                                                query: { orderNumber: order.number },
                                            }}
                                        >
                                            {order.number}
                                        </NextLink>
                                    </td>
                                    <td className="text-right" data-testid={testIdentifier + 'creation-date'}>
                                        {order.creationDate}
                                    </td>
                                    <td className="text-right" data-testid={testIdentifier + 'quantity'}>
                                        {order.items.quantity}
                                    </td>
                                    <td data-testid={testIdentifier + 'transport'}>
                                        <TransportImageWrapperStyled>
                                            <Image image={order.transport.image} alt={order.transport.name} />
                                        </TransportImageWrapperStyled>
                                        {order.transport.name}
                                    </td>
                                    <td data-testid={testIdentifier + 'payment'}>{order.payment}</td>
                                    <td className="text-right" data-testid={testIdentifier + 'total-price'}>
                                        {formatPrice(order.totalPrice.priceWithVat, currencyCode, t)}
                                    </td>
                                    <td data-testid={testIdentifier + 'detail-link'}>
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
                        totalCount={props.totalCount !== undefined ? props.totalCount : 0}
                        containerWrapRef={containerWrapRef}
                    />
                </Webline>
            </div>
        </>
    );
};

export default Orders;
