import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { Image } from 'components/Basic/Image/Image';
import { TableGrid } from 'components/Basic/TableGrid/TableGrid';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi, ListedOrderFragmentApi } from 'graphql/generated';
import { formatDateAndTime } from 'helpers/formaters/formatDate';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRef } from 'react';

type OrdersContentProps = {
    orders: ListedOrderFragmentApi[] | undefined;
    totalCount: number | undefined;
    breadcrumbs: BreadcrumbFragmentApi[];
};

const TEST_IDENTIFIER = 'pages-customer-orders-';

export const OrdersContent: FC<OrdersContentProps> = ({ breadcrumbs, orders, totalCount }) => {
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);

    return (
        <>
            <Webline>
                <div className="text-center">
                    <Heading type="h1">{t('My orders')}</Heading>
                </div>
                <Breadcrumbs key="breadcrumb" breadcrumb={breadcrumbs} />
            </Webline>
            <div ref={containerWrapRef}>
                <Webline>
                    <TableGrid>
                        {orders !== undefined && orders.length !== 0 && (
                            <thead>
                                <tr>
                                    <th>{t('Order number')}</th>
                                    <th className="text-right">{t('Creation date')}</th>
                                    <th className="text-right">{t('Number of items')}</th>
                                    <th>{t('Shipping')}</th>
                                    <th>{t('Payment')}</th>
                                    <th className="text-right">{t('Total price including VAT')}</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                        )}

                        {orders !== undefined && orders.length !== 0 && (
                            <tbody>
                                {orders.map((order, index) => (
                                    <tr key={index} data-testid={TEST_IDENTIFIER + index}>
                                        <td data-testid={TEST_IDENTIFIER + 'number'}>
                                            <ExtendedNextLink
                                                href={{
                                                    pathname: customerOrderDetailUrl,
                                                    query: { orderNumber: order.number },
                                                }}
                                                type="static"
                                            >
                                                {order.number}
                                            </ExtendedNextLink>
                                        </td>
                                        <td className="text-right" data-testid={TEST_IDENTIFIER + 'creation-date'}>
                                            {formatDateAndTime(order.creationDate)}
                                        </td>
                                        <td className="text-right" data-testid={TEST_IDENTIFIER + 'quantity'}>
                                            {order.productItems.length}
                                        </td>
                                        <td data-testid={TEST_IDENTIFIER + 'transport'}>
                                            <div className="relative top-1 mr-1 inline-flex w-10 justify-center">
                                                <Image
                                                    image={order.transport.mainImage}
                                                    type="default"
                                                    alt={order.transport.mainImage?.name || order.transport.name}
                                                    maxWidth={36}
                                                    maxHeight={20}
                                                />
                                            </div>
                                            {order.transport.name}
                                        </td>
                                        <td data-testid={TEST_IDENTIFIER + 'payment'}>{order.payment.name}</td>
                                        <td className="text-right" data-testid={TEST_IDENTIFIER + 'total-price'}>
                                            {formatPrice(order.totalPrice.priceWithVat)}
                                        </td>
                                        <td data-testid={TEST_IDENTIFIER + 'detail-link'}>
                                            <ExtendedNextLink
                                                href={{
                                                    pathname: customerOrderDetailUrl,
                                                    query: { orderNumber: order.number },
                                                }}
                                                type="static"
                                            >
                                                {t('Detail')}
                                            </ExtendedNextLink>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        )}

                        {orders?.length === 0 && (
                            <tbody>
                                <tr>
                                    <th>{t('You have no orders')}</th>
                                </tr>
                            </tbody>
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
        </>
    );
};
